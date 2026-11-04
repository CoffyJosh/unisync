
<?php

require_once __DIR__ . '/../php/app/organization/get_organization.php';

function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$organizationName = $organization['organization_name'];
$organizationStatus = strtolower($organization['status']);

$statusClass = $organizationStatus === 'active'
    ? 'bg-emerald-100 text-emerald-700'
    : 'bg-amber-100 text-amber-700';

$initials = '';
$words = preg_split('/\s+/', trim($organizationName));

foreach ($words as $word) {
    if ($word !== '') {
        $initials .= strtoupper(substr($word, 0, 1));
    }
}

$initials = substr($initials, 0, 3);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($organizationName) ?> | UniSync</title>

    <script src="../javascript/tailwind.js"></script>
    <script src="../javascript/jquery.js"></script>
</head>

<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">

    <?php include __DIR__ . '/../php/components/navbar.php'; ?>

    <main class="flex-1 w-full max-w-6xl mx-auto px-5 sm:px-8 py-8">

        <!-- Back Navigation -->
        <a href="browse.php"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-900 mb-6">
            <span>&larr;</span>
            Back to Organizations
        </a>

        <!-- Organization Hero -->
        <section class="relative overflow-hidden bg-[#142C4C] rounded-3xl p-7 sm:p-10 text-white shadow-lg">

            <div class="absolute -right-16 -top-24 w-80 h-80 rounded-full border border-white/10"></div>
            <div class="absolute -right-4 -top-12 w-60 h-60 rounded-full border border-white/10"></div>

            <div class="relative flex flex-col sm:flex-row sm:items-center gap-6">

                <!-- Organization Initials -->
                <div class="w-24 h-24 shrink-0 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
                    <span class="text-3xl font-extrabold tracking-wider">
                        <?= e($initials) ?>
                    </span>
                </div>

                <!-- Organization Identity -->
                <div class="flex-1 min-w-0">

                    <div class="flex flex-wrap items-center gap-3 mb-3">

                        <span class="text-xs font-bold uppercase tracking-widest text-blue-200">
                            University Organization
                        </span>

                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $statusClass ?>">
                            <?= e($organization['status']) ?>
                        </span>

                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                        <?= e($organizationName) ?>
                    </h1>

                    <p class="text-sm text-blue-100/70 mt-2">
                        Discover the organization, its members, and its activities.
                    </p>

                </div>

                <!-- Management Action -->
                <?php if ($canManageEvents): ?>
                    <div class="relative">
                        <a href="create_event.php?organization_id=<?= (int) $organizationId ?>"
                           class="inline-flex items-center justify-center gap-2 bg-white text-[#142C4C] hover:bg-blue-50 font-bold px-5 py-3 rounded-xl transition">
                            <span>+</span>
                            Create Event
                        </a>
                    </div>
                <?php endif; ?>

            </div>

        </section>

        <!-- Statistics -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Total Members
                </p>
                <p class="text-3xl font-extrabold text-[#1E3A5F] mt-2">
                    <?= count($members) ?>
                </p>
                <p class="text-xs text-slate-400 mt-1">
                    Registered organization members
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Total Events
                </p>
                <p class="text-3xl font-extrabold text-[#1E3A5F] mt-2">
                    <?= count($events) ?>
                </p>
                <p class="text-xs text-slate-400 mt-1">
                    Events created by this organization
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Your Membership
                </p>

                <p class="text-2xl font-extrabold mt-2 <?= $isMember ? 'text-emerald-600' : 'text-slate-500' ?>">
                    <?= $isMember ? 'Member' : 'Visitor' ?>
                </p>

                <p class="text-xs text-slate-400 mt-1">
                    <?php if ($isMember): ?>
                        <?= e($membership['role']) ?>
                    <?php elseif ($studentId === null): ?>
                        Log in to check your membership
                    <?php else: ?>
                        You are not a member of this organization
                    <?php endif; ?>
                </p>
            </div>

        </section>

        <!-- Organization Navigation -->
        <section class="mt-9">

            <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto">

                <button type="button"
                        class="org-tab shrink-0 px-5 py-4 text-sm font-bold border-b-2 border-[#1E3A5F] text-[#1E3A5F]"
                        data-tab="about">
                    About
                </button>

                <button type="button"
                        class="org-tab shrink-0 px-5 py-4 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-[#1E3A5F]"
                        data-tab="events">
                    Events
                    <span class="ml-1 text-xs text-slate-400">(<?= count($events) ?>)</span>
                </button>

                <button type="button"
                        class="org-tab shrink-0 px-5 py-4 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-[#1E3A5F]"
                        data-tab="members">
                    Members
                    <span class="ml-1 text-xs text-slate-400">(<?= count($members) ?>)</span>
                </button>

            </div>

            <!-- ABOUT TAB -->
            <div id="tab-about" class="org-panel py-8">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- About Description -->
                    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-7">

                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center font-bold">
                                i
                            </div>

                            <div>
                                <h2 class="text-xl font-bold text-[#1E3A5F]">
                                    About Us
                                </h2>
                                <p class="text-xs text-slate-400">
                                    Get to know this organization
                                </p>
                            </div>
                        </div>

                        <div class="text-sm leading-7 text-slate-600 whitespace-pre-line">
                            <?php if (!empty($organization['description'])): ?>
                                <?= e($organization['description']) ?>
                            <?php else: ?>
                                <p class="text-slate-400 italic">
                                    This organization hasn't added its introduction yet.
                                </p>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- Organization Information -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-7">

                        <h2 class="text-lg font-bold text-[#1E3A5F] mb-5">
                            Organization Information
                        </h2>

                        <div class="space-y-5">

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Organization Name
                                </p>
                                <p class="text-sm font-semibold text-slate-700">
                                    <?= e($organizationName) ?>
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Organization ID
                                </p>
                                <p class="text-sm font-semibold text-slate-700">
                                    #<?= (int) $organizationId ?>
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Status
                                </p>
                                <p class="text-sm font-semibold text-slate-700">
                                    <?= e($organization['status']) ?>
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Total Members
                                </p>
                                <p class="text-sm font-semibold text-slate-700">
                                    <?= count($members) ?> students
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- EVENTS TAB -->
            <div id="tab-events" class="org-panel hidden py-8">

                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">

                    <div>
                        <h2 class="text-2xl font-bold text-[#1E3A5F]">
                            Organization Events
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">
                            Browse the events organized by <?= e($organizationName) ?>.
                        </p>
                    </div>

                    <p class="text-sm font-semibold text-slate-400">
                        <?= count($events) ?> total events
                    </p>

                </div>

                <?php if (!empty($events)): ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <?php foreach ($events as $event): ?>

                            <?php
                            $eventStatus = strtolower($event['status']);

                            $eventStatusClass = [
                                'confirmed' => 'bg-emerald-100 text-emerald-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'completed' => 'bg-slate-100 text-slate-600'
                            ][$eventStatus] ?? 'bg-slate-100 text-slate-600';
                            ?>

                            <article class="bg-white border border-slate-200 rounded-2xl p-6 hover:shadow-md transition">

                                <div class="flex items-start justify-between gap-3 mb-4">

                                    <h3 class="text-lg font-bold text-slate-900">
                                        <?= e($event['event_name']) ?>
                                    </h3>

                                    <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold uppercase <?= $eventStatusClass ?>">
                                        <?= e($event['status']) ?>
                                    </span>

                                </div>

                                <p class="text-sm text-slate-500 leading-6 mb-5">
                                    <?= e($event['description'] ?: 'No event description available.') ?>
                                </p>

                                <div class="border-t border-slate-100 pt-4 mb-5">

                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                        Schedule
                                    </p>

                                    <p class="text-sm text-slate-600 whitespace-pre-line leading-6">
                                        <?= e($event['schedules'] ?: 'No schedule assigned yet.') ?>
                                    </p>

                                </div>

                                <a href="event.php?id=<?= (int) $event['event_id'] ?>"
                                   class="inline-flex items-center gap-2 text-sm font-bold text-[#1E3A5F] hover:text-blue-700">
                                    View Event Details
                                    <span>&rarr;</span>
                                </a>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center">

                        <div class="text-4xl mb-4">📅</div>

                        <h3 class="text-lg font-bold text-slate-800">
                            No Events Yet
                        </h3>

                        <p class="text-sm text-slate-500 mt-2">
                            This organization hasn't scheduled any events.
                        </p>

                        <?php if ($canManageEvents): ?>
                            <a href="create_event.php?organization_id=<?= (int) $organizationId ?>"
                               class="inline-block mt-5 bg-[#1E3A5F] hover:bg-[#294D78] text-white font-semibold px-5 py-3 rounded-xl">
                                Create First Event
                            </a>
                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

            <!-- MEMBERS TAB -->
            <div id="tab-members" class="org-panel hidden py-8">

                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-[#1E3A5F]">
                        Organization Members
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Meet the students who are part of this organization.
                    </p>
                </div>

                <?php if (!empty($members)): ?>

                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-0">

                            <?php foreach ($members as $member): ?>

                                <div class="flex items-center gap-4 p-5 border-b border-slate-100">

                                    <div class="w-12 h-12 shrink-0 rounded-full bg-blue-50 text-[#1E3A5F] flex items-center justify-center font-bold">
                                        <?= e(strtoupper(substr($member['student_name'], 0, 1))) ?>
                                    </div>

                                    <div class="flex-1 min-w-0">

                                        <p class="font-bold text-slate-800 truncate">
                                            <?= e($member['student_name']) ?>

                                            <?php if (
                                                $studentId !== null &&
                                                (string) $member['student_id'] === (string) $studentId
                                            ): ?>
                                                <span class="text-xs text-blue-700 font-medium">
                                                    (You)
                                                </span>
                                            <?php endif; ?>
                                        </p>

                                        <p class="text-xs text-slate-400 mt-1">
                                            <?php if (!empty($member['membership_date'])): ?>
                                                Joined <?= e(date('M j, Y', strtotime($member['membership_date']))) ?>
                                            <?php else: ?>
                                                Membership date unavailable
                                            <?php endif; ?>
                                        </p>

                                    </div>

                                    <span class="shrink-0 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                        <?= e($member['role']) ?>
                                    </span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php else: ?>

                    <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center">

                        <div class="text-4xl mb-4">👥</div>

                        <h3 class="text-lg font-bold text-slate-800">
                            No Members Yet
                        </h3>

                        <p class="text-sm text-slate-500 mt-2">
                            There are currently no registered members in this organization.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

    <?php include __DIR__ . '/../php/components/footer.php'; ?>

    <script>
        $(document).ready(function () {

            $('.org-tab').on('click', function () {

                const selectedTab = $(this).data('tab');

                // Hide all tab panels.
                $('.org-panel').addClass('hidden');

                // Show selected panel.
                $('#tab-' + selectedTab).removeClass('hidden');

                // Reset tab styles.
                $('.org-tab')
                    .removeClass('border-[#1E3A5F] text-[#1E3A5F]')
                    .addClass('border-transparent text-slate-500');

                // Highlight active tab.
                $(this)
                    .removeClass('border-transparent text-slate-500')
                    .addClass('border-[#1E3A5F] text-[#1E3A5F]');
            });

        });
    </script>

</body>
</html>
