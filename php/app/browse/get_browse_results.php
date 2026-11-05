<?php
require_once __DIR__ . '/../../db.php';

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Filters
$search = trim($_GET['search'] ?? '');
$view = $_GET['view'] ?? 'events';
$organizationId = (int) ($_GET['organization_id'] ?? 0);
$status = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'nearest';

if (!in_array($view, ['events', 'organizations'], true)) {
    $view = 'events';
}

if (!in_array($status, ['all', 'upcoming', 'completed'], true)) {
    $status = 'all';
}

if (!in_array($sort, ['nearest', 'newest', 'oldest'], true)) {
    $sort = 'nearest';
}

// Organization filters
$university = trim($_GET['university'] ?? '');
$orgSort = $_GET['org_sort'] ?? 'name_asc';

if (!in_array($orgSort, ['name_asc', 'name_desc', 'events_desc', 'events_asc'], true)) {
    $orgSort = 'name_asc';
}

// Escape LIKE wildcards so "%" and "_" are searched literally.
$searchLike = '%' . addcslashes($search, '%_\\') . '%';

$page = max(1, (int) ($_GET['page'] ?? 1));

// Sorting (events)
switch ($sort) {
    case 'newest':
        $orderSQL = 'es.event_date DESC, es.start_time DESC';
        break;

    case 'oldest':
        $orderSQL = 'es.event_date ASC, es.start_time ASC';
        break;

    default:
        $orderSQL = "
            CASE WHEN es.event_date >= CURRENT_DATE THEN 0 ELSE 1 END,
            CASE WHEN es.event_date >= CURRENT_DATE THEN es.event_date END ASC,
            CASE WHEN es.event_date < CURRENT_DATE THEN es.event_date END DESC,
            es.start_time ASC
        ";
        break;
}

// Formatting helpers
function formatEventDate($date)
{
    return date('M d, Y', strtotime($date));
}

function formatEventTime($time)
{
    return date('g:i A', strtotime($time));
}

function statusBadgeClass($status)
{
    return match ($status) {
        'confirmed' => 'bg-green-100 text-green-700',
        'completed' => 'bg-slate-100 text-slate-600',
        default => 'bg-amber-100 text-amber-700'
    };
}

function buildPageUrl($pageNumber)
{
    $query = $_GET;
    $query['page'] = $pageNumber;
    unset($query['ajax']);

    return '?' . http_build_query($query);
}

function renderPagination($page, $totalPages)
{
    if ($totalPages <= 1) {
        return;
    }
    ?>
    <div class="flex justify-center items-center gap-2 mt-8">
        <?php if ($page > 1): ?>
            <a href="<?= h(buildPageUrl($page - 1)) ?>"
               class="pagination-link px-4 py-2 rounded-lg border border-slate-200 text-sm hover:bg-slate-100">
                Previous
            </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= h(buildPageUrl($i)) ?>"
               class="pagination-link px-4 py-2 rounded-lg text-sm font-medium <?= $i === $page
                   ? 'bg-[#1E3A5F] text-white'
                   : 'border border-slate-200 text-slate-600 hover:bg-slate-100' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?= h(buildPageUrl($page + 1)) ?>"
               class="pagination-link px-4 py-2 rounded-lg border border-slate-200 text-sm hover:bg-slate-100">
                Next
            </a>
        <?php endif; ?>
    </div>
    <?php
}

// Organization filter (events view only).
$organizationName = '';

if ($view === 'events' && $organizationId > 0) {
    $orgStmt = $pdo->prepare("
        SELECT organization_name
        FROM organization
        WHERE organization_id = :id
          AND status = 'active'
    ");

    $orgStmt->bindValue(':id', $organizationId, PDO::PARAM_INT);
    $orgStmt->execute();

    $organizationName = (string) $orgStmt->fetchColumn();

    if ($organizationName === '') {
        $organizationId = 0;
    }
}

// Universities for the organization filter dropdown.
$universities = $pdo->query("
    SELECT DISTINCT university_name
    FROM organization
    WHERE status = 'active'
    ORDER BY university_name ASC
")->fetchAll(PDO::FETCH_COLUMN);

$events = [];
$organizations = [];
$totalItems = 0;
$totalPages = 1;

if ($view === 'events') {

    $perPage = 9;

    $params = [];
    $orderParams = [];
    $priorityOrder = '';

    $where = [
        "e.status NOT IN ('cancelled', 'deleted')",
        "o.status = 'active'",
        "es.status IN ('confirmed', 'completed')",
        "v.status = 'active'"
    ];

    if ($search !== '') {
        $where[] = "(
            e.event_name ILIKE :s1
            OR e.description ILIKE :s2
            OR o.organization_name ILIKE :s3
        )";

        $params[':s1'] = $searchLike;
        $params[':s2'] = $searchLike;
        $params[':s3'] = $searchLike;

        // Name matches first, then organization matches, then description-only matches.
        $priorityOrder = "
            CASE
                WHEN e.event_name ILIKE :s4 THEN 0
                WHEN o.organization_name ILIKE :s5 THEN 1
                ELSE 2
            END,
        ";

        $orderParams[':s4'] = $searchLike;
        $orderParams[':s5'] = $searchLike;
    }

    if ($organizationId > 0) {
        $where[] = 'e.organization_id = :organization_id';
        $params[':organization_id'] = $organizationId;
    }

    // Upcoming: confirmed events with at least one upcoming confirmed schedule.
    if ($status === 'upcoming') {
        $where[] = "e.status = 'confirmed'";
        $where[] = "
            EXISTS (
                SELECT 1
                FROM event_schedule upcoming_es
                WHERE upcoming_es.event_id = e.event_id
                  AND upcoming_es.status = 'confirmed'
                  AND upcoming_es.event_date >= CURRENT_DATE
            )
        ";
    }

    // Completed: completed events with at least one completed schedule.
    if ($status === 'completed') {
        $where[] = "e.status = 'completed'";
        $where[] = "
            EXISTS (
                SELECT 1
                FROM event_schedule completed_es
                WHERE completed_es.event_id = e.event_id
                  AND completed_es.status = 'completed'
            )
        ";
    }

    $whereSQL = implode(' AND ', $where);

    $fromSQL = "
        FROM event e
        INNER JOIN organization o
            ON e.organization_id = o.organization_id
        INNER JOIN event_schedule es
            ON e.event_id = es.event_id
        INNER JOIN venue v
            ON es.venue_id = v.venue_id
        WHERE $whereSQL
    ";

    // Count distinct events to prevent duplicate cards from multiple schedules.
    $countStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT e.event_id)
        $fromSQL
    ");

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $totalItems = (int) $countStmt->fetchColumn();

    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $eventsStmt = $pdo->prepare("
        SELECT DISTINCT ON (e.event_id)
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
        $fromSQL
        ORDER BY
            e.event_id,
            $priorityOrder
            $orderSQL
        LIMIT :limit OFFSET :offset
    ");

    foreach (array_merge($params, $orderParams) as $key => $value) {
        $eventsStmt->bindValue($key, $value);
    }

    $eventsStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $eventsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $eventsStmt->execute();
    $events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

} else {

    $perPage = 9;

    $where = ["o.status = 'active'"];
    $params = [];

    if ($search !== '') {
        $where[] = 'o.organization_name ILIKE :s1';
        $params[':s1'] = $searchLike;
    }

    if ($university !== '') {
        $where[] = 'o.university_name = :university';
        $params[':university'] = $university;
    }

    switch ($orgSort) {
        case 'name_desc':
            $orgOrderSQL = 'o.organization_name DESC';
            break;

        case 'events_desc':
            $orgOrderSQL = 'event_count DESC, o.organization_name ASC';
            break;

        case 'events_asc':
            $orgOrderSQL = 'event_count ASC, o.organization_name ASC';
            break;

        default:
            $orgOrderSQL = 'o.organization_name ASC';
            break;
    }

    $whereSQL = implode(' AND ', $where);

    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM organization o
        WHERE $whereSQL
    ");

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $totalItems = (int) $countStmt->fetchColumn();

    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $orgsStmt = $pdo->prepare("
        SELECT
            o.organization_id,
            o.organization_name,
            o.university_name,
            o.description,
            (
                SELECT COUNT(*)
                FROM event e
                WHERE e.organization_id = o.organization_id
                  AND e.status NOT IN ('cancelled', 'deleted')
            ) AS event_count
        FROM organization o
        WHERE $whereSQL
        ORDER BY $orgOrderSQL
        LIMIT :limit OFFSET :offset
    ");

    foreach ($params as $key => $value) {
        $orgsStmt->bindValue($key, $value);
    }

    $orgsStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $orgsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $orgsStmt->execute();
    $organizations = $orgsStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Count label
if ($view === 'events') {
    $countLabel = number_format($totalItems)
        . ($totalItems === 1 ? ' event found' : ' events found');
} else {
    $countLabel = number_format($totalItems)
        . ($totalItems === 1 ? ' organization found' : ' organizations found');
}

// Organization filter chip
ob_start();

if ($view === 'events' && $organizationId > 0):
    ?>
    <div class="flex items-center gap-2 mb-5">
        <span class="inline-flex items-center gap-2 bg-blue-50 text-[#1E3A5F] text-sm font-medium px-3 py-1.5 rounded-full">
            Organization: <?= h($organizationName) ?>

            <button type="button"
                    id="clearOrgFilter"
                    class="text-lg leading-none hover:text-red-600"
                    aria-label="Clear organization filter">
                &times;
            </button>
        </span>
    </div>
    <?php
endif;

$chipHTML = ob_get_clean();

// Render results
ob_start();

if ($view === 'events'):
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php if (empty($events)): ?>
            <div class="col-span-full bg-white border border-slate-200 rounded-xl p-10 text-center">
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
                                <?= h($event['organization_name']) ?>
                            </span>

                            <span class="text-xs font-medium px-2.5 py-1 rounded-full <?= statusBadgeClass($event['status']) ?>">
                                <?= h(ucfirst($event['status'])) ?>
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-800 mb-2 line-clamp-2">
                            <?= h($event['event_name']) ?>
                        </h3>

                        <p class="text-sm text-slate-500 mb-5 line-clamp-2 min-h-[2.5rem]">
                            <?= h($event['description'] ?? 'No description available.') ?>
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
                                    <?= h($event['venue_name']) ?>,
                                    <?= h($event['location']) ?>
                                </span>
                            </div>
                        </div>

                        <a href="event.php?id=<?= (int) $event['event_id'] ?>"
                           class="block text-center w-full bg-[#1E3A5F] hover:bg-[#152c49] text-white text-sm font-semibold py-3 rounded-lg transition-colors">
                            View Event Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
else:
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php if (empty($organizations)): ?>
            <div class="col-span-full bg-white border border-slate-200 rounded-xl p-10 text-center">
                <h3 class="text-lg font-semibold text-slate-700">No organizations found</h3>
                <p class="text-sm text-slate-500 mt-2">
                    Try a different search.
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($organizations as $org):
                $orgId = (int) $org['organization_id'];
                $eventCount = (int) $org['event_count'];

                $orgSlug = strtolower(trim(
                    preg_replace('/[^a-z0-9]+/i', '-', $org['organization_name']),
                    '-'
                ));

                $orgPageUrl = 'organization.php?id='
                    . urlencode($orgId)
                    . '&name='
                    . urlencode($orgSlug);
                ?>
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="h-2 bg-[#1E3A5F]"></div>

                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-lg font-bold text-slate-800 mb-1 line-clamp-2">
                            <?= h($org['organization_name']) ?>
                        </h3>

                        <p class="text-xs text-slate-400 mb-3 truncate">
                            <?= h($org['university_name']) ?>
                        </p>

                        <p class="text-sm text-slate-500 mb-4 line-clamp-3 min-h-[3.75rem]">
                            <?= h($org['description'] ?? 'No description available.') ?>
                        </p>

                        <p class="text-xs text-slate-400 mb-5">
                            <?= $eventCount ?>
                            <?= $eventCount === 1 ? 'event' : 'events' ?>
                        </p>

                        <a href="<?= h($orgPageUrl) ?>"
                           class="mt-auto block text-center w-full bg-[#1E3A5F] hover:bg-[#152c49] text-white text-sm font-semibold py-3 rounded-lg transition-colors">
                            View Organization
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
endif;

renderPagination($page, $totalPages);

$resultsHTML = ob_get_clean();

// AJAX requests return JSON.
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'html' => $resultsHTML,
        'chip' => $chipHTML,
        'label' => $countLabel,
        'count' => $totalItems
    ]);

    exit;
}