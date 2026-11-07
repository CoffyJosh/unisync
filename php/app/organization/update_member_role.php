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
$membershipId = filter_input(INPUT_POST, 'membership_id', FILTER_VALIDATE_INT);
$newRole = $_POST['role'] ?? '';

if (!$accountId) {
    respond(false, 'Please log in first.', 401);
}

if (!$membershipId || $membershipId <= 0) {
    respond(false, 'Invalid membership.', 400);
}

$allowedRoles = ['Member', 'Manager', 'Admin'];

if (!in_array($newRole, $allowedRoles, true)) {
    respond(false, 'Invalid role selected.', 400);
}

try {
    $pdo->beginTransaction();

    // Retrieve and lock the target membership and its organization.
    // Locking the organization row serializes simultaneous role changes,
    // so two Admins cannot demote each other at the same time.
    $targetStmt = $pdo->prepare("
        SELECT
            m.membership_id,
            m.account_id,
            m.organization_id,
            m.role
        FROM membership m
        INNER JOIN organization o
            ON o.organization_id = m.organization_id
        WHERE m.membership_id = :membership_id
          AND m.status = 'active'
          AND o.status = 'active'
        FOR UPDATE OF m, o
    ");

    $targetStmt->execute([
        'membership_id' => $membershipId
    ]);

    $target = $targetStmt->fetch(PDO::FETCH_ASSOC);

    if (!$target) {
        // Was the organization itself deleted?
        $goneStmt = $pdo->prepare("
            SELECT o.status
            FROM membership m
            INNER JOIN organization o
                ON o.organization_id = m.organization_id
            WHERE m.membership_id = :membership_id
        ");
        $goneStmt->execute(['membership_id' => $membershipId]);
        $orgStatus = $goneStmt->fetchColumn();

        $pdo->rollBack();

        if ($orgStatus !== false && $orgStatus !== 'active') {
            respond(false, 'This organization no longer exists.', 404, ['organization_gone' => true]);
        }

        respond(false, 'Active member not found.', 404);
    }

    $isSelf = (int) $target['account_id'] === (int) $accountId;

    // Verify that the current user is an Admin of this organization.
    $adminStmt = $pdo->prepare("
        SELECT membership_id
        FROM membership
        WHERE account_id = :account_id
          AND organization_id = :organization_id
          AND role = 'Admin'
          AND status = 'active'
        LIMIT 1
        FOR UPDATE
    ");

    $adminStmt->execute([
        'account_id' => $accountId,
        'organization_id' => $target['organization_id']
    ]);

    if (!$adminStmt->fetchColumn()) {
        $pdo->rollBack();
        respond(false, 'Only organization Admins can change member roles.', 403);
    }

    // Nothing to change.
    if ($target['role'] === $newRole) {
        $pdo->rollBack();
        respond(true, 'Role is already ' . $newRole . '.', 200, [
            'role' => $newRole,
            'is_self' => $isSelf
        ]);
    }

    // An Admin may demote themselves only if another active Admin exists.
    if ($isSelf && $newRole !== 'Admin') {
        $otherAdminStmt = $pdo->prepare("
            SELECT 1
            FROM membership m
            INNER JOIN account a
                ON a.account_id = m.account_id
            WHERE m.organization_id = :organization_id
              AND m.account_id <> :account_id
              AND m.role = 'Admin'
              AND m.status = 'active'
              AND a.account_status = 'active'
            LIMIT 1
        ");

        $otherAdminStmt->execute([
            'organization_id' => $target['organization_id'],
            'account_id' => $accountId
        ]);

        if (!$otherAdminStmt->fetchColumn()) {
            $pdo->rollBack();
            respond(
                false,
                'You are the only Admin of this organization. Promote another member to Admin before changing your own role.',
                403
            );
        }
    }

    // Save the new role.
    $updateStmt = $pdo->prepare("
        UPDATE membership
        SET role = :role
        WHERE membership_id = :membership_id
          AND status = 'active'
    ");

    $updateStmt->execute([
        'role' => $newRole,
        'membership_id' => $membershipId
    ]);

    $pdo->commit();

    respond(true, 'Member role successfully updated to ' . $newRole . '.', 200, [
        'role' => $newRole,
        'is_self' => $isSelf
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Update member role error: ' . $e->getMessage());

    respond(false, 'An error occurred while updating the member role.', 500);
}