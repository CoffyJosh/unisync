
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['student_id']);

$currentPage = basename($_SERVER['PHP_SELF']);

$showSearch = !in_array($currentPage, [
    'login.php',
    'registration.php'
]);

$authButtonText = ($currentPage === 'login.php') ? 'Register' : 'Login';
$authButtonLink = ($currentPage === 'login.php') ? 'registration.php' : 'login.php';
?>

<nav class="sticky top-0 z-50 w-full bg-white border-b border-slate-200 shadow-sm">

    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        <div class="flex items-center justify-between h-20 gap-8">

            <!-- UniSync Branding -->
            <a href="index.php"
               class="flex items-center gap-2.5 shrink-0 group">

                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] flex items-center justify-center group-hover:bg-[#294D78] transition-colors">
                    <i class="fa-solid fa-calendar-days text-white text-lg"></i>
                </div>

                <span class="text-2xl font-bold tracking-tight text-[#1E3A5F]">
                    UniSync
                </span>

            </a>

            <!-- Desktop Search -->
            <?php if ($showSearch): ?>

                <form action="browse.php"
                      method="GET"
                      class="flex-1 max-w-md hidden md:flex">

                    <div class="relative w-full">

                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                        <input
                            type="text"
                            name="search"
                            placeholder="Search events or organizations..."
                            aria-label="Search events or organizations"
                            class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:bg-white focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100 transition-all"
                        >

                    </div>

                </form>

            <?php endif; ?>

            <!-- Navigation Links -->
            <div class="flex items-center gap-7 text-sm font-semibold text-slate-600">

                <a href="index.php"
                   class="hover:text-[#1E3A5F] transition-colors">
                    Home
                </a>

                <a href="browse.php"
                   class="hover:text-[#1E3A5F] transition-colors">
                    Browse
                </a>

                <?php if ($isLoggedIn): ?>

                    <!-- Account Dropdown -->
                    <details class="relative group">

                        <summary class="flex items-center gap-2 cursor-pointer list-none text-[#1E3A5F] hover:text-blue-700 transition-colors">
                            Account
                            <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>

                        </summary>

                        <div class="absolute right-0 top-full mt-4 w-48 rounded-xl border border-slate-200 bg-white shadow-xl p-2 flex flex-col gap-1">

                            <a href="dashboard.php"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-600 hover:text-[#1E3A5F] hover:bg-slate-50 transition-colors">

                                <i class="fa-solid fa-gauge w-4"></i>
                                Dashboard

                            </a>

                            <a href="settings.php"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-600 hover:text-[#1E3A5F] hover:bg-slate-50 transition-colors">

                                <i class="fa-solid fa-gear w-4"></i>
                                Settings

                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <a href="../php/app/auth/logout.php"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-500 hover:bg-red-50 transition-colors">

                                <i class="fa-solid fa-right-from-bracket w-4"></i>
                                Logout

                            </a>

                        </div>

                    </details>

                <?php else: ?>

                    <!-- Single Authentication Button -->
                    <a href="<?php echo htmlspecialchars($authButtonLink); ?>"
                       class="px-6 py-2.5 rounded-lg bg-[#1E3A5F] text-white hover:bg-[#294D78] shadow-sm transition-colors">

                        <?php echo htmlspecialchars($authButtonText); ?>

                    </a>

                <?php endif; ?>

            </div>

        </div>

        <!-- Mobile Search -->
        <?php if ($showSearch): ?>

            <form action="browse.php"
                  method="GET"
                  class="md:hidden pb-4">

                <div class="relative">

                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                    <input
                        type="text"
                        name="search"
                        placeholder="Search events or organizations..."
                        aria-label="Search events or organizations"
                        class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                    >

                </div>

            </form>

        <?php endif; ?>

    </div>

</nav>
