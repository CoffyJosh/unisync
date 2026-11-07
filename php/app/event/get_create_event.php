<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

function createEventJson($success, $message, $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

try {
    require_once __DIR__ . '/../../../config/db.php';
    $pdo = $pdo ?? $conn ?? $db ?? null;
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection is unavailable.');
    }

    $accountId = $_SESSION['account_id'] ?? null;
    $organizationId = filter_input(INPUT_GET, 'organization_id', FILTER_VALIDATE_INT);

    if (!$accountId) {
        http_response_code(401);
        createEventJson(false, 'Please log in first.');
    }
    if (!$organizationId) {
        http_response_code(400);
        createEventJson(false, 'Invalid organization.');
    }

    $permission = $pdo->prepare("
        SELECT o.organization_id, o.organization_name
        FROM ORGANIZATION o
        JOIN MEMBERSHIP m ON m.organization_id = o.organization_id
        WHERE o.organization_id = :organization_id
          AND o.status = 'active'
          AND m.account_id = :account_id
          AND m.status = 'active'
          AND m.role IN ('Admin', 'Manager')
        LIMIT 1
    ");
    $permission->execute([
        ':organization_id' => $organizationId,
        ':account_id' => $accountId
    ]);
    $organization = $permission->fetch(PDO::FETCH_ASSOC);

    if (!$organization) {
        http_response_code(403);
        createEventJson(false, 'You are not authorized to create events for this organization.');
    }

    createEventJson(true, 'Organization verified.', [
        'organization' => $organization,
        'venues' => []
    ]);
} catch (Throwable $e) {
    error_log('get_create_event.php: ' . $e->getMessage());
    http_response_code(500);
    createEventJson(false, 'Unable to load event creation data.');
}
?>
