<?php
require_once __DIR__ . '/../../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

function respond($success, $message, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
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

    // Lock the organization to prevent simultaneous membership changes.
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

    // Lock the current membership.
    $membershipStmt = $pdo->prepare("
        SELECT membership_id, role
        FROM membership
        WHERE account_id = :account_id
          AND organization_id = :organization_id
          AND status = 'active'
        FOR UPDATE
    ");
    $membershipStmt->execute([
        'account_id' => $accountId,
        'organization_id' => $organizationId
    ]);

    $membership = $membershipStmt->fetch(PDO::FETCH_ASSOC);

    if (!$membership) {
        $pdo->rollBack();
        respond(false, 'You are not an active member of this organization.', 403);
    }

    $leavingRole = $membership['role'];

    // Deactivate the departing member (the record is kept, not deleted).
    $leaveStmt = $pdo->prepare("
        UPDATE membership
        SET status = 'inactive'
        WHERE membership_id = :membership_id
    ");
    $leaveStmt->execute([
        'membership_id' => $membership['membership_id']
    ]);

    // Admin succession rules.
    if ($leavingRole === 'Admin') {

        // Is there another active Admin (with an active account)?
        $adminStmt = $pdo->prepare("
            SELECT m.membership_id
            FROM membership m
            INNER JOIN account a
                ON a.account_id = m.account_id
            WHERE m.organization_id = :organization_id
              AND m.status = 'active'
              AND m.role = 'Admin'
              AND a.account_status = 'active'
            LIMIT 1
        ");
        $adminStmt->execute(['organization_id' => $organizationId]);

        if (!$adminStmt->fetchColumn()) {

            // Promote the earliest active Manager.
            $successorStmt = $pdo->prepare("
                SELECT m.membership_id
                FROM membership m
                INNER JOIN account a
                    ON a.account_id = m.account_id
                WHERE m.organization_id = :organization_id
                  AND m.status = 'active'
                  AND m.role = 'Manager'
                  AND a.account_status = 'active'
                ORDER BY m.membership_date ASC, m.membership_id ASC
                LIMIT 1
                FOR UPDATE OF m
            ");
            $successorStmt->execute([
                'organization_id' => $organizationId
            ]);

            $successorId = $successorStmt->fetchColumn();

            // If no Manager exists, promote the earliest active Member.
            if (!$successorId) {
                $successorStmt = $pdo->prepare("
                    SELECT m.membership_id
                    FROM membership m
                    INNER JOIN account a
                        ON a.account_id = m.account_id
                    WHERE m.organization_id = :organization_id
                      AND m.status = 'active'
                      AND m.role = 'Member'
                      AND a.account_status = 'active'
                    ORDER BY m.membership_date ASC, m.membership_id ASC
                    LIMIT 1
                    FOR UPDATE OF m
                ");
                $successorStmt->execute([
                    'organization_id' => $organizationId
                ]);

                $successorId = $successorStmt->fetchColumn();
            }

            if ($successorId) {
                $promoteStmt = $pdo->prepare("
                    UPDATE membership
                    SET role = 'Admin'
                    WHERE membership_id = :membership_id
                ");
                $promoteStmt->execute([
                    'membership_id' => $successorId
                ]);
            } else {
                // No active members remain. Deactivate the organization
                // (soft delete; the record is kept).
                $deleteStmt = $pdo->prepare("
                    UPDATE organization
                    SET status = 'deleted'
                    WHERE organization_id = :organization_id
                ");
                $deleteStmt->execute([
                    'organization_id' => $organizationId
                ]);

                $pdo->commit();
                respond(true, 'You left the organization. Since no members remained, the organization was deactivated.');
            }
        }
    }

    $pdo->commit();

    respond(true, 'You successfully left the organization.');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Leave organization error: ' . $e->getMessage());
    respond(false, 'An error occurred while leaving the organization.', 500);
}