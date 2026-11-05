
<?php

require_once __DIR__ . '/../../db.php';

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

// Only POST requests are allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}

// Require an authenticated account.
$accountId = $_SESSION['account_id'] ?? null;

if (!$accountId) {
    respond(false, 'You must be logged in to join an organization.', 401);
}

// Validate organization ID.
$organizationId = filter_input(
    INPUT_POST,
    'organization_id',
    FILTER_VALIDATE_INT
);

if (!$organizationId || $organizationId <= 0) {
    respond(false, 'Invalid organization.', 400);
}

try {

    // Verify that the account is active.
    $accountStmt = $pdo->prepare("
        SELECT 1
        FROM account
        WHERE account_id = :account_id
          AND account_status = 'active'
    ");

    $accountStmt->execute([
        ':account_id' => $accountId
    ]);

    if (!$accountStmt->fetchColumn()) {
        respond(false, 'Your account is not active.', 403);
    }

    // Verify that the organization exists and is active.
    $organizationStmt = $pdo->prepare("
        SELECT 1
        FROM organization
        WHERE organization_id = :organization_id
          AND status = 'active'
    ");

    $organizationStmt->execute([
        ':organization_id' => $organizationId
    ]);

    if (!$organizationStmt->fetchColumn()) {
        respond(false, 'This organization no longer exists.', 404, ['organization_gone' => true]);
    }

    // Insert membership or reactivate an existing inactive membership.
    $membershipStmt = $pdo->prepare("
        INSERT INTO membership (
            account_id,
            organization_id,
            role,
            status
        )
        VALUES (
            :account_id,
            :organization_id,
            'Member',
            'active'
        )
        ON CONFLICT (account_id, organization_id)
        DO UPDATE SET
            role = 'Member',
            status = 'active',
            membership_date = CURRENT_DATE
        WHERE membership.status <> 'active'
        RETURNING membership_id
    ");

    $membershipStmt->execute([
        ':account_id' => $accountId,
        ':organization_id' => $organizationId
    ]);

    $membershipId = $membershipStmt->fetchColumn();

    if (!$membershipId) {
        respond(false, 'You are already a member of this organization.', 409);
    }

    respond(true, 'You have successfully joined the organization.');

} catch (PDOException $e) {

    error_log($e->getMessage());

    respond(false, 'Unable to join the organization. Please try again.', 500);

}
