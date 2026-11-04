
<?php
require_once __DIR__ . '/../../db.php';

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
    header('Content-Type: application/json; charset=UTF-8');

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
        (float) $schedule['latitude'] >= -90 &&
        (float) $schedule['latitude'] <= 90 &&
        (float) $schedule['longitude'] >= -180 &&
        (float) $schedule['longitude'] <= 180
    ) {
        $mapSchedule = $schedule;
        break;
    }
}

$eventImage = $event['event_image'] ?? '';
