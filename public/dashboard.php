<?php
session_start();

$accountId = $_SESSION['account_id'] ?? null;

if (!$accountId) {
    header('Location: account_login.php');
    exit;
}

require_once __DIR__ . '/../php/db.php';

// Get the current account's information.
$stmt = $pdo->prepare("
    SELECT account_name, email
    FROM account
    WHERE account_id = :account_id
");

$stmt->execute(['account_id' => $accountId]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    session_destroy();
    header('Location: account_login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Dashboard | UniSync</title>

    <script src="../javascript/tailwind.js"></script>
    <script src="../javascript/jquery.js"></script>
</head>

<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">

    <?php include __DIR__ . '/../php/components/navbar.php'; ?>

    <main class="flex-1 w-full max-w-7xl mx-auto px-6 py-10">

        <!-- Dashboard Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-[#1E3A5F]">
                My Dashboard
            </h1>

            <p class="text-slate-500 mt-2">
                Keep track of your registered events and organizations.
            </p>
        </div>

        <!-- Student Information -->
        <section class="bg-white border border-slate-200 rounded-xl px-5 py-4 mb-8">

            <div>
                <h2 class="text-lg font-bold text-[#1E3A5F]">
                    <?= htmlspecialchars($account['account_name']) ?>
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    <?= htmlspecialchars($account['email']) ?>
                </p>

                <p class="text-xs text-slate-400 mt-2">
                    Student
                </p>
            </div>

        </section>

        <!-- Dashboard Content -->
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">

            <!-- Tabs -->
            <div class="grid grid-cols-2 w-full border-b border-slate-200">

                <button
                    type="button"
                    class="dashboard-tab w-full flex items-center justify-center gap-2 px-4 py-4 font-semibold text-sm text-[#1E3A5F] border-b-2 border-[#1E3A5F] transition"
                    data-tab="events">

                    Events

                    <span
                        id="registeredCount"
                        class="text-xs font-bold bg-blue-100 text-[#1E3A5F] px-2 py-0.5 rounded-full">
                        0
                    </span>

                </button>

                <button
                    type="button"
                    class="dashboard-tab w-full flex items-center justify-center gap-2 px-4 py-4 font-semibold text-sm text-slate-500 border-b-2 border-transparent hover:text-[#1E3A5F] transition"
                    data-tab="organizations">

                    Organizations

                    <span
                        id="organizationCount"
                        class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">
                        0
                    </span>

                </button>

            </div>

            <div class="p-6">

                <!-- Events Tab -->
                <div id="eventsTab">

                    <div class="flex flex-col sm:flex-row gap-3 justify-between mb-6">

                        <div>
                            <h2 class="text-xl font-bold text-[#1E3A5F]">
                                Events
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Events you've signed up for.
                            </p>
                        </div>

                        <input
                            type="search"
                            id="eventOrganizationSearch"
                            placeholder="Search organization..."
                            class="w-full sm:w-72 border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-900">

                    </div>

                    <div id="eventResults">
                        <p class="text-center text-slate-500 py-10">
                            Loading events...
                        </p>
                    </div>

                    <div id="eventPagination" class="mt-6"></div>

                </div>

                <!-- Organizations Tab -->
                <div id="organizationsTab" class="hidden">

                    <div class="flex flex-col sm:flex-row gap-3 justify-between mb-6">

                        <div>
                            <h2 class="text-xl font-bold text-[#1E3A5F]">
                                Organizations
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                Organizations you've joined.
                            </p>
                        </div>

                        <!-- Search + Create Organization -->
                        <div class="flex flex-col sm:flex-row sm:items-stretch gap-3">

                            <input
                                type="search"
                                id="organizationSearch"
                                placeholder="Search organization..."
                                class="w-full sm:w-64 border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-900">

                            <a
                                href="create_organization.php"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-[#1E3A5F] px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#152c49] focus:outline-none focus:ring-2 focus:ring-[#1E3A5F] focus:ring-offset-2">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 5v14M5 12h14" />
                                </svg>

                                Create Organization
                            </a>

                        </div>

                    </div>

                    <div id="organizationResults">
                        <p class="text-center text-slate-500 py-10">
                            Loading organizations...
                        </p>
                    </div>

                    <div id="organizationPagination" class="mt-6"></div>

                </div>

                <p id="dashboardMessage" class="hidden text-sm text-center mt-5"></p>

            </div>

        </section>

    </main>

    <?php include __DIR__ . '/../php/components/footer.php'; ?>

    <script src="../javascript/app/dashboard.js"></script>

    <script>
        $(document).ready(function () {

            $('.dashboard-tab').on('click', function () {

                const selectedTab = $(this).data('tab');

                // Switch content panels.
                $('#eventsTab, #organizationsTab').addClass('hidden');

                if (selectedTab === 'events') {
                    $('#eventsTab').removeClass('hidden');
                } else {
                    $('#organizationsTab').removeClass('hidden');
                }

                // Reset tab styles.
                $('.dashboard-tab')
                    .removeClass('text-[#1E3A5F] border-[#1E3A5F]')
                    .addClass('text-slate-500 border-transparent');

                // Highlight selected tab.
                $(this)
                    .removeClass('text-slate-500 border-transparent')
                    .addClass('text-[#1E3A5F] border-[#1E3A5F]');

            });

        });
    </script>

</body>
</html>
