<?php

require_once __DIR__ . '/../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['account_id'])) {
    header('Location: account_login.php');
    exit;
}

$accountId = $_SESSION['account_id'];

$error = '';

$organizationName = '';
$universityName = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organizationName = trim($_POST['organization_name'] ?? '');
    $universityName = trim($_POST['university_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($organizationName === '') {
        $error = 'Organization name is required.';
    } elseif (mb_strlen($organizationName) > 150) {
        $error = 'Organization name must not exceed 150 characters.';
    } elseif ($universityName === '') {
        $error = 'University name is required.';
    } elseif (mb_strlen($universityName) > 150) {
        $error = 'University name must not exceed 150 characters.';
    } else {
        try {
            // Block duplicate organizations at the same university.
            $duplicateStmt = $pdo->prepare("
                SELECT 1
                FROM organization
                WHERE LOWER(organization_name) = LOWER(:organization_name)
                  AND LOWER(university_name) = LOWER(:university_name)
                LIMIT 1
            ");
            $duplicateStmt->execute([
                ':organization_name' => $organizationName,
                ':university_name' => $universityName
            ]);

            if ($duplicateStmt->fetchColumn()) {
                $error = 'An organization with this name already exists at that university.';
            } else {
                $pdo->beginTransaction();

                $organizationStmt = $pdo->prepare("
                    INSERT INTO organization (
                        organization_name,
                        university_name,
                        description
                    )
                    VALUES (
                        :organization_name,
                        :university_name,
                        :description
                    )
                    RETURNING organization_id
                ");
                $organizationStmt->execute([
                    ':organization_name' => $organizationName,
                    ':university_name' => $universityName,
                    ':description' => $description !== '' ? $description : null
                ]);

                $organizationId = $organizationStmt->fetchColumn();

                $membershipStmt = $pdo->prepare("
                    INSERT INTO membership (
                        account_id,
                        organization_id,
                        role
                    )
                    VALUES (
                        :account_id,
                        :organization_id,
                        'Admin'
                    )
                ");
                $membershipStmt->execute([
                    ':account_id' => $accountId,
                    ':organization_id' => $organizationId
                ]);

                $pdo->commit();

                $slug = trim(
                    strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $organizationName)),
                    '-'
                );

                header(
                    'Location: organization.php?id='
                    . urlencode((string) $organizationId)
                    . '&name='
                    . urlencode($slug)
                );
                exit;
            }

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());
            $error = 'Unable to create the organization. Please try again.';
        }
    }
}
