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
$decision = $_POST['decision'] ?? '';

if (!$accountId) {
    respond(false, 'Please log in first.', 401);
}

if (!$membershipId || $membershipId <= 0) {
    respond(false, 'Invalid membership.', 400);
}

if (!in_array($decision, ['approve', 'reject'], true)) {
    respond(false, 'Invalid decision.', 400);
}

try {
    $pdo->beginTransaction();

    // Retrieve and lock the pending request and its organization.
    $targetStmt = $pdo->prepare("
        SELECT
            m.membership_id,
            m.account_id,
            m.organization_id,
            a.account_name
        FROM membership m
        INNER JOIN organization o
            ON o.organization_id = m.organization_id
        INNER JOIN account a
            ON a.account_id = m.account_id
        WHERE m.membership_id = :membership_id
          AND m.status = 'pending'
          AND o.status = 'active'
        FOR UPDATE OF m, o
    ");
    $targetStmt->execute(['membership_id' => $membershipId]);

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

        respond(false, 'This request is no longer pending. The user may have cancelled it.', 404);
    }

    // Only an active Admin of this organization may review requests.
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
        'organization_id' => $target['organization_id']
    ]);

    if (!$adminStmt->fetchColumn()) {
        $pdo->rollBack();
        respond(false, 'Only organization Admins can review join requests.', 403);
    }

    if ($decision === 'approve') {
        // The join date becomes the approval date.
        $updateStmt = $pdo->prepare("
            UPDATE membership
            SET status = 'active',
                role = 'Member',
                membership_date = CURRENT_DATE
            WHERE membership_id = :membership_id
              AND status = 'pending'
        ");
        $message = $target['account_name'] . ' is now a member.';
    } else {
        $updateStmt = $pdo->prepare("
            UPDATE membership
            SET status = 'rejected'
            WHERE membership_id = :membership_id
              AND status = 'pending'
        ");
        $message = $target['account_name'] . '\'s request was rejected.';
    }

    $updateStmt->execute(['membership_id' => $membershipId]);

    if ($updateStmt->rowCount() === 0) {
        $pdo->rollBack();
        respond(false, 'The request could not be updated.', 400);
    }

    $pdo->commit();

    respond(true, $message);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Review membership error: ' . $e->getMessage());

    respond(false, 'An error occurred while reviewing the request.', 500);
}