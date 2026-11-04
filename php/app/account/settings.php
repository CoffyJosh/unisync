<?php
require_once __DIR__ . '/../../db.php';

// Only logged-in students can access settings.
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$studentId = $_SESSION['student_id'];

// Get the student's latest account information.
$query = $pdo->prepare(
    'SELECT student_name, email
     FROM student
     WHERE student_id = :student_id'
);

$query->execute([':student_id' => $studentId]);
$student = $query->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    $_SESSION = [];
    session_destroy();

    header('Location: login.php');
    exit;
}

// Handle AJAX requests.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    try {

        // UPDATE ACCOUNT INFORMATION
        if ($action === 'update_account') {

            $studentName = trim($_POST['student_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $errors = [];

            if ($studentName === '') {
                $errors['student_name'][] = 'Student name is required.';
            } elseif (strlen($studentName) > 150) {
                $errors['student_name'][] = 'Student name must not exceed 150 characters.';
            }

            if ($email === '') {
                $errors['email'][] = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'][] = 'Please enter a valid email address.';
            } elseif (strlen($email) > 255) {
                $errors['email'][] = 'Email address must not exceed 255 characters.';
            }

            // Check whether another student already uses this email.
            if (empty($errors)) {
                $checkEmail = $pdo->prepare(
                    'SELECT student_id
                     FROM student
                     WHERE email = :email
                     AND student_id != :student_id'
                );

                $checkEmail->execute([
                    ':email' => $email,
                    ':student_id' => $studentId
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
                'UPDATE student
                 SET student_name = :student_name,
                     email = :email
                 WHERE student_id = :student_id'
            );

            $update->execute([
                ':student_name' => $studentName,
                ':email' => $email,
                ':student_id' => $studentId
            ]);

            // Update the session with the new account information.
            $_SESSION['student_name'] = $studentName;
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
                 FROM student
                 WHERE student_id = :student_id'
            );

            $passwordQuery->execute([
                ':student_id' => $studentId
            ]);

            $account = $passwordQuery->fetch(PDO::FETCH_ASSOC);

            if (!$account || !password_verify($currentPassword, $account['password_hash'])) {
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
                'UPDATE student
                 SET password_hash = :password_hash
                 WHERE student_id = :student_id'
            );

            $updatePassword->execute([
                ':password_hash' => $newPasswordHash,
                ':student_id' => $studentId
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