<?php

require_once __DIR__ . '/../../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectToOrganization($organizationId, $organizationName)
{
    $slug = strtolower(trim($organizationName));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    header(
        'Location: ../../../public/organization.php?id='
        . (int) $organizationId
        . '&name=' . urlencode($slug)
        . '#events'
    );
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../public/organizations.php');
    exit;
}

$accountId = $_SESSION['account_id'] ?? null;
$organizationId = filter_input(INPUT_POST, 'organization_id', FILTER_VALIDATE_INT);

$organizationName = trim($_POST['organization_name'] ?? '');
$universityName = trim($_POST['university_name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (!$accountId || !$organizationId) {
    header('Location: ../../../public/organizations.php');
    exit;
}

if ($organizationName === '' || $universityName === '') {
    exit('Organization name and university name are required.');
}

if (strlen($organizationName) > 150 || strlen($universityName) > 150) {
    exit('Organization name or university name is too long.');
}

try {
    // The organization may have been deleted while the form was open.
    $activeCheck = $pdo->prepare("
        SELECT 1
        FROM organization
        WHERE organization_id = :organization_id
          AND status = 'active'
    ");
    $activeCheck->execute([':organization_id' => $organizationId]);

    if (!$activeCheck->fetchColumn()) {
        echo '<script>alert("This organization no longer exists."); window.location.href = "../../../public/browse.php";</script>';
        exit;
    }

    // Verify that the current account is an Admin or Manager.
    $permissionQuery = $pdo->prepare("
        SELECT 1
        FROM membership
        WHERE account_id = :account_id
          AND organization_id = :organization_id
          AND status = 'active'
          AND LOWER(role) IN ('admin', 'manager')
    ");

    $permissionQuery->execute([
        ':account_id' => $accountId,
        ':organization_id' => $organizationId
    ]);

    if (!$permissionQuery->fetchColumn()) {
        http_response_code(403);
        exit('You do not have permission to edit this organization.');
    }

    // Update organization information.
    $updateQuery = $pdo->prepare("
        UPDATE organization
        SET
            organization_name = :organization_name,
            university_name = :university_name,
            description = :description
        WHERE organization_id = :organization_id
          AND status <> 'deleted'
    ");

    $updateQuery->execute([
        ':organization_name' => $organizationName,
        ':university_name' => $universityName,
        ':description' => $description !== '' ? $description : null,
        ':organization_id' => $organizationId
    ]);

    redirectToOrganization($organizationId, $organizationName);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Unable to update the organization. Please try again.');
}