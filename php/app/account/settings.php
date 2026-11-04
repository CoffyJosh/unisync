
<?php
require_once __DIR__ . '/../../db.php';

// Only logged-in accounts can access settings.
if (!isset($_SESSION['account_id'])) {
    header('Location: account_login.php');
    exit;
}

$accountId = $_SESSION['account_id'];

// Get the account's latest information.
$query = $pdo->prepare(
    'SELECT account_name, email
     FROM account
     WHERE account_id = :account_id
       AND account_status = :account_status'
);

$query->execute([
    ':account_id' => $accountId,
    ':account_status' => 'active'
]);

$account = $query->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    $_SESSION = [];
    session_destroy();

    header('Location: account_login.php');
    exit;
}

// Handle AJAX requests.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    try {

        // UPDATE ACCOUNT INFORMATION
        if ($action === 'update_account') {

            $accountName = trim($_POST['account_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $errors = [];

            if ($accountName === '') {
                $errors['account_name'][] = 'Account name is required.';
            } elseif (strlen($accountName) > 150) {
                $errors['account_name'][] = 'Account name must not exceed 150 characters.';
            }

            if ($email === '') {
                $errors['email'][] = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'][] = 'Please enter a valid email address.';
            } elseif (strlen($email) > 255) {
                $errors['email'][] = 'Email address must not exceed 255 characters.';
            }

            // Check whether another account already uses this email.
            if (empty($errors)) {
                $checkEmail = $pdo->prepare(
                    'SELECT account_id
                     FROM account
                     WHERE email = :email
                       AND account_id != :account_id'
                );

                $checkEmail->execute([
                    ':email' => $email,
                    ':account_id' => $accountId
                ]);

                if ($checkEmail->fetch()) {
                    $errors['email'][] = 'This email address is already registered.';
                }
            }

            if (!empty($errors)) {
                echo json_encode([
                    'success' => false,
                    'errors' => $errors
                ]);
                exit;
            }

            $update = $pdo->prepare(
                'UPDATE account
                 SET account_name = :account_name,
                     email = :email
                 WHERE account_id = :account_id'
            );

            $update->execute([
                ':account_name' => $accountName,
                ':email' => $email,
                ':account_id' => $accountId
            ]);

            // Update the session with the new account information.
            $_SESSION['account_name'] = $accountName;
            $_SESSION['email'] = $email;

            echo json_encode([
                'success' => true,
                'message' => 'Your account information has been updated.'
            ]);
            exit;
        }

        // CHANGE PASSWORD
        if ($action === 'change_password') {

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = [];

            if ($currentPassword === '') {
                $errors['current_password'][] = 'Current password is required.';
            }

            if ($newPassword === '') {
                $errors['new_password'][] = 'New password is required.';
            } else {
                if (strlen($newPassword) < 8) {
                    $errors['new_password'][] = 'Password must be at least 8 characters long.';
                }

                if (!preg_match('/[A-Z]/', $newPassword)) {
                    $errors['new_password'][] = 'Password must contain at least one uppercase letter.';
                }

                if (!preg_match('/[a-z]/', $newPassword)) {
                    $errors['new_password'][] = 'Password must contain at least one lowercase letter.';
                }

                if (!preg_match('/[0-9]/', $newPassword)) {
                    $errors['new_password'][] = 'Password must contain at least one number.';
                }
            }

            if ($confirmPassword === '') {
                $errors['confirm_password'][] = 'Please confirm your new password.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors['confirm_password'][] = 'Passwords do not match.';
            }

            // Get the current password hash.
            $passwordQuery = $pdo->prepare(
                'SELECT password_hash
                 FROM account
                 WHERE account_id = :account_id
                   AND account_status = :account_status'
            );

            $passwordQuery->execute([
                ':account_id' => $accountId,
                ':account_status' => 'active'
            ]);

            $accountPassword = $passwordQuery->fetch(PDO::FETCH_ASSOC);

            if (
                !$accountPassword ||
                !password_verify($currentPassword, $accountPassword['password_hash'])
            ) {
                $errors['current_password'][] = 'Your current password is incorrect.';
            }

            if (!empty($errors)) {
                echo json_encode([
                    'success' => false,
                    'errors' => $errors
                ]);
                exit;
            }

            // Hash and save the new password.
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $updatePassword = $pdo->prepare(
                'UPDATE account
                 SET password_hash = :password_hash
                 WHERE account_id = :account_id'
            );

            $updatePassword->execute([
                ':password_hash' => $newPasswordHash,
                ':account_id' => $accountId
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Your password has been changed successfully.'
            ]);
            exit;
        }

        // Invalid action.
        echo json_encode([
            'success' => false,
            'errors' => [
                'general' => ['Invalid request.']
            ]
        ]);
        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'errors' => [
                'general' => ['Something went wrong. Please try again.']
            ]
        ]);
        exit;
    }
}
?>
