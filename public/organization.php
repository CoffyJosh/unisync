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

$membershipLabel = $isMember
    ? ucfirst(strtolower($membership['role']))
    : 'Guest';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($organizationName) ?> | UniSync</title>

    <script src="../javascript/tailwind.js"></script>
    <script src="../javascript/jquery.js"></script>
    <script src="../javascript/app/organization.js"></script>
</head>

<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">

    <?php include __DIR__ . '/../php/components/navbar.php'; ?>

    <main class="flex-1 w-full max-w-6xl mx-auto px-5 sm:px-8 py-8">

        <!-- Back Navigation -->
        <a href="javascript:history.back()"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-900 mb-6">
            <span>&larr;</span>
            Back
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
                            <?= e($organization['university_name']) ?>
                        </span>

                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $statusClass ?>">
                            <?= e($organization['status']) ?>
                        </span>

                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight break-words">
                        <?= e($organizationName) ?>
                    </h1>

                    <p class="text-sm text-blue-100/70 mt-2">
                        Discover the organization, its members, and its activities.
                    </p>

                </div>

                <!-- Membership / Management Actions (right side, stacked) -->
                <div class="flex flex-col gap-2 shrink-0 w-full sm:w-40">

                    <?php if (!$isMember): ?>

                        <button
                            type="button"
                            id="joinOrganization"
                            data-organization-id="<?= (int) $organizationId ?>"
                            class="w-full inline-flex items-center justify-center gap-1.5 bg-white text-[#142C4C] hover:bg-blue-50 text-xs font-semibold px-3 py-2 rounded-lg transition"
                        >
                            <i class="fa-solid fa-user-plus"></i>
                            Join Organization
                        </button>

                    <?php else: ?>

                        <button
                            type="button"
                            id="leaveOrganization"
                            data-organization-id="<?= (int) $organizationId ?>"
                            data-role="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"
                            data-member-count="<?= count($members) ?>"
                            class="w-full inline-flex items-center justify-center gap-1.5 border border-white/30 text-white hover:bg-white/10 text-xs font-semibold px-3 py-2 rounded-lg transition"
                        >
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Leave Organization
                        </button>

                    <?php endif; ?>

                    <?php if ($canManageEvents): ?>

                        <button
                            type="button"
                            id="openEditOrganization"
                            class="w-full inline-flex items-center justify-center gap-1.5 bg-white text-[#142C4C] hover:bg-blue-50 text-xs font-semibold px-3 py-2 rounded-lg transition"
                        >
                            <i class="fa-solid fa-pen-to-square"></i>
                            Edit Organization
                        </button>

                    <?php endif; ?>

                </div>

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

            <!-- User Membership -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Your Membership
                </p>

                <p class="text-2xl font-extrabold mt-2 <?= $isMember ? 'text-emerald-600' : 'text-slate-500' ?>">
                    <?= e($membershipLabel) ?>
                </p>

                <p class="text-xs text-slate-400 mt-1">
                    <?= $isMember
                        ? 'Your role in this organization'
                        : 'You are not a member of this organization'
                    ?>
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

                <!-- Events Heading and Create Button -->
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">

                    <div>
                        <h2 class="text-2xl font-bold text-[#1E3A5F]">
                            Organization Events
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">
                            Browse the events organized by <?= e($organizationName) ?>.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">

                        <?php if ($canManageEvents): ?>
                            <a href="create_event.php?organization_id=<?= (int) $organizationId ?>"
                               class="inline-flex items-center gap-2 bg-[#1E3A5F] hover:bg-[#294D78] text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                                <span>+</span>
                                Create Event
                            </a>
                        <?php endif; ?>

                    </div>

                </div>

                <!-- Persistent Events Container -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6">

                    <?php if (!empty($events)): ?>

                        <!-- Event Cards -->
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

                                <article class="flex flex-col h-full border border-slate-200 rounded-2xl overflow-hidden hover:shadow-md transition">

                                    <div class="p-5 flex flex-col flex-1">

                                        <!-- Organization and Status -->
                                        <div class="flex items-start justify-between gap-3 mb-3">

                                            <span class="text-xs font-semibold text-[#1E3A5F] bg-blue-50 px-3 py-1 rounded-full max-w-[65%] truncate">
                                                <?= e($organizationName) ?>
                                            </span>

                                            <span class="text-xs font-medium px-2.5 py-1 rounded-full <?= $eventStatusClass ?>">
                                                <?= e(ucfirst($eventStatus)) ?>
                                            </span>

                                        </div>

                                        <!-- Event Name (up to 2 lines) -->
                                        <h3 class="text-lg font-bold text-slate-800 mb-2 line-clamp-2">
                                            <?= e($event['event_name']) ?>
                                        </h3>

                                        <!-- Description (up to 2 lines) -->
                                        <p class="text-sm text-slate-500 line-clamp-2">
                                            <?= e($event['description'] ?: 'No description available.') ?>
                                        </p>

                                        <!-- Schedule and actions, pushed to the bottom of the card -->
                                        <div class="mt-auto pt-4">

                                            <div class="flex items-start gap-2 text-sm text-slate-600 mb-4">
                                                <i class="fa-regular fa-calendar text-[#D4A64A] w-4 mt-0.5"></i>

                                                <span class="whitespace-pre-line line-clamp-3">
                                                    <?= e($event['schedules'] ?: 'No schedule assigned yet.') ?>
                                                </span>
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex gap-2">

                                                <a href="event.php?id=<?= (int) $event['event_id'] ?>"
                                                class="flex-1 text-center bg-[#1E3A5F] hover:bg-[#152c49] text-white text-sm font-semibold py-3 rounded-lg transition-colors">
                                                    View
                                                </a>

                                                <?php if ($canManageEvents): ?>
                                                    <a href="edit_event.php?id=<?= (int) $event['event_id'] ?>"
                                                    class="flex-1 text-center border border-[#1E3A5F] text-[#1E3A5F] hover:bg-blue-50 text-sm font-semibold py-3 rounded-lg transition-colors">
                                                        <i class="fa-solid fa-pen-to-square mr-1"></i>
                                                        Edit
                                                    </a>
                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <!-- Empty State Inside the Same Container -->
                        <div class="py-6 text-center">

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

                        <div class="flex flex-col">

                            <?php foreach ($members as $member): ?>

                            <?php
                                $isCurrentUser = $accountId !== null
                                    && (int) $member['account_id'] === (int) $accountId;

                                // Text color for the role tag/dropdown (the border follows the text color).
                                $dropdownColors = [
                                    'Admin'   => 'text-blue-700',
                                    'Manager' => 'text-amber-600',
                                    'Member'  => 'text-green-700'
                                ];
                                $dropdownColor = $dropdownColors[$member['role']] ?? 'text-slate-600';
                            ?>

                            <div class="member-row flex items-center gap-4 p-5 border-b border-slate-100">

                                <div class="w-12 h-12 shrink-0 rounded-full bg-blue-50 text-[#1E3A5F] flex items-center justify-center font-bold">
                                    <?= e(strtoupper(substr($member['account_name'], 0, 1))) ?>
                                </div>

                                <div class="flex-1 min-w-0">

                                    <p class="font-bold text-slate-800 truncate">
                                        <?= e($member['account_name']) ?>

                                        <?php if ($isCurrentUser): ?>
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

                                <!-- Role + Kick/Leave, same line, right-aligned -->
                                <div class="flex items-center justify-end gap-2 shrink-0">

                                    <?php if ($isAdmin): ?>

                                        <div class="role-wrap relative <?= $dropdownColor ?>">

                                            <select
                                                class="member-role-select appearance-none cursor-pointer h-9 w-32 rounded-full bg-white border-2 border-current text-inherit text-xs font-bold pl-4 pr-9 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                                data-membership-id="<?= (int) $member['membership_id'] ?>"
                                                data-is-self="<?= $isCurrentUser ? 'true' : 'false' ?>"
                                                data-member-name="<?= e($member['account_name']) ?>"
                                                data-current-role="<?= e($member['role']) ?>"
                                            >
                                                <option class="text-slate-700" value="Admin" <?= $member['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                                <option class="text-slate-700" value="Manager" <?= $member['role'] === 'Manager' ? 'selected' : '' ?>>Manager</option>
                                                <option class="text-slate-700" value="Member" <?= $member['role'] === 'Member' ? 'selected' : '' ?>>Member</option>
                                            </select>

                                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-3 h-3"
                                                 viewBox="0 0 12 12" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 4l4 4 4-4"/>
                                            </svg>

                                        </div>

                                    <?php else: ?>

                                        <!-- Static tag: same look and width as the admin dropdown -->
                                        <span class="inline-flex items-center justify-center h-9 w-32 rounded-full bg-white border-2 border-current <?= $dropdownColor ?> text-xs font-bold">
                                            <?= e($member['role']) ?>
                                        </span>

                                    <?php endif; ?>

                                    <?php if ($isCurrentUser && $isAdmin): ?>

                                        <button
                                            type="button"
                                            class="leave-member inline-flex items-center justify-center h-9 w-20 bg-slate-600 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold"
                                            data-organization-id="<?= (int) $organizationId ?>"
                                            data-role="<?= e($role) ?>"
                                            data-member-count="<?= count($members) ?>"
                                        >
                                            Leave
                                        </button>

                                    <?php elseif (!$isCurrentUser && $canManageEvents && ($isAdmin || $member['role'] === 'Member')): ?>

                                        <button
                                            type="button"
                                            class="kick-member inline-flex items-center justify-center h-9 w-20 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold"
                                            data-membership-id="<?= (int) $member['membership_id'] ?>"
                                            data-member-name="<?= e($member['account_name']) ?>"
                                        >
                                            Kick
                                        </button>

                                    <?php endif; ?>

                                </div>

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

    <?php if ($canManageEvents): ?>
        <!-- Edit Organization Modal -->
        <div id="editOrganizationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-xl">

                <div class="flex items-center justify-between p-6 border-b border-slate-200">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            Edit Organization
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">
                            Update your organization's information.
                        </p>
                    </div>

                    <button
                        type="button"
                        id="closeEditOrganization"
                        class="text-slate-400 hover:text-slate-700 text-2xl"
                    >
                        &times;
                    </button>
                </div>

                <form
                    action="../php/app/organization/update_organization.php"
                    method="POST"
                    class="px-6 pb-6 pt-2 md:px-8 md:pb-8 md:pt-2 space-y-6"
                >
                    <input
                        type="hidden"
                        name="organization_id"
                        value="<?= (int) $organizationId ?>"
                    >

                    <div>
                        <label for="edit_organization_name"
                            class="block text-sm font-semibold text-slate-700 mb-2">
                            Organization Name
                        </label>

                        <input
                            type="text"
                            id="edit_organization_name"
                            name="organization_name"
                            maxlength="150"
                            required
                            value="<?= e($organization['organization_name']) ?>"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-100"
                        >
                    </div>

                    <div>
                        <label for="edit_university_name"
                            class="block text-sm font-semibold text-slate-700 mb-2">
                            University Name
                        </label>

                        <input
                            type="text"
                            id="edit_university_name"
                            name="university_name"
                            maxlength="150"
                            required
                            value="<?= e($organization['university_name']) ?>"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-100"
                        >
                    </div>

                    <div>
                        <label for="edit_description"
                            class="block text-sm font-semibold text-slate-700 mb-2">
                            Description
                            <span class="font-normal text-slate-400">(Optional)</span>
                        </label>

                        <textarea
                            id="edit_description"
                            name="description"
                            rows="5"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-100 resize-y"
                        ><?= e($organization['description']) ?></textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                        <button
                            type="button"
                            id="cancelEditOrganization"
                            class="rounded-xl border border-slate-300 px-6 py-3 font-semibold text-slate-600 hover:bg-slate-50"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-[#1E3A5F] px-6 py-3 font-semibold text-white hover:bg-blue-950"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>

            </div>
        </div>
    <?php endif; ?>

    <?php include __DIR__ . '/../php/components/footer.php'; ?>

    <!-- Remember the active tab per organization (survives reload / back navigation) -->
    <script>
        $(function () {
            var storageKey = 'unisync_org_tab_<?= (int) $organizationId ?>';

            function readTab() {
                try { return sessionStorage.getItem(storageKey); } catch (err) { return null; }
            }

            function saveTab(tab) {
                try { sessionStorage.setItem(storageKey, tab); } catch (err) {}
            }

            // Save whenever a tab is clicked.
            $(document).on('click', '.org-tab', function () {
                var tab = $(this).data('tab');
                if (tab) saveTab(tab);
            });

            // Restore the saved tab on load.
            var saved = readTab();
            if (!saved || saved === 'about') return;

            var $btn = $('.org-tab[data-tab="' + saved + '"]');
            if (!$btn.length) return;

            // Let organization.js handle the switch...
            $btn.trigger('click');

            // ...and fall back to switching manually if it didn't.
            var $panel = $('#tab-' + saved);
            if ($panel.hasClass('hidden')) {
                $('.org-panel').addClass('hidden');
                $panel.removeClass('hidden');

                $('.org-tab')
                    .removeClass('border-[#1E3A5F] text-[#1E3A5F] font-bold')
                    .addClass('border-transparent text-slate-500 font-semibold');

                $btn
                    .removeClass('border-transparent text-slate-500 font-semibold')
                    .addClass('border-[#1E3A5F] text-[#1E3A5F] font-bold');
            }
        });
    </script>

</body>
</html>