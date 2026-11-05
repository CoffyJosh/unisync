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
        e.organization_id,
        o.organization_name,
        o.university_name
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

// =====================================
// EVENT MANAGEMENT PERMISSIONS
// =====================================

$canManageEvent = false;
$attendees = [];
$registeredAttendeeCount = 0;

if ($accountId) {

    $roleQuery = $pdo->prepare(
        "SELECT role
         FROM membership
         WHERE account_id = :account_id
           AND organization_id = :organization_id
           AND status = 'active'"
    );

    $roleQuery->execute([
        ':account_id' => $accountId,
        ':organization_id' => $event['organization_id']
    ]);

    $accountRole = $roleQuery->fetchColumn();

    $canManageEvent = in_array(
        $accountRole,
        ['Admin', 'Manager'],
        true
    );
}


// =====================================
// LOAD ORGANIZATION MEMBERS / ATTENDEES
// =====================================
// Includes every active member of the organization plus any account
// actively registered for this event (even if not a member).

if ($canManageEvent) {

    $attendeeQuery = $pdo->prepare(
        "SELECT
            a.account_id,
            a.account_name,
            a.email,
            CASE
                WHEN m.membership_id IS NOT NULL THEN 1
                ELSE 0
            END AS is_member,
            CASE
                WHEN r.registration_id IS NOT NULL THEN 1
                ELSE 0
            END AS is_registered
         FROM account a
         LEFT JOIN membership m
            ON m.account_id = a.account_id
            AND m.organization_id = :organization_id
            AND m.status = 'active'
         LEFT JOIN registration r
            ON r.account_id = a.account_id
            AND r.event_id = :event_id
            AND r.status = 'active'
         WHERE a.account_status = 'active'
           AND (
                m.membership_id IS NOT NULL
                OR r.registration_id IS NOT NULL
           )
         ORDER BY
            CASE WHEN r.registration_id IS NOT NULL THEN 0 ELSE 1 END,
            a.account_name ASC"
    );

    $attendeeQuery->execute([
        ':event_id' => $eventId,
        ':organization_id' => $event['organization_id']
    ]);

    $attendees = array_map(
        static function ($row) {
            return [
                'account_id' => (int) $row['account_id'],
                'account_name' => $row['account_name'],
                'email' => $row['email'],
                'is_member' => (int) $row['is_member'] === 1,
                'is_registered' => (int) $row['is_registered'] === 1
            ];
        },
        $attendeeQuery->fetchAll(PDO::FETCH_ASSOC)
    );

    $registeredAttendeeCount = count(array_filter(
        $attendees,
        static function ($attendee) {
            return $attendee['is_registered'];
        }
    ));
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

        if ($action === 'remove_attendee') {
            // Only organization Admins and Managers can remove attendees.
            if (!$canManageEvent) {

                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'You are not authorized to manage attendees.'
                ]);

                exit;
            }

            $targetAccountId = filter_input(
                INPUT_POST,
                'target_account_id',
                FILTER_VALIDATE_INT
            );

            if (!$targetAccountId || $targetAccountId < 1) {

                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid attendee.'
                ]);

                exit;
            }

            // Only the registration is soft-deleted. The account and
            // membership records are never touched.
            $removeQuery = $pdo->prepare(
                "UPDATE registration
                SET status = 'deleted'
                WHERE account_id = :account_id
                AND event_id = :event_id
                AND status = 'active'"
            );

            $removeQuery->execute([
                ':account_id' => $targetAccountId,
                ':event_id' => $eventId
            ]);

            if ($removeQuery->rowCount() > 0) {

                // Updated number of active registrations for the page.
                $countQuery = $pdo->prepare(
                    "SELECT COUNT(*)
                     FROM registration r
                     INNER JOIN account a
                        ON r.account_id = a.account_id
                     WHERE r.event_id = :event_id
                       AND r.status = 'active'
                       AND a.account_status = 'active'"
                );

                $countQuery->execute([
                    ':event_id' => $eventId
                ]);

                echo json_encode([
                    'success' => true,
                    'action' => 'remove_attendee',
                    'target_account_id' => $targetAccountId,
                    'registered_count' => (int) $countQuery->fetchColumn(),
                    'message' => 'Attendee successfully removed.'
                ]);

            } else {

                echo json_encode([
                    'success' => false,
                    'message' => 'This account is not registered for the event.'
                ]);
            }

            exit;
        }

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