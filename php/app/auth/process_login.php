
<?php
require_once __DIR__ . '/../../db.php';

// Redirect students who are already logged in.
if (isset($_SESSION['student_id'])) {
    header('Location: index.php');
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
        // Find the student using their email.
        $query = $pdo->prepare(
            'SELECT student_id, student_name, email, password_hash
             FROM student
             WHERE email = :email
             LIMIT 1'
        );

        $query->execute([
            ':email' => $email
        ]);

        $student = $query->fetch(PDO::FETCH_ASSOC);

        // Verify the password.
        if (!$student || !password_verify($password, $student['password_hash'])) {
            echo json_encode([
                'success' => false,
                'errors' => [
                    'general' => ['Incorrect email or password.']
                ]
            ]);
            exit;
        }

        // Regenerate session ID after successful authentication.
        session_regenerate_id(true);

        // Store student information in the session.
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['student_name'];
        $_SESSION['email'] = $student['email'];

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