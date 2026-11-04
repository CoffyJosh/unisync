<?php
session_start();
require_once __DIR__ . "/../php/app/account/settings.php";
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
                            <label for="account_name"
                                   class="block text-sm font-medium text-slate-700 mb-2">
                                Account Name
                            </label>

                            <input
                                type="text"
                                id="account_name"
                                name="account_name"
                                value="<?php echo htmlspecialchars($account['account_name']); ?>"
                                maxlength="150"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                                required>

                            <div class="field-error text-red-500 text-sm mt-1"
                                 data-error="account_name"></div>
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
                                value="<?php echo htmlspecialchars($account['email']); ?>"
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