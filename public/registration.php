
<?php
session_start();

require_once '../php/db.php';

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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | UniSync</title>

    <!-- Tailwind CSS -->
    <script src="../javascript/tailwind.js"></script>

    <!-- jQuery -->
    <script src="../javascript/jquery.js"></script>
</head>

<body class="min-h-screen flex flex-col bg-slate-50">

    <!-- Navbar -->
    <?php include '../php/components/navbar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center px-4 py-12">

        <div class="w-full max-w-md">

            <!-- Registration Card -->
            <div id="registrationCard"
                class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">

                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-[#1E3A5F]">
                        Create an Account
                    </h1>

                    <p class="text-slate-500 mt-2">
                        Join your university community with UniSync.
                    </p>
                </div>

                <!-- General Error -->
                <div id="generalError"
                    class="hidden mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm">
                </div>

                <!-- Registration Form -->
                <form id="registrationForm" class="space-y-5">

                    <!-- Full Name -->
                    <div>
                        <label for="student_name"
                            class="block text-sm font-medium text-slate-700 mb-2">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="student_name"
                            name="student_name"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-[#1E3A5F] focus:border-[#1E3A5F]"
                            required>

                        <div class="field-error text-red-500 text-sm mt-1"
                            data-error="student_name"></div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email"
                            class="block text-sm font-medium text-slate-700 mb-2">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your university email"
                            autocomplete="email"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-[#1E3A5F] focus:border-[#1E3A5F]"
                            required>

                        <div class="field-error text-red-500 text-sm mt-1"
                            data-error="email"></div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password"
                            class="block text-sm font-medium text-slate-700 mb-2">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-[#1E3A5F] focus:border-[#1E3A5F]"
                            required>

                        <div class="field-error text-red-500 text-sm mt-1"
                            data-error="password"></div>

                        <p class="text-xs text-slate-400 mt-2">
                            Minimum 8 characters, with uppercase, lowercase,
                            and a number.
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password"
                            class="block text-sm font-medium text-slate-700 mb-2">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-[#1E3A5F] focus:border-[#1E3A5F]"
                            required>

                        <div class="field-error text-red-500 text-sm mt-1"
                            data-error="confirm_password"></div>
                    </div>

                    <!-- Create Account Button -->
                    <button
                        type="submit"
                        id="registerButton"
                        class="w-full bg-[#1E3A5F] hover:bg-[#294d78] text-white font-semibold py-3 rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed">

                        Create Account
                    </button>

                </form>

                <!-- Login Link -->
                <p class="text-center text-sm text-slate-500 mt-6">
                    Already have an account?
                    <a href="login.php"
                        class="text-[#1E3A5F] font-semibold hover:underline">
                        Log in
                    </a>
                </p>

            </div>

            <!-- Loading Screen -->
            <div id="registrationLoading"
                class="hidden bg-white border border-slate-200 rounded-2xl shadow-sm p-10 text-center">

                <div class="w-12 h-12 border-4 border-slate-200 border-t-[#1E3A5F] rounded-full animate-spin mx-auto mb-5"></div>

                <h2 class="text-xl font-bold text-[#1E3A5F]">
                    Creating Your Account
                </h2>

                <p class="text-slate-500 mt-2">
                    Please wait while we set everything up.
                </p>

            </div>

            <!-- Success Screen -->
            <div id="registrationSuccess"
                class="hidden bg-white border border-slate-200 rounded-2xl shadow-sm p-10 text-center">

                <div class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-9 h-9"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-[#1E3A5F]">
                    Account Created!
                </h2>

                <p class="text-slate-500 mt-2">
                    Your UniSync account has been created successfully.
                </p>

                <p class="text-sm text-slate-400 mt-5">
                    Redirecting you to your dashboard...
                </p>

            </div>

        </div>

    </main>

    <!-- Footer -->
    <?php include '../php/components/footer.php'; ?>

    <!-- Registration JavaScript -->
    <script src="../javascript/app/registration.js"></script>

</body>
</html>
