<?php
session_start();

require_once __DIR__ . '/../../../config/db.php';

header('Content-Type: application/json');

$accountId = $_SESSION['account_id'] ?? null;

if (!$accountId) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in.'
    ]);
    exit;
}

$eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

if (!$eventId || $eventId < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid event.'
    ]);
    exit;
}

try {

    $query = $pdo->prepare(
        "UPDATE registration
        SET status = 'cancelled'
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
            'message' => 'Registration cancelled successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration not found.'
        ]);
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to cancel registration.'
    ]);
}
