
<?php

require_once __DIR__ . '/../../db.php';

$accountId = $_SESSION['account_id'] ?? null;
$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);


// Validate event ID.
if (!$eventId || $eventId < 1) {
    header('Location: browse.php');
    exit;
}


// Retrieve event and organization information.
$eventQuery = $pdo->prepare(
    "SELECT
        e.event_id,
        e.event_name,
        e.description,
        e.status,
        e.event_image,
        o.organization_name
     FROM event e
     INNER JOIN organization o
        ON e.organization_id = o.organization_id
     WHERE e.event_id = :event_id
       AND e.status <> 'deleted'
       AND o.status = 'active'"
);

$eventQuery->execute([
    ':event_id' => $eventId
]);

$event = $eventQuery->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}


// Retrieve confirmed and completed schedules with their venues.
$scheduleQuery = $pdo->prepare(
    "SELECT
        es.schedule_id,
        es.event_date,
        es.start_time,
        es.end_time,
        es.status AS schedule_status,
        v.venue_id,
        v.venue_name,
        v.location,
        v.latitude,
        v.longitude
     FROM event_schedule es
     INNER JOIN venue v
        ON es.venue_id = v.venue_id
     WHERE es.event_id = :event_id
       AND es.status IN ('pending', 'confirmed', 'completed')
       AND v.status = 'active'
     ORDER BY es.event_date ASC, es.start_time ASC"
);

$scheduleQuery->execute([
    ':event_id' => $eventId
]);

$schedules = $scheduleQuery->fetchAll(PDO::FETCH_ASSOC);
$eventStatus = strtolower($event['status']);

$hasConfirmedSchedule = false;
$hasPendingSchedule = false;
$hasFutureOrOngoingSchedule = false;

$timezone = new DateTimeZone('Asia/Manila');
$now = new DateTimeImmutable('now', $timezone);

foreach ($schedules as $schedule) {

    $status = strtolower($schedule['schedule_status']);

    if ($status === 'pending') {
        $hasPendingSchedule = true;
    }

    if ($status === 'confirmed') {
        $hasConfirmedSchedule = true;

        $start = new DateTimeImmutable(
            $schedule['event_date'] . ' ' . $schedule['start_time'],
            $timezone
        );

        $end = new DateTimeImmutable(
            $schedule['event_date'] . ' ' . $schedule['end_time'],
            $timezone
        );

        if ($now < $end) {
            $hasFutureOrOngoingSchedule = true;
        }
    }
}

// Preserve manually cancelled events.
if ($eventStatus !== 'cancelled' && $eventStatus !== 'deleted') {

    if (!$hasConfirmedSchedule) {
        $eventStatus = 'pending';

    } elseif (!$hasFutureOrOngoingSchedule) {
        $eventStatus = 'completed';

    } else {
        $eventStatus = 'confirmed';
    }
}

$event['status'] = $eventStatus;

// Count pending venue requests.
$pendingQuery = $pdo->prepare(
    "SELECT COUNT(*)
     FROM event_schedule
     WHERE event_id = :event_id
       AND status = 'pending'"
);

$pendingQuery->execute([
    ':event_id' => $eventId
]);

$pendingScheduleCount = (int) $pendingQuery->fetchColumn();


// Determine event timing using Manila time.
$timezone = new DateTimeZone('Asia/Manila');
$now = new DateTimeImmutable('now', $timezone);

$timingSchedules = [];
$mapSchedules = [];

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


    // Prepare schedule information for the reusable map.
    $mapSchedules[] = [
        'id' => (int) $schedule['schedule_id'],
        'venue_name' => $schedule['venue_name'],
        'location' => $schedule['location'],
        'latitude' => $schedule['latitude'] !== null
            ? (float) $schedule['latitude']
            : null,
        'longitude' => $schedule['longitude'] !== null
            ? (float) $schedule['longitude']
            : null,
        'start' => $start->format('c'),
        'end' => $end->format('c'),
        'status' => $schedule['schedule_status']
    ];


    // Only confirmed schedules participate in the countdown
    // and registration availability.
    if ($schedule['schedule_status'] === 'confirmed') {

        $timingSchedules[] = [
            'start' => $start->format('c'),
            'end' => $end->format('c')
        ];

        if ($now >= $start && $now < $end) {
            $isOngoing = true;
        }

        if ($now < $start) {
            $hasFutureSchedule = true;
        }
    }
}

unset($schedule);


// Determine registration status.
$isRegistered = false;

if ($accountId) {

    $registrationQuery = $pdo->prepare(
        "SELECT registration_id
         FROM registration
         WHERE account_id = :account_id
           AND event_id = :event_id
           AND status = 'active'"
    );

    $registrationQuery->execute([
        ':account_id' => $accountId,
        ':event_id' => $eventId
    ]);

    $isRegistered = (bool) $registrationQuery->fetch();
}


// Keep the variable name expected by event.php.
$registered = $isRegistered;


// Registration is allowed only for confirmed events
// with an ongoing or upcoming confirmed schedule.
$canRegister = !in_array(
    $event['status'],
    ['cancelled', 'completed', 'deleted'],
    true
);


// Handle AJAX event registration.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['ajax'])
) {

    header('Content-Type: application/json; charset=UTF-8');

    if (!$accountId) {

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


            // Check whether a registration already exists.
            $existingQuery = $pdo->prepare(
                'SELECT registration_id, status
                 FROM registration
                 WHERE account_id = :account_id
                   AND event_id = :event_id'
            );

            $existingQuery->execute([
                ':account_id' => $accountId,
                ':event_id' => $eventId
            ]);

            $existingRegistration = $existingQuery->fetch(PDO::FETCH_ASSOC);


            if ($existingRegistration) {

                if ($existingRegistration['status'] === 'active') {

                    echo json_encode([
                        'success' => false,
                        'message' => 'You are already registered for this event.'
                    ]);

                    exit;
                }


                // Reactivate a previous registration.
                $query = $pdo->prepare(
                    "UPDATE registration
                     SET status = 'active',
                         registration_date = CURRENT_TIMESTAMP
                     WHERE registration_id = :registration_id"
                );

                $query->execute([
                    ':registration_id' => $existingRegistration['registration_id']
                ]);

            } else {

                // Create a new registration.
                $query = $pdo->prepare(
                    "INSERT INTO registration (
                        account_id,
                        event_id,
                        status
                     )
                     VALUES (
                        :account_id,
                        :event_id,
                        'active'
                     )"
                );

                $query->execute([
                    ':account_id' => $accountId,
                    ':event_id' => $eventId
                ]);
            }


            echo json_encode([
                'success' => true,
                'action' => 'register',
                'message' => 'You have successfully registered for this event!'
            ]);

            exit;


        } elseif ($action === 'unregister') {

            // Soft-delete the registration.
            $query = $pdo->prepare(
                "UPDATE registration
                 SET status = 'deleted'
                 WHERE account_id = :account_id
                   AND event_id = :event_id
                   AND status = 'active'"
            );

            $query->execute([
                ':account_id' => $accountId,
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


// Event image.
$eventImage = $event['event_image'] ?? '';
