<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

function createEventResponse($success, $message, $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Path of an uploaded image, so it can be removed if publishing fails.
$savedImageFile = null;

function removeSavedImage($path) {
    if ($path && is_file($path)) {
        @unlink($path);
    }
}

try {
    require_once __DIR__ . '/../../db.php';
    $pdo = $pdo ?? $conn ?? $db ?? null;
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection is unavailable.');
    }

    $accountId = $_SESSION['account_id'] ?? null;
    if (!$accountId) {
        http_response_code(401);
        createEventResponse(false, 'Your session has expired. Please log in again.');
    }

    $organizationId = filter_var($_POST['organization_id'] ?? null, FILTER_VALIDATE_INT);
    $eventName = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $rawSchedules = $_POST['schedules'] ?? '[]';

    if (!$organizationId || $eventName === '' || mb_strlen($eventName) > 200) {
        http_response_code(422);
        createEventResponse(false, 'Enter a valid event name.');
    }

    $schedules = json_decode($rawSchedules, true);
    if (!is_array($schedules) || count($schedules) > 100) {
        http_response_code(422);
        createEventResponse(false, 'The schedule data is invalid.');
    }

    $permission = $pdo->prepare("
        SELECT 1 FROM MEMBERSHIP
        WHERE account_id = :account_id
          AND organization_id = :organization_id
          AND status = 'active'
          AND role IN ('Admin', 'Manager')
        LIMIT 1
    ");
    $permission->execute([
        ':account_id' => $accountId,
        ':organization_id' => $organizationId
    ]);
    if (!$permission->fetchColumn()) {
        http_response_code(403);
        createEventResponse(false, 'You are not authorized to publish for this organization.');
    }

    $imagePath = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image = $_FILES['event_image'];
        if ($image['error'] !== UPLOAD_ERR_OK || $image['size'] > 5 * 1024 * 1024) {
            http_response_code(422);
            createEventResponse(false, 'The image upload failed or exceeds 5 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($image['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
        if (!isset($extensions[$mime])) {
            http_response_code(422);
            createEventResponse(false, 'Only JPG, PNG, and WEBP images are allowed.');
        }

        $uploadDirectory = dirname(__DIR__, 3) . '/public/uploads/events';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Unable to create event upload directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($image['tmp_name'], $uploadDirectory . '/' . $filename)) {
            throw new RuntimeException('Unable to save uploaded event image.');
        }
        $savedImageFile = $uploadDirectory . '/' . $filename;
        $imagePath = 'uploads/events/' . $filename;
    }

    // pending = no schedules, completed = every schedule already ended, otherwise confirmed.
    $eventStatus = 'pending';

    if (count($schedules) > 0) {
        $eventStatus = 'completed';
        $timezone = new DateTimeZone('Asia/Manila');
        $nowManila = new DateTimeImmutable('now', $timezone);

        foreach ($schedules as $scheduleCheck) {
            $endAt = DateTimeImmutable::createFromFormat(
                'Y-m-d H:i',
                ($scheduleCheck['event_date'] ?? '') . ' ' . ($scheduleCheck['end_time'] ?? ''),
                $timezone
            );

            if ($endAt === false || $endAt > $nowManila) {
                $eventStatus = 'confirmed';
                break;
            }
        }
    }

    $pdo->beginTransaction();

    $eventInsert = $pdo->prepare("
        INSERT INTO EVENT (organization_id, event_name, description, event_image, status)
        VALUES (:organization_id, :event_name, :description, :event_image, :status)
        RETURNING event_id
    ");
    $eventInsert->execute([
        ':organization_id' => $organizationId,
        ':event_name' => $eventName,
        ':description' => $description !== '' ? $description : null,
        ':event_image' => $imagePath,
        ':status' => $eventStatus
    ]);
    $eventId = (int)$eventInsert->fetchColumn();

    // Venue names are not unique. Reuse this account's venue only when the
    // same name is already pinned at the same spot; otherwise create a new one.
    $venueLookup = $pdo->prepare("
        SELECT venue_id
        FROM VENUE
        WHERE account_id = :account_id
          AND venue_name = :venue_name
          AND status = 'active'
          AND ABS(latitude - CAST(:latitude AS NUMERIC)) < 0.0005
          AND ABS(longitude - CAST(:longitude AS NUMERIC)) < 0.0005
        LIMIT 1
    ");
    $venueInsert = $pdo->prepare("
        INSERT INTO VENUE (account_id, venue_name, location, capacity, latitude, longitude, status)
        VALUES (:account_id, :venue_name, :location, :capacity, :latitude, :longitude, 'active')
        RETURNING venue_id
    ");
    $scheduleInsert = $pdo->prepare("
        INSERT INTO EVENT_SCHEDULE (event_id, venue_id, event_date, start_time, end_time, status)
        VALUES (:event_id, :venue_id, :event_date, :start_time, :end_time, 'confirmed')
    ");

    $venueCache = [];

    foreach ($schedules as $index => $schedule) {
        $venueName = trim($schedule['venue_name'] ?? '');
        $location = trim($schedule['location'] ?? '');
        $date = $schedule['event_date'] ?? '';
        $start = $schedule['start_time'] ?? '';
        $end = $schedule['end_time'] ?? '';
        $capacity = ($schedule['capacity'] ?? '') === '' ? null : filter_var($schedule['capacity'], FILTER_VALIDATE_INT);
        $latitude = filter_var($schedule['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
        $longitude = filter_var($schedule['longitude'] ?? null, FILTER_VALIDATE_FLOAT);

        $validDate = is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
        $validTime = is_string($start) && is_string($end)
            && preg_match('/^\d{2}:\d{2}$/', $start)
            && preg_match('/^\d{2}:\d{2}$/', $end)
            && $end > $start;
        $validCoordinates = $latitude !== false && $longitude !== false
            && $latitude >= -90 && $latitude <= 90
            && $longitude >= -180 && $longitude <= 180;

        if ($venueName === '' || mb_strlen($venueName) > 150 || $location === ''
            || !$validDate || !$validTime || !$validCoordinates
            || ($capacity !== null && (!$capacity || $capacity < 1))) {
            throw new InvalidArgumentException('Schedule ' . ($index + 1) . ' has missing or invalid information.');
        }

        $venueKey = $venueName . '|' . round($latitude, 4) . '|' . round($longitude, 4);

        if (isset($venueCache[$venueKey])) {

            $venueId = $venueCache[$venueKey];

        } else {

            $venueLookup->execute([
                ':account_id' => $accountId,
                ':venue_name' => $venueName,
                ':latitude' => $latitude,
                ':longitude' => $longitude
            ]);

            $venueId = $venueLookup->fetchColumn();

            if ($venueId === false) {

                $venueInsert->execute([
                    ':account_id' => $accountId,
                    ':venue_name' => $venueName,
                    ':location' => $location,
                    ':capacity' => $capacity,
                    ':latitude' => $latitude,
                    ':longitude' => $longitude
                ]);

                $venueId = $venueInsert->fetchColumn();
            }

            $venueId = (int) $venueId;
            $venueCache[$venueKey] = $venueId;
        }

        $scheduleInsert->execute([
            ':event_id' => $eventId,
            ':venue_id' => $venueId,
            ':event_date' => $date,
            ':start_time' => $start,
            ':end_time' => $end
        ]);
    }

    // The CASTs are required: PostgreSQL cannot infer the type of a bare
    // parameter in a SELECT list ("indeterminate datatype").
    $notificationMessage = 'A new event has been created: ' . $eventName;
    $notificationInsert = $pdo->prepare("
        INSERT INTO NOTIFICATION
            (account_id, organization_id, event_id, notification_message)
        SELECT m.account_id,
               CAST(:organization_id AS INTEGER),
               CAST(:event_id AS INTEGER),
               CAST(:message AS TEXT)
        FROM MEMBERSHIP m
        WHERE m.organization_id = :membership_organization_id
          AND m.status = 'active'
          AND m.account_id <> :creator_account_id
    ");
    $notificationInsert->execute([
        ':organization_id' => $organizationId,
        ':event_id' => $eventId,
        ':message' => $notificationMessage,
        ':membership_organization_id' => $organizationId,
        ':creator_account_id' => $accountId
    ]);

    $pdo->commit();

    createEventResponse(true, 'Event published successfully.', [
        'event_id' => $eventId,
        'status' => $eventStatus,
        'schedule_count' => count($schedules)
    ]);
} catch (InvalidArgumentException $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    removeSavedImage($savedImageFile);
    http_response_code(422);
    createEventResponse(false, $e->getMessage());
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    removeSavedImage($savedImageFile);
    error_log('create_event.php: ' . $e->getMessage());
    http_response_code(500);
    createEventResponse(false, 'The event could not be published. No database changes were committed.');
}
?>