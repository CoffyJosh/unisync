
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

if (!$accountId) {
    respond(false, 'Please log in first.', 401);
}

if (!$membershipId || $membershipId <= 0) {
    respond(false, 'Invalid membership.', 400);
}

try {
    $pdo->beginTransaction();

    // Retrieve and lock the target membership and organization.
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

    // Prevent kicking yourself.
    if ((int) $target['account_id'] === (int) $accountId) {
        $pdo->rollBack();
        respond(false, 'You cannot kick yourself. Use Leave Organization instead.', 403);
    }

    // Check the current user's role in this organization.
    $actorStmt = $pdo->prepare("
        SELECT role
        FROM membership
        WHERE account_id = :account_id
          AND organization_id = :organization_id
          AND status = 'active'
        LIMIT 1
        FOR UPDATE
    ");

    $actorStmt->execute([
        'account_id' => $accountId,
        'organization_id' => $target['organization_id']
    ]);

    $actorRole = $actorStmt->fetchColumn();

    if (!$actorRole) {
        $pdo->rollBack();
        respond(false, 'You are not an active member of this organization.', 403);
    }

    // Admins can kick anyone except themselves.
    // Managers can only kick regular Members.
    if ($actorRole === 'Manager' && $target['role'] !== 'Member') {
        $pdo->rollBack();
        respond(false, 'Managers can only kick regular Members.', 403);
    }

    if (!in_array($actorRole, ['Admin', 'Manager'], true)) {
        $pdo->rollBack();
        respond(false, 'You do not have permission to kick members.', 403);
    }

    // Soft-remove the target membership.
    $kickStmt = $pdo->prepare("
        UPDATE membership
        SET status = 'inactive'
        WHERE membership_id = :membership_id
          AND status = 'active'
    ");

    $kickStmt->execute([
        'membership_id' => $membershipId
    ]);

    if ($kickStmt->rowCount() === 0) {
        $pdo->rollBack();
        respond(false, 'The member could not be removed.', 400);
    }

    $pdo->commit();

    respond(true, 'Member successfully removed from the organization.');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Kick member error: ' . $e->getMessage());

    respond(false, 'An error occurred while removing the member.', 500);
}
