<?php

require_once __DIR__ . '/../../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

function respond($success, $message, $statusCode = 200, $extra = [])
{
    http_response_code($statusCode);

    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}

$accountId = $_SESSION['account_id'] ?? null;
$organizationId = filter_input(INPUT_POST, 'organization_id', FILTER_VALIDATE_INT);

if (!$accountId) {
    respond(false, 'Please log in first.', 401);
}

if (!$organizationId || $organizationId <= 0) {
    respond(false, 'Invalid organization.', 400);
}

try {

    // The organization may have been deleted while the page was open.
    $organizationStmt = $pdo->prepare("
        SELECT 1
        FROM organization
        WHERE organization_id = :organization_id
          AND status = 'active'
    ");
    $organizationStmt->execute([':organization_id' => $organizationId]);

    if (!$organizationStmt->fetchColumn()) {
        respond(false, 'This organization no longer exists.', 404, ['organization_gone' => true]);
    }

    // Cancel the account's own pending request (soft delete; the row is kept).
    $cancelStmt = $pdo->prepare("
        UPDATE membership
        SET status = 'deleted'
        WHERE account_id = :account_id
          AND organization_id = :organization_id
          AND status = 'pending'
        RETURNING membership_id
    ");
    $cancelStmt->execute([
        ':account_id' => $accountId,
        ':organization_id' => $organizationId
    ]);

    if (!$cancelStmt->fetchColumn()) {
        respond(false, 'You have no pending request for this organization. It may have already been reviewed.', 404);
    }

    respond(true, 'Your join request has been cancelled.');

} catch (PDOException $e) {

    error_log('Cancel join request error: ' . $e->getMessage());

    respond(false, 'Unable to cancel your request. Please try again.', 500);

}