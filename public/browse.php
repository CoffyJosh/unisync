<?php
require_once __DIR__ . '/../php/app/browse/get_browse_results.php';

$tabActive = 'bg-[#1E3A5F] text-white border border-[#1E3A5F]';
$tabInactive = 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Browse | UniSync</title>

    <script src="../javascript/jquery.js"></script>
    <script src="../javascript/tailwind.js"></script>
</head>

<body class="bg-slate-50 text-slate-800">

    <?php include __DIR__ . '/../php/components/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-5 py-8">

        <!-- View tabs -->
        <div class="flex gap-2 mb-5">
            <button type="button"
                    class="view-tab px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $view === 'events' ? $tabActive : $tabInactive ?>"
                    data-view="events">
                Events
            </button>

            <button type="button"
                    class="view-tab px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $view === 'organizations' ? $tabActive : $tabInactive ?>"
                    data-view="organizations">
                Organizations
            </button>
        </div>

        <!-- Filters -->
        <form id="filterForm" class="bg-white border border-slate-200 rounded-xl p-5 mb-6">

            <input type="hidden" id="view" name="view" value="<?= h($view) ?>">
            <input type="hidden" id="organization_id" name="organization_id" value="<?= $organizationId > 0 ? (int) $organizationId : '' ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700 mb-2">
                        Event Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-sm text-slate-700 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Events</option>
                        <option value="upcoming" <?= $status === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div>
                    <label for="sort" class="block text-sm font-medium text-slate-700 mb-2">
                        Sort By
                    </label>

                    <select
                        id="sort"
                        name="sort"
                        class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-sm text-slate-700 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="nearest" <?= $sort === 'nearest' ? 'selected' : '' ?>>Nearest Date</option>
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    </select>
                </div>

            </div>
        </form>

        <!-- Active organization filter -->
        <div id="orgChip"><?= $chipHTML ?></div>

        <!-- Result count and loading indicator -->
        <div class="flex items-center justify-between mb-5">
            <h2 id="eventCount" class="text-lg font-semibold text-slate-800">
                <?= h($countLabel) ?>
            </h2>

            <span id="loadingIndicator" class="hidden text-sm text-slate-500">
                Updating...
            </span>
        </div>

        <!-- Results -->
        <div id="eventResults">
            <?= $resultsHTML ?>
        </div>

    </div>

    <?php include __DIR__ . '/../php/components/footer.php'; ?>

    <script src="../javascript/app/browse.js"></script>

</body>
</html>