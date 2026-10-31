
<?php
session_start();

require_once '../php/db.php';

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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | UniSync</title>

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

            <!-- Login Card -->
            <div id="loginCard"
                class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">

                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-[#1E3A5F]">
                        Welcome Back
                    </h1>

                    <p class="text-slate-500 mt-2">
                        Log in to your UniSync account.
                    </p>
                </div>

                <!-- General Error -->
                <div id="generalError"
                    class="hidden mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm">
                </div>

                <!-- Login Form -->
                <form id="loginForm" class="space-y-5">

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
                            placeholder="Enter your email address"
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
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-[#1E3A5F] focus:border-[#1E3A5F]"
                            required>

                        <div class="field-error text-red-500 text-sm mt-1"
                            data-error="password"></div>
                    </div>

                    <!-- Login Button -->
                    <button
                        type="submit"
                        id="loginButton"
                        class="w-full bg-[#1E3A5F] hover:bg-[#294d78] text-white font-semibold py-3 rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed">

                        Log In
                    </button>

                </form>

                <!-- Registration Link -->
                <p class="text-center text-sm text-slate-500 mt-6">
                    Don't have an account?
                    <a href="registration.php"
                        class="text-[#1E3A5F] font-semibold hover:underline">
                        Create an account
                    </a>
                </p>

            </div>

            <!-- Loading Screen -->
            <div id="loginLoading"
                class="hidden bg-white border border-slate-200 rounded-2xl shadow-sm p-10 text-center">

                <div class="w-12 h-12 border-4 border-slate-200 border-t-[#1E3A5F] rounded-full animate-spin mx-auto mb-5"></div>

                <h2 class="text-xl font-bold text-[#1E3A5F]">
                    Logging In
                </h2>

                <p class="text-slate-500 mt-2">
                    Please wait...
                </p>

            </div>

            <!-- Success Screen -->
            <div id="loginSuccess"
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
                    Login Successful!
                </h2>

                <p class="text-slate-500 mt-2">
                    Welcome back to UniSync.
                </p>

                <p class="text-sm text-slate-400 mt-5">
                    Redirecting to your dashboard...
                </p>

            </div>

        </div>

    </main>

    <!-- Footer -->
    <?php include '../php/components/footer.php'; ?>

    <!-- Login JavaScript -->
    <script src="../javascript/app/login.js"></script>

</body>
</html>
