<?php
session_start();

require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

$studentId = $_SESSION['student_id'] ?? null;

if (!$studentId) {
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