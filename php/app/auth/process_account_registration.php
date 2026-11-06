<?php
require_once __DIR__ . '/../../db.php';

// Determine which portal the registration came from.
$isProviderRegistration = ($_GET['redirect'] ?? '') === 'provider';

$redirectPage = $isProviderRegistration
    ? 'provider_dashboard.php'
    : 'home.php';

// Redirect already logged-in accounts to the appropriate dashboard.
if (isset($_SESSION['account_id'])) {
    header('Location: ' . $redirectPage);
    exit;
}

// Handle AJAX registration.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $account_name = trim($_POST['account_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Name validation.
    if ($account_name === '') {
        $errors['account_name'][] = 'Full name is required.';
    } elseif (mb_strlen($account_name) > 150) {
        $errors['account_name'][] = 'Full name cannot exceed 150 characters.';
    }

    // Email validation.
    if ($email === '') {
        $errors['email'][] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'][] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 255) {
        $errors['email'][] = 'Email address cannot exceed 255 characters.';
    }

    // Password validation.
    if (strlen($password) < 8) {
        $errors['password'][] = 'At least 8 characters are required.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors['password'][] = 'At least one uppercase letter is required.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors['password'][] = 'At least one lowercase letter is required.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors['password'][] = 'At least one number is required.';
    }

    // Confirm password validation.
    if ($confirm_password === '') {
        $errors['confirm_password'][] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'][] = 'Passwords do not match.';
    }

    // Return all validation errors.
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit;
    }

    try {
        // Check whether the email is already registered.
        $checkEmail = $pdo->prepare(
            'SELECT account_id FROM account WHERE email = :email LIMIT 1'
        );

        $checkEmail->execute([':email' => $email]);

        if ($checkEmail->fetch()) {
            echo json_encode([
                'success' => false,
                'errors' => [
                    'email' => ['This email address is already registered.']
                ]
            ]);
            exit;
        }

        // Hash the password before storing it.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new account.
        $insertAccount = $pdo->prepare(
            'INSERT INTO account (account_name, email, password_hash)
             VALUES (:account_name, :email, :password_hash)
             RETURNING account_id'
        );

        $insertAccount->execute([
            ':account_name' => $account_name,
            ':email' => $email,
            ':password_hash' => $password_hash
        ]);

        $account_id = $insertAccount->fetchColumn();

        // Automatically log in the newly registered account.
        session_regenerate_id(true);

        $_SESSION['account_id'] = $account_id;
        $_SESSION['account_name'] = $account_name;
        $_SESSION['email'] = $email;

        echo json_encode([
            'success' => true,
            'message' => 'Your account has been created successfully!',
            'redirect' => $redirectPage
        ]);
        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());

        // PostgreSQL unique constraint violation.
        if ($e->getCode() === '23505') {
            echo json_encode([
                'success' => false,
                'errors' => [
                    'email' => ['This email address is already registered.']
                ]
            ]);
        } else {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'errors' => [
                    'general' => ['Something went wrong. Please try again.']
                ]
            ]);
        }

        exit;
    }
}
?>