<?php
require_once __DIR__ . '/../../db.php';

// Redirect logged-in students to the dashboard.
if (isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit;
}

// Handle AJAX registration.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $student_name = trim($_POST['student_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Name validation.
    if ($student_name === '') {
        $errors['student_name'][] = 'Full name is required.';
    } elseif (mb_strlen($student_name) > 150) {
        $errors['student_name'][] = 'Full name cannot exceed 150 characters.';
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
            'SELECT student_id FROM student WHERE email = :email LIMIT 1'
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

        // Insert the new student.
        $insertStudent = $pdo->prepare(
            'INSERT INTO student (student_name, email, password_hash)
             VALUES (:student_name, :email, :password_hash)
             RETURNING student_id'
        );

        $insertStudent->execute([
            ':student_name' => $student_name,
            ':email' => $email,
            ':password_hash' => $password_hash
        ]);

        $student_id = $insertStudent->fetchColumn();

        // Automatically log in the newly registered student.
        session_regenerate_id(true);

        $_SESSION['student_id'] = $student_id;
        $_SESSION['student_name'] = $student_name;
        $_SESSION['email'] = $email;

        echo json_encode([
            'success' => true,
            'message' => 'Your account has been created successfully!'
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