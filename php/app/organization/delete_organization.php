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
    $pdo->beginTransaction();

    // Lock the organization.
    $orgStmt = $pdo->prepare("
        SELECT organization_id
        FROM organization
        WHERE organization_id = :organization_id
          AND status = 'active'
        FOR UPDATE
    ");
    $orgStmt->execute(['organization_id' => $organizationId]);

    if (!$orgStmt->fetchColumn()) {
        $pdo->rollBack();
        respond(false, 'This organization no longer exists.', 404, ['organization_gone' => true]);
    }

    // Only an active Admin of this organization may delete it.
    $adminStmt = $pdo->prepare("
        SELECT 1
        FROM membership
        WHERE account_id = :account_id
          AND organization_id = :organization_id
          AND role = 'Admin'
          AND status = 'active'
        LIMIT 1
    ");
    $adminStmt->execute([
        'account_id' => $accountId,
        'organization_id' => $organizationId
    ]);

    if (!$adminStmt->fetchColumn()) {
        $pdo->rollBack();
        respond(false, 'Only organization Admins can delete the organization.', 403);
    }

    // Soft delete (same status the leave flow uses). Nothing is removed from the table.
    $deleteStmt = $pdo->prepare("
        UPDATE organization
        SET status = 'deleted'
        WHERE organization_id = :organization_id
    ");
    $deleteStmt->execute(['organization_id' => $organizationId]);

    $pdo->commit();

    respond(true, 'The organization has been deleted.');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Delete organization error: ' . $e->getMessage());
    respond(false, 'An error occurred while deleting the organization.', 500);
}