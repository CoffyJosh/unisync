
<?php
session_start();

require_once '../php/db.php';

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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Settings | UniSync</title>

    <script src="../javascript/tailwind.js"></script>
    <script src="../javascript/jquery.js"></script>
</head>

<body class="min-h-screen flex flex-col bg-slate-50">

    <?php include '../php/components/navbar.php'; ?>

    <main class="flex-1 w-full max-w-5xl mx-auto px-6 lg:px-8 py-10">

        <!-- Page Heading -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[#1E3A5F]">
                Account Settings
            </h1>

            <p class="text-slate-500 mt-2">
                Manage your account information and security.
            </p>
        </div>

        <!-- General Notification -->
        <div id="generalMessage"
             class="hidden mb-6 p-4 rounded-xl text-sm">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Settings Navigation -->
            <aside class="md:col-span-1">
                <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 px-3 mb-3">
                        Settings
                    </p>

                    <a href="#accountSection"
                       class="flex items-center gap-3 px-3 py-3 rounded-lg bg-blue-50 text-[#1E3A5F] font-semibold">

                        <i class="fa-solid fa-user w-5"></i>
                        Account Information

                    </a>

                    <a href="#passwordSection"
                       class="flex items-center gap-3 px-3 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-[#1E3A5F] transition-colors">

                        <i class="fa-solid fa-lock w-5"></i>
                        Change Password

                    </a>

                </div>
            </aside>

            <!-- Settings Content -->
            <div class="md:col-span-2 space-y-8">

                <!-- ACCOUNT INFORMATION -->
                <section id="accountSection"
                         class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">

                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-[#1E3A5F]">
                            Account Information
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">
                            Update your registered name and email address.
                        </p>
                    </div>

                    <form id="accountForm" class="space-y-5">

                        <div>
                            <label for="student_name"
                                   class="block text-sm font-medium text-slate-700 mb-2">
                                Student Name
                            </label>

                            <input
                                type="text"
                                id="student_name"
                                name="student_name"
                                value="<?php echo htmlspecialchars($student['student_name']); ?>"
                                maxlength="150"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                                required>

                            <div class="field-error text-red-500 text-sm mt-1"
                                 data-error="student_name"></div>
                        </div>

                        <div>
                            <label for="email"
                                   class="block text-sm font-medium text-slate-700 mb-2">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($student['email']); ?>"
                                maxlength="255"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                                required>

                            <div class="field-error text-red-500 text-sm mt-1"
                                 data-error="email"></div>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                id="accountButton"
                                class="px-6 py-3 rounded-lg bg-[#1E3A5F] text-white font-semibold hover:bg-[#294D78] transition-colors disabled:opacity-60">

                                Save Changes

                            </button>
                        </div>

                    </form>

                </section>

                <!-- CHANGE PASSWORD -->
                <section id="passwordSection"
                         class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">

                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-[#1E3A5F]">
                            Change Password
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">
                            Choose a strong password to keep your account secure.
                        </p>
                    </div>

                    <form id="passwordForm" class="space-y-5">

                        <div>
                            <label for="current_password"
                                   class="block text-sm font-medium text-slate-700 mb-2">
                                Current Password
                            </label>

                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                autocomplete="current-password"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                                required>

                            <div class="field-error text-red-500 text-sm mt-1"
                                 data-error="current_password"></div>
                        </div>

                        <div>
                            <label for="new_password"
                                   class="block text-sm font-medium text-slate-700 mb-2">
                                New Password
                            </label>

                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                autocomplete="new-password"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                                required>

                            <p class="text-xs text-slate-400 mt-2">
                                At least 8 characters, including uppercase, lowercase, and a number.
                            </p>

                            <div class="field-error text-red-500 text-sm mt-1"
                                 data-error="new_password"></div>
                        </div>

                        <div>
                            <label for="confirm_password"
                                   class="block text-sm font-medium text-slate-700 mb-2">
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                autocomplete="new-password"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                                required>

                            <div class="field-error text-red-500 text-sm mt-1"
                                 data-error="confirm_password"></div>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                id="passwordButton"
                                class="px-6 py-3 rounded-lg bg-[#1E3A5F] text-white font-semibold hover:bg-[#294D78] transition-colors disabled:opacity-60">

                                Update Password

                            </button>
                        </div>

                    </form>

                </section>

            </div>

        </div>

    </main>

    <?php include '../php/components/footer.php'; ?>

    <script src="../javascript/app/settings.js"></script>

</body>
</html>
