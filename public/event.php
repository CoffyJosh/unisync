
<?php
session_start();

require_once '../php/db.php';

$studentId = $_SESSION['student_id'] ?? null;
$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validate event ID.
if (!$eventId || $eventId < 1) {
    header('Location: browse.php');
    exit;
}

// Retrieve event and organization information.
$eventQuery = $pdo->prepare(
    'SELECT
        e.event_id,
        e.event_name,
        e.description,
        e.status,
        e.event_image,
        o.organization_name
     FROM event e
     INNER JOIN organization o
        ON e.organization_id = o.organization_id
     WHERE e.event_id = :event_id'
);

$eventQuery->execute([
    ':event_id' => $eventId
]);

$event = $eventQuery->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

// Retrieve every schedule and its venue.
$scheduleQuery = $pdo->prepare(
    'SELECT
        es.schedule_id,
        es.event_date,
        es.start_time,
        es.end_time,
        v.venue_name,
        v.location,
        v.latitude,
        v.longitude
     FROM event_schedule es
     INNER JOIN venue v
        ON es.venue_id = v.venue_id
     WHERE es.event_id = :event_id
     ORDER BY es.event_date ASC, es.start_time ASC'
);

$scheduleQuery->execute([
    ':event_id' => $eventId
]);

$schedules = $scheduleQuery->fetchAll(PDO::FETCH_ASSOC);

// Determine the event's current timing using Manila time.
$timezone = new DateTimeZone('Asia/Manila');
$now = new DateTimeImmutable('now', $timezone);

$nextSchedule = null;
$isOngoing = false;
$hasFutureSchedule = false;

foreach ($schedules as &$schedule) {

    $start = new DateTimeImmutable(
        $schedule['event_date'] . ' ' . $schedule['start_time'],
        $timezone
    );

    $end = new DateTimeImmutable(
        $schedule['event_date'] . ' ' . $schedule['end_time'],
        $timezone
    );

    $schedule['start_datetime'] = $start->format('c');
    $schedule['end_datetime'] = $end->format('c');

    if ($now >= $start && $now < $end) {
        $isOngoing = true;
    }

    if ($now < $start) {
        $hasFutureSchedule = true;

        if (
            $nextSchedule === null ||
            $start < new DateTimeImmutable(
                $nextSchedule['event_date'] . ' ' . $nextSchedule['start_time'],
                $timezone
            )
        ) {
            $nextSchedule = $schedule;
        }
    }
}
unset($schedule);

// Determine registration status.
$isRegistered = false;

if ($studentId) {
    $registrationQuery = $pdo->prepare(
        'SELECT registration_id
         FROM registration
         WHERE student_id = :student_id
         AND event_id = :event_id'
    );

    $registrationQuery->execute([
        ':student_id' => $studentId,
        ':event_id' => $eventId
    ]);

    $isRegistered = (bool) $registrationQuery->fetch();
}

// Determine whether registration is allowed.
$canRegister =
    $event['status'] === 'confirmed' &&
    ($isOngoing || $hasFutureSchedule);

// Handle AJAX event registration.

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['ajax'])
) {
    header('Content-Type: application/json');

    if (!$studentId) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Please log in before managing your registration.'
        ]);
        exit;
    }

    $action = $_POST['action'] ?? '';

    try {

        if ($action === 'register') {

            if (!$canRegister) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This event is not accepting registrations.'
                ]);
                exit;
            }

            $query = $pdo->prepare(
                'INSERT INTO registration (student_id, event_id)
                 VALUES (:student_id, :event_id)'
            );

            $query->execute([
                ':student_id' => $studentId,
                ':event_id' => $eventId
            ]);

            echo json_encode([
                'success' => true,
                'action' => 'register',
                'message' => 'You have successfully registered for this event!'
            ]);

            exit;

        } elseif ($action === 'unregister') {

            $query = $pdo->prepare(
                'DELETE FROM registration
                 WHERE student_id = :student_id
                 AND event_id = :event_id'
            );

            $query->execute([
                ':student_id' => $studentId,
                ':event_id' => $eventId
            ]);

            if ($query->rowCount() > 0) {

                echo json_encode([
                    'success' => true,
                    'action' => 'unregister',
                    'message' => 'You have successfully unregistered from this event.'
                ]);

            } else {

                echo json_encode([
                    'success' => false,
                    'message' => 'You are not registered for this event.'
                ]);

            }

            exit;

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid registration action.'
            ]);

            exit;
        }

    } catch (PDOException $e) {

        if ($e->getCode() === '23505') {
            echo json_encode([
                'success' => false,
                'message' => 'You are already registered for this event.'
            ]);
            exit;
        }

        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Something went wrong. Please try again.'
        ]);

        exit;
    }
}


// Prepare map coordinates from the first schedule with valid coordinates.
$mapSchedule = null;

foreach ($schedules as $schedule) {
    if (
        $schedule['latitude'] !== null &&
        $schedule['longitude'] !== null &&
        is_numeric($schedule['latitude']) &&
        is_numeric($schedule['longitude']) &&
        (float)$schedule['latitude'] >= -90 &&
        (float)$schedule['latitude'] <= 90 &&
        (float)$schedule['longitude'] >= -180 &&
        (float)$schedule['longitude'] <= 180
    ) {
        $mapSchedule = $schedule;
        break;
    }
}

$eventImage = $event['event_image'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($event['event_name']); ?> | UniSync
    </title>

    <script src="../javascript/tailwind.js"></script>
    <script src="../javascript/jquery.js"></script>

    <!-- Leaflet Map CSS -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <!-- Leaflet Map JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        #eventMap {
            height: 350px;
            width: 100%;
            z-index: 1;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-slate-50">

    <?php include '../php/components/navbar.php'; ?>

    <main class="flex-1 w-full max-w-6xl mx-auto px-6 lg:px-8 py-10">

        <!-- Back Navigation -->
        <a href="browse.php"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-[#1E3A5F] mb-6">

            <i class="fa-solid fa-arrow-left"></i>
            Back to Events

        </a>

        <!-- Event Banner -->
        <div class="w-full h-64 md:h-96 rounded-2xl overflow-hidden bg-slate-200 mb-8">

            <?php if (!empty($eventImage)): ?>

                <img
                    src="<?php echo htmlspecialchars($eventImage); ?>"
                    alt="<?php echo htmlspecialchars($event['event_name']); ?>"
                    class="w-full h-full object-cover"
                    onerror="this.style.display='none';"
                >

            <?php else: ?>

                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">

                    <i class="fa-solid fa-calendar-days text-6xl mb-4"></i>

                    <p class="font-medium">
                        No event image available
                    </p>

                </div>

            <?php endif; ?>

        </div>

        <!-- Event Heading -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-8">

                <section>

                    <div class="flex flex-wrap items-center gap-3 mb-4">

                        <?php
                        $statusColors = [
                            'confirmed' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            'completed' => 'bg-slate-200 text-slate-600'
                        ];

                        $statusClass = $statusColors[$event['status']]
                            ?? 'bg-slate-100 text-slate-600';
                        ?>

                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($event['status']); ?>
                        </span>

                        <span class="text-sm text-slate-500">
                            <?php echo htmlspecialchars($event['organization_name']); ?>
                        </span>

                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold text-[#1E3A5F]">
                        <?php echo htmlspecialchars($event['event_name']); ?>
                    </h1>

                </section>

                <!-- Event Description -->
                <section class="bg-white border border-slate-200 rounded-xl p-6">

                    <h2 class="text-xl font-bold text-[#1E3A5F] mb-4">
                        About This Event
                    </h2>

                    <p class="text-slate-600 leading-relaxed whitespace-pre-line">
                        <?php echo htmlspecialchars($event['description'] ?: 'No event description available.'); ?>
                    </p>

                </section>

                <!-- Event Schedule -->
                <section class="bg-white border border-slate-200 rounded-xl p-6">

                    <h2 class="text-xl font-bold text-[#1E3A5F] mb-5">
                        Event Schedule
                    </h2>

                    <?php if (empty($schedules)): ?>

                        <p class="text-slate-500">
                            No schedule has been assigned to this event yet.
                        </p>

                    <?php else: ?>

                        <div class="space-y-5">

                            <?php foreach ($schedules as $schedule): ?>

                                <div class="flex gap-4">

                                    <div class="w-11 h-11 shrink-0 rounded-xl bg-blue-50 text-[#1E3A5F] flex items-center justify-center">
                                        <i class="fa-solid fa-calendar-day text-lg"></i>
                                    </div>

                                    <div class="min-w-0">

                                        <h3 class="font-semibold text-slate-800">
                                            <?php echo date('F d, Y', strtotime($schedule['event_date'])); ?>
                                        </h3>

                                        <p class="text-sm text-slate-500 mt-1">
                                            <?php
                                            echo date('g:i A', strtotime($schedule['start_time']));
                                            echo ' - ';
                                            echo date('g:i A', strtotime($schedule['end_time']));
                                            ?>
                                        </p>

                                        <p class="text-sm text-slate-500 mt-1">
                                            <i class="fa-solid fa-location-dot mr-1"></i>
                                            <?php echo htmlspecialchars($schedule['venue_name']); ?>
                                        </p>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </section>

                <!-- Venue and Map -->
                <section class="bg-white border border-slate-200 rounded-xl p-6">

                    <h2 class="text-xl font-bold text-[#1E3A5F] mb-5">
                        Venue Location
                    </h2>

                    <?php if (!empty($schedules)): ?>

                        <?php foreach ($schedules as $schedule): ?>

                            <div class="mb-5">

                                <h3 class="font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($schedule['venue_name']); ?>
                                </h3>

                                <p class="text-sm text-slate-500 mt-1">
                                    <i class="fa-solid fa-map-pin mr-1"></i>
                                    <?php echo htmlspecialchars($schedule['location']); ?>
                                </p>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <p class="text-slate-500 mb-4">
                            Venue information is not available yet.
                        </p>

                    <?php endif; ?>

                    <?php if ($mapSchedule): ?>

                        <div id="eventMap"
                             class="rounded-xl overflow-hidden border border-slate-200"
                             data-lat="<?php echo htmlspecialchars((string)$mapSchedule['latitude']); ?>"
                             data-lng="<?php echo htmlspecialchars((string)$mapSchedule['longitude']); ?>"
                             data-venue="<?php echo htmlspecialchars($mapSchedule['venue_name'], ENT_QUOTES); ?>">
                        </div>

                        <p class="text-xs text-slate-400 mt-3">
                            Map pin shows the coordinates saved for this venue.
                        </p>

                        <a
                            href="https://www.openstreetmap.org/?mlat=<?php echo urlencode((string)$mapSchedule['latitude']); ?>&mlon=<?php echo urlencode((string)$mapSchedule['longitude']); ?>#map=18/<?php echo urlencode((string)$mapSchedule['latitude']); ?>/<?php echo urlencode((string)$mapSchedule['longitude']); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 mt-3 text-sm font-semibold text-[#1E3A5F] hover:underline">

                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            Open in OpenStreetMap

                        </a>

                    <?php else: ?>

                        <div class="rounded-xl bg-slate-100 p-8 text-center">

                            <i class="fa-solid fa-map-location-dot text-4xl text-slate-400 mb-3"></i>

                            <p class="font-medium text-slate-600">
                                Map coordinates unavailable
                            </p>

                            <p class="text-sm text-slate-500 mt-1">
                                The venue's map location has not been set yet.
                            </p>

                        </div>

                    <?php endif; ?>

                </section>

            </div>

            <!-- Registration and Countdown Sidebar -->
            <aside class="space-y-6">

                <!-- Countdown -->
                <section class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">

                    <h2 class="font-bold text-[#1E3A5F] mb-4">
                        Event Countdown
                    </h2>

                    <?php if ($nextSchedule): ?>

                        <div id="eventCountdown"
                             data-start="<?php echo htmlspecialchars($nextSchedule['start_datetime']); ?>">

                            <p class="text-sm text-slate-500 mb-3">
                                Event starts in
                            </p>

                            <div class="grid grid-cols-4 gap-2">

                                <div class="bg-slate-50 rounded-lg p-3 text-center">
                                    <p id="countdownDays" class="text-2xl font-bold text-[#1E3A5F]">00</p>
                                    <p class="text-xs text-slate-400">Days</p>
                                </div>

                                <div class="bg-slate-50 rounded-lg p-3 text-center">
                                    <p id="countdownHours" class="text-2xl font-bold text-[#1E3A5F]">00</p>
                                    <p class="text-xs text-slate-400">Hours</p>
                                </div>

                                <div class="bg-slate-50 rounded-lg p-3 text-center">
                                    <p id="countdownMinutes" class="text-2xl font-bold text-[#1E3A5F]">00</p>
                                    <p class="text-xs text-slate-400">Minutes</p>
                                </div>

                                <div class="bg-slate-50 rounded-lg p-3 text-center">
                                    <p id="countdownSeconds" class="text-2xl font-bold text-[#1E3A5F]">00</p>
                                    <p class="text-xs text-slate-400">Seconds</p>
                                </div>

                            </div>

                        </div>

                    <?php elseif ($isOngoing): ?>

                        <div class="bg-green-50 text-green-700 rounded-lg p-4 text-center font-semibold">
                            <i class="fa-solid fa-circle-play mr-2"></i>
                            This event is currently ongoing!
                        </div>

                    <?php elseif ($event['status'] === 'cancelled'): ?>

                        <div class="bg-red-50 text-red-600 rounded-lg p-4 text-center font-semibold">
                            This event has been cancelled.
                        </div>

                    <?php else: ?>

                        <div class="bg-slate-100 text-slate-600 rounded-lg p-4 text-center font-semibold">
                            This event has ended.
                        </div>

                    <?php endif; ?>

                </section>

                <!-- Registration -->
                <section class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">

                    <h2 class="font-bold text-[#1E3A5F] mb-3">
                        Event Registration
                    </h2>

                    <?php if (!$studentId): ?>

                        <p class="text-sm text-slate-500 mb-4">
                            Log in to your UniSync account to register for this event.
                        </p>

                        <a href="login.php"
                        class="block w-full text-center bg-[#1E3A5F] text-white font-semibold py-3 rounded-lg hover:bg-[#294D78]">
                            Log In to Register
                        </a>

                    <?php else: ?>

                        <!-- Registration Confirmation -->
                        <div id="registrationStatus"
                            class="<?php echo $isRegistered ? '' : 'hidden'; ?>">

                            <div class="bg-green-50 text-green-700 rounded-lg p-4 text-center">

                                <i class="fa-solid fa-circle-check text-xl mb-2"></i>

                                <p class="font-semibold">
                                    You're registered!
                                </p>

                                <p class="text-sm mt-1">
                                    You're already registered for this event.
                                </p>

                            </div>

                            <!-- Cancellation link outside the green box -->
                            <p class="text-sm text-slate-500 text-center mt-3">
                                Changed your mind?

                                <a href="#"
                                id="unregisterLink"
                                class="font-semibold text-red-600 hover:underline">
                                    Cancel registration
                                </a>
                            </p>

                        </div>

                        <!-- Registration Button -->
                        <?php if (!$isRegistered && $canRegister): ?>

                            <button
                                id="registerButton"
                                type="button"
                                class="w-full bg-[#1E3A5F] text-white font-semibold py-3 rounded-lg hover:bg-[#294D78] transition-colors">

                                Register for Event

                            </button>

                        <?php elseif (!$isRegistered): ?>

                            <p class="text-sm text-slate-500">
                                Registration is currently unavailable for this event.
                            </p>

                        <?php endif; ?>

                        <div id="registrationMessage"
                            class="hidden mt-4 p-3 rounded-lg text-sm">
                        </div>

                    <?php endif; ?>

                </section>

            </aside>

        </div>

    </main>

    <?php include '../php/components/footer.php'; ?>

    <script src="../javascript/app/event.js"></script>

</body>
</html>
