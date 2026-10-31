
<?php
require_once '../php/db.php';

// Get filter values
$search = trim($_GET['search'] ?? '');
$organization = trim($_GET['organization'] ?? '');
$status = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'nearest';

$allowedStatuses = ['all', 'upcoming', 'completed'];
$allowedSorts = ['nearest', 'newest', 'oldest'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'nearest';
}

// Pagination
$perPage = 9;
$page = max(1, (int)($_GET['page'] ?? 1));

// Build WHERE conditions
$whereConditions = ["e.status != 'cancelled'"];
$params = [];

if ($search !== '') {
    $whereConditions[] = "(
        e.event_name ILIKE :search
        OR e.description ILIKE :search
        OR o.organization_name ILIKE :search
    )";

    $params[':search'] = '%' . $search . '%';
}

if ($organization !== '') {
    $whereConditions[] = "o.organization_name ILIKE :organization";
    $params[':organization'] = '%' . $organization . '%';
}

if ($status === 'upcoming') {
    $whereConditions[] = "e.status IN ('pending', 'confirmed')";
    $whereConditions[] = "es.event_date >= CURRENT_DATE";
} elseif ($status === 'completed') {
    $whereConditions[] = "e.status = 'completed'";
}

$whereSQL = implode(' AND ', $whereConditions);

// Sorting
switch ($sort) {
    case 'newest':
        $orderSQL = "es.event_date DESC, es.start_time DESC";
        break;

    case 'oldest':
        $orderSQL = "es.event_date ASC, es.start_time ASC";
        break;

    default:
        $orderSQL = "
            CASE WHEN es.event_date >= CURRENT_DATE THEN 0 ELSE 1 END,
            CASE
                WHEN es.event_date >= CURRENT_DATE THEN es.event_date
            END ASC,
            CASE
                WHEN es.event_date < CURRENT_DATE THEN es.event_date
            END DESC,
            es.start_time ASC
        ";
        break;
}

// Count matching events
$countSQL = "
    SELECT COUNT(*)
    FROM event e
    INNER JOIN organization o
        ON e.organization_id = o.organization_id
    INNER JOIN event_schedule es
        ON e.event_id = es.event_id
    INNER JOIN venue v
        ON es.venue_id = v.venue_id
    WHERE $whereSQL
";

$countStmt = $pdo->prepare($countSQL);

foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}

$countStmt->execute();
$totalEvents = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalEvents / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Retrieve events
$eventsSQL = "
    SELECT
        e.event_id,
        e.event_name,
        e.description,
        e.status,
        o.organization_name,
        es.event_date,
        es.start_time,
        es.end_time,
        v.venue_name,
        v.location
    FROM event e
    INNER JOIN organization o
        ON e.organization_id = o.organization_id
    INNER JOIN event_schedule es
        ON e.event_id = es.event_id
    INNER JOIN venue v
        ON es.venue_id = v.venue_id
    WHERE $whereSQL
    ORDER BY $orderSQL
    LIMIT :limit OFFSET :offset
";

$eventsStmt = $pdo->prepare($eventsSQL);

foreach ($params as $key => $value) {
    $eventsStmt->bindValue($key, $value);
}

$eventsStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$eventsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$eventsStmt->execute();
$events = $eventsStmt->fetchAll();

// Format helpers
function formatEventDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatEventTime($time) {
    return date('g:i A', strtotime($time));
}

function buildPageUrl($pageNumber) {
    $query = $_GET;
    $query['page'] = $pageNumber;

    return '?' . http_build_query($query);
}

// Render event results
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

    <?php if (empty($events)): ?>

        <div class="col-span-full bg-white border border-slate-200 rounded-xl p-10 text-center">
            <i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-slate-700">No events found</h3>
            <p class="text-sm text-slate-500 mt-2">
                Try changing your search or filters.
            </p>
        </div>

    <?php else: ?>

        <?php foreach ($events as $event): ?>

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                
                <div class="h-2 bg-[#1E3A5F]"></div>

                <div class="p-5">

                    <div class="flex items-start justify-between gap-3 mb-3">

                        <span class="text-xs font-semibold text-[#1E3A5F] bg-blue-50 px-3 py-1 rounded-full max-w-[65%] truncate">
                            <?= htmlspecialchars($event['organization_name']) ?>
                        </span>

                        <?php
                        $statusClass = match ($event['status']) {
                            'confirmed' => 'bg-green-100 text-green-700',
                            'completed' => 'bg-slate-100 text-slate-600',
                            default => 'bg-amber-100 text-amber-700'
                        };
                        ?>

                        <span class="text-xs font-medium px-2.5 py-1 rounded-full <?= $statusClass ?>">
                            <?= htmlspecialchars(ucfirst($event['status'])) ?>
                        </span>

                    </div>

                    <h3 class="text-lg font-bold text-slate-800 mb-2 line-clamp-2">
                        <?= htmlspecialchars($event['event_name']) ?>
                    </h3>

                    <p class="text-sm text-slate-500 mb-5 line-clamp-2 min-h-[2.5rem]">
                        <?= htmlspecialchars($event['description'] ?? 'No description available.') ?>
                    </p>

                    <div class="space-y-2 text-sm text-slate-600 mb-5">

                        <div class="flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-[#D4A64A] w-4"></i>
                            <?= formatEventDate($event['event_date']) ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <i class="fa-regular fa-clock text-[#D4A64A] w-4"></i>
                            <?= formatEventTime($event['start_time']) ?>
                            -
                            <?= formatEventTime($event['end_time']) ?>
                        </div>

                        <div class="flex items-center gap-2 min-w-0">
                            <i class="fa-solid fa-location-dot text-[#D4A64A] w-4 shrink-0"></i>
                            <span class="truncate">
                                <?= htmlspecialchars($event['venue_name']) ?>,
                                <?= htmlspecialchars($event['location']) ?>
                            </span>
                        </div>

                    </div>

                    <a
                        href="event.php?id=<?php echo (int)$event['event_id']; ?>"
                        class="block text-center w-full bg-[#1E3A5F] hover:bg-[#152c49] text-white text-sm font-semibold py-3 rounded-lg transition-colors"
                    >
                        View Event Details
                    </a>

                </div>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php if ($totalPages > 1): ?>

    <div class="flex justify-center items-center gap-2 mt-8">

        <?php if ($page > 1): ?>
            <a
                href="<?= htmlspecialchars(buildPageUrl($page - 1)) ?>"
                class="pagination-link px-4 py-2 rounded-lg border border-slate-200 text-sm hover:bg-slate-100"
            >
                Previous
            </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

            <a
                href="<?= htmlspecialchars(buildPageUrl($i)) ?>"
                class="pagination-link px-4 py-2 rounded-lg text-sm font-medium <?= $i === $page
                    ? 'bg-[#1E3A5F] text-white'
                    : 'border border-slate-200 text-slate-600 hover:bg-slate-100' ?>"
            >
                <?= $i ?>
            </a>

        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a
                href="<?= htmlspecialchars(buildPageUrl($page + 1)) ?>"
                class="pagination-link px-4 py-2 rounded-lg border border-slate-200 text-sm hover:bg-slate-100"
            >
                Next
            </a>
        <?php endif; ?>

    </div>

<?php endif; ?>

<?php
$resultsHTML = ob_get_clean();

// AJAX response
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    echo json_encode([
        'html' => $resultsHTML,
        'count' => $totalEvents
    ]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Browse Events | UniSync</title>

    <script src="../javascript/jquery.js"></script>
    <script src="../javascript/tailwind.js"></script>

</head>

<body class="bg-slate-50 text-slate-800">

    <?php include '../php/components/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-5 py-8">

        <!-- Filters -->
        <form id="filterForm" class="bg-white border border-slate-200 rounded-xl p-5 mb-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div>
                    <label for="organization" class="block text-sm font-medium text-slate-700 mb-2">
                        Organization
                    </label>

                    <input
                        type="text"
                        id="organization"
                        name="organization"
                        value="<?= htmlspecialchars($organization, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Search organization..."
                        autocomplete="off"
                        class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-sm text-slate-700 outline-none focus:border-[#1E3A5F] focus:ring-2 focus:ring-blue-100"
                    >
                </div>

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

        <!-- Event count -->
        <div class="flex items-center justify-between mb-5">
            <h2 id="eventCount" class="hidden text-lg font-semibold text-slate-800"></h2>

            <span id="loadingIndicator" class="hidden text-sm text-slate-500">
                Updating...
            </span>
        </div>

        <!-- Event results -->
        <div id="eventResults">
            <?= $resultsHTML ?>
        </div>

    </div>

    <?php include '../php/components/footer.php'; ?>

    <script src='../javascript/app/browse.js'></script>

</body>
</html>
