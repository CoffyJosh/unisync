<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

function updateEventResponse($success, $message, $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Newly uploaded image file, removed again if saving fails.
$savedImageFile = null;

function removeImageFile($path) {
    if ($path && is_file($path)) {
        @unlink($path);
    }
}

// Reuse this account's venue if the same name is already pinned at the
// same spot; otherwise create a new venue row.
function findOrCreateVenue(PDO $pdo, $accountId, $venueName, $location, $capacity, $latitude, $longitude, array &$cache) {
    $key = $venueName . '|' . round($latitude, 4) . '|' . round($longitude, 4);

    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $lookup = $pdo->prepare("
        SELECT venue_id
        FROM VENUE
        WHERE account_id = :account_id
          AND venue_name = :venue_name
          AND status = 'active'
          AND ABS(latitude - CAST(:latitude AS NUMERIC)) < 0.0005
          AND ABS(longitude - CAST(:longitude AS NUMERIC)) < 0.0005
        LIMIT 1
    ");
    $lookup->execute([
        ':account_id' => $accountId,
        ':venue_name' => $venueName,
        ':latitude' => $latitude,
        ':longitude' => $longitude
    ]);

    $venueId = $lookup->fetchColumn();

    if ($venueId === false) {
        $insert = $pdo->prepare("
            INSERT INTO VENUE (account_id, venue_name, location, capacity, latitude, longitude, status)
            VALUES (:account_id, :venue_name, :location, :capacity, :latitude, :longitude, 'active')
            RETURNING venue_id
        ");
        $insert->execute([
            ':account_id' => $accountId,
            ':venue_name' => $venueName,
            ':location' => $location,
            ':capacity' => $capacity,
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);
        $venueId = $insert->fetchColumn();
    }

    $cache[$key] = (int) $venueId;
    return $cache[$key];
}

try {
    require_once __DIR__ . '/../../../config/db.php';
    $pdo = $pdo ?? $conn ?? $db ?? null;
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection is unavailable.');
    }

    $accountId = $_SESSION['account_id'] ?? null;
    if (!$accountId) {
        http_response_code(401);
        updateEventResponse(false, 'Your session has expired. Please log in again.');
    }

    $eventId = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT);
    $eventName = trim($_POST['event_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $removeImage = ($_POST['remove_image'] ?? '0') === '1';
    $rawSchedules = $_POST['schedules'] ?? '[]';

    if (!$eventId || $eventName === '' || mb_strlen($eventName) > 200) {
        http_response_code(422);
        updateEventResponse(false, 'Enter a valid event name.');
    }

    $schedules = json_decode($rawSchedules, true);
    if (!is_array($schedules) || count($schedules) > 100) {
        http_response_code(422);
        updateEventResponse(false, 'The schedule data is invalid.');
    }

    // Load the event.
    $eventQuery = $pdo->prepare("
        SELECT event_id, organization_id, event_image, status
        FROM EVENT
        WHERE event_id = :event_id
          AND status <> 'deleted'
        LIMIT 1
    ");
    $eventQuery->execute([':event_id' => $eventId]);
    $event = $eventQuery->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        http_response_code(404);
        updateEventResponse(false, 'Event not found.');
    }

    // Only Admins and Managers of the event's organization may edit it.
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
        ':organization_id' => $event['organization_id']
    ]);
    if (!$permission->fetchColumn()) {
        http_response_code(403);
        updateEventResponse(false, 'You are not authorized to edit this event.');
    }

    // Image handling: keep, replace, or remove.
    $oldImagePath = $event['event_image'];
    $imagePath = $oldImagePath;
    $deleteOldImage = false;

    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image = $_FILES['event_image'];
        if ($image['error'] !== UPLOAD_ERR_OK || $image['size'] > 5 * 1024 * 1024) {
            http_response_code(422);
            updateEventResponse(false, 'The image upload failed or exceeds 5 MB.');
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
            updateEventResponse(false, 'Only JPG, PNG, and WEBP images are allowed.');
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
        $deleteOldImage = true;

    } elseif ($removeImage) {
        $imagePath = null;
        $deleteOldImage = true;
    }

    // Validate every submitted schedule before touching the database.
    $submitted = [];
    $submittedIds = [];

    foreach ($schedules as $index => $schedule) {
        $scheduleId = isset($schedule['schedule_id']) && $schedule['schedule_id'] !== null
            ? filter_var($schedule['schedule_id'], FILTER_VALIDATE_INT)
            : null;
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

        if ($scheduleId === false || $venueName === '' || mb_strlen($venueName) > 150 || $location === ''
            || !$validDate || !$validTime || !$validCoordinates
            || ($capacity !== null && (!$capacity || $capacity < 1))) {
            throw new InvalidArgumentException('Schedule ' . ($index + 1) . ' has missing or invalid information.');
        }

        if ($scheduleId !== null) {
            $submittedIds[] = $scheduleId;
        }

        $submitted[] = [
            'schedule_id' => $scheduleId,
            'venue_name' => $venueName,
            'location' => $location,
            'capacity' => $capacity,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'event_date' => $date,
            'start_time' => $start,
            'end_time' => $end
        ];
    }

    $pdo->beginTransaction();

    // Update the event itself.
    $eventUpdate = $pdo->prepare("
        UPDATE EVENT
        SET event_name = :event_name,
            description = :description,
            event_image = :event_image
        WHERE event_id = :event_id
    ");
    $eventUpdate->execute([
        ':event_name' => $eventName,
        ':description' => $description !== '' ? $description : null,
        ':event_image' => $imagePath,
        ':event_id' => $eventId
    ]);

    // Current schedules (the ones the edit page shows).
    $currentQuery = $pdo->prepare("
        SELECT
            es.schedule_id,
            es.venue_id,
            es.event_date,
            es.start_time,
            es.end_time,
            v.venue_name,
            v.location,
            v.latitude,
            v.longitude
        FROM EVENT_SCHEDULE es
        INNER JOIN VENUE v
            ON es.venue_id = v.venue_id
        WHERE es.event_id = :event_id
          AND es.status IN ('pending', 'confirmed', 'completed')
    ");
    $currentQuery->execute([':event_id' => $eventId]);

    $current = [];
    foreach ($currentQuery->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $current[(int) $row['schedule_id']] = $row;
    }

    foreach ($submittedIds as $submittedId) {
        if (!isset($current[$submittedId])) {
            throw new InvalidArgumentException('One of the schedules no longer belongs to this event. Reload the page and try again.');
        }
    }

    // Removed schedules are cancelled (hidden everywhere, not erased).
    $cancelSchedule = $pdo->prepare("
        UPDATE EVENT_SCHEDULE
        SET status = 'cancelled'
        WHERE schedule_id = :schedule_id
          AND event_id = :event_id
    ");

    foreach ($current as $currentId => $row) {
        if (!in_array($currentId, $submittedIds, true)) {
            $cancelSchedule->execute([
                ':schedule_id' => $currentId,
                ':event_id' => $eventId
            ]);
        }
    }

    $updateSchedule = $pdo->prepare("
        UPDATE EVENT_SCHEDULE
        SET venue_id = :venue_id,
            event_date = :event_date,
            start_time = :start_time,
            end_time = :end_time,
            status = 'confirmed'
        WHERE schedule_id = :schedule_id
          AND event_id = :event_id
    ");
    $insertSchedule = $pdo->prepare("
        INSERT INTO EVENT_SCHEDULE (event_id, venue_id, event_date, start_time, end_time, status)
        VALUES (:event_id, :venue_id, :event_date, :start_time, :end_time, 'confirmed')
    ");

    $venueCache = [];

    foreach ($submitted as $item) {

        if ($item['schedule_id'] !== null) {

            $existing = $current[$item['schedule_id']];

            $sameVenue = $existing['venue_name'] === $item['venue_name']
                && $existing['location'] === $item['location']
                && $existing['latitude'] !== null
                && $existing['longitude'] !== null
                && abs((float) $existing['latitude'] - $item['latitude']) < 0.0000005
                && abs((float) $existing['longitude'] - $item['longitude']) < 0.0000005;

            $sameTime = $existing['event_date'] === $item['event_date']
                && substr($existing['start_time'], 0, 5) === $item['start_time']
                && substr($existing['end_time'], 0, 5) === $item['end_time'];

            // Nothing changed: leave the schedule exactly as it is.
            if ($sameVenue && $sameTime) {
                continue;
            }

            $venueId = $sameVenue
                ? (int) $existing['venue_id']
                : findOrCreateVenue(
                    $pdo, $accountId, $item['venue_name'], $item['location'],
                    $item['capacity'], $item['latitude'], $item['longitude'], $venueCache
                );

            $updateSchedule->execute([
                ':venue_id' => $venueId,
                ':event_date' => $item['event_date'],
                ':start_time' => $item['start_time'],
                ':end_time' => $item['end_time'],
                ':schedule_id' => $item['schedule_id'],
                ':event_id' => $eventId
            ]);

        } else {

            $venueId = findOrCreateVenue(
                $pdo, $accountId, $item['venue_name'], $item['location'],
                $item['capacity'], $item['latitude'], $item['longitude'], $venueCache
            );

            $insertSchedule->execute([
                ':event_id' => $eventId,
                ':venue_id' => $venueId,
                ':event_date' => $item['event_date'],
                ':start_time' => $item['start_time'],
                ':end_time' => $item['end_time']
            ]);
        }
    }

    // Recalculate the event status from its remaining schedules.
    // pending = no schedules, completed = all ended, otherwise confirmed.
    // Cancelled events stay cancelled.
    $eventStatus = $event['status'];

    if (!in_array($eventStatus, ['cancelled', 'deleted'], true)) {

        $remainingQuery = $pdo->prepare("
            SELECT event_date, end_time
            FROM EVENT_SCHEDULE
            WHERE event_id = :event_id
              AND status IN ('pending', 'confirmed', 'completed')
        ");
        $remainingQuery->execute([':event_id' => $eventId]);
        $remaining = $remainingQuery->fetchAll(PDO::FETCH_ASSOC);

        if (count($remaining) === 0) {

            $eventStatus = 'pending';

        } else {

            $eventStatus = 'completed';
            $timezone = new DateTimeZone('Asia/Manila');
            $nowManila = new DateTimeImmutable('now', $timezone);

            foreach ($remaining as $row) {
                $endAt = new DateTimeImmutable(
                    $row['event_date'] . ' ' . $row['end_time'],
                    $timezone
                );

                if ($endAt > $nowManila) {
                    $eventStatus = 'confirmed';
                    break;
                }
            }
        }

        $statusUpdate = $pdo->prepare("
            UPDATE EVENT
            SET status = :status
            WHERE event_id = :event_id
        ");
        $statusUpdate->execute([
            ':status' => $eventStatus,
            ':event_id' => $eventId
        ]);
    }

    $pdo->commit();

    // The old image file is only removed once the changes are committed.
    if ($deleteOldImage && $oldImagePath) {
        $uploadsRoot = realpath(dirname(__DIR__, 3) . '/public/uploads/events');
        $oldFile = realpath(dirname(__DIR__, 3) . '/public/' . $oldImagePath);

        if ($uploadsRoot && $oldFile && strpos($oldFile, $uploadsRoot . DIRECTORY_SEPARATOR) === 0) {
            removeImageFile($oldFile);
        }
    }

    updateEventResponse(true, 'Event updated successfully.', [
        'event_id' => $eventId,
        'status' => $eventStatus
    ]);

} catch (InvalidArgumentException $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    removeImageFile($savedImageFile);
    http_response_code(422);
    updateEventResponse(false, $e->getMessage());
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    removeImageFile($savedImageFile);
    error_log('update_event.php: ' . $e->getMessage());
    http_response_code(500);
    updateEventResponse(false, 'The event could not be updated. No database changes were committed.');
}
?>