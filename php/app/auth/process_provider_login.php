
<?php

require_once __DIR__ . '/../../db.php';

// Redirect accounts that are already logged in.
if (isset($_SESSION['account_id'])) {
    header('Location: provider_dashboard.php');
    exit;
}

// Handle AJAX login request.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    if ($email === '') {
        $errors['email'][] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'][] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'][] = 'Password is required.';
    }

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit;
    }

    try {
        // Find the account using its email.
        $query = $pdo->prepare(
            'SELECT account_id, account_name, email, password_hash, account_status
             FROM account
             WHERE email = :email
             LIMIT 1'
        );

        $query->execute([
            ':email' => $email
        ]);

        $account = $query->fetch(PDO::FETCH_ASSOC);

        // Verify the password.
        if (
            !$account ||
            !password_verify($password, $account['password_hash'])
        ) {
            echo json_encode([
                'success' => false,
                'errors' => [
                    'general' => ['Incorrect email or password.']
                ]
            ]);
            exit;
        }

        // Prevent disabled or deleted accounts from logging in.
        if ($account['account_status'] !== 'active') {
            echo json_encode([
                'success' => false,
                'errors' => [
                    'general' => ['This account is currently unavailable.']
                ]
            ]);
            exit;
        }

        // Regenerate session ID after successful authentication.
        session_regenerate_id(true);

        // Store account information in the session.
        $_SESSION['account_id'] = $account['account_id'];
        $_SESSION['account_name'] = $account['account_name'];
        $_SESSION['email'] = $account['email'];

        echo json_encode([
            'success' => true,
            'message' => 'Login successful!'
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
