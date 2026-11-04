<?php
session_start();

require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

$studentId = $_SESSION['student_id'] ?? null;

if (!$studentId) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in.'
    ]);
    exit;
}

$type = $_GET['type'] ?? 'events';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');

try {

    if ($type === 'events') {

        // Count matching registered events.
        $countQuery = $pdo->prepare(
            "SELECT COUNT(*)
             FROM registration r
             INNER JOIN event e ON r.event_id = e.event_id
             INNER JOIN organization o
                ON e.organization_id = o.organization_id
             WHERE r.student_id = :student_id
               AND o.organization_name ILIKE :search"
        );

        $countQuery->execute([
            ':student_id' => $studentId,
            ':search' => '%' . $search . '%'
        ]);

        $total = (int)$countQuery->fetchColumn();

        // Retrieve only the current page.
        $query = $pdo->prepare(
            "SELECT
                e.event_id,
                e.event_name,
                e.status,
                o.organization_name,
                r.registration_date,
                (
                    SELECT MIN(es.event_date)
                    FROM event_schedule es
                    WHERE es.event_id = e.event_id
                      AND es.event_date >= CURRENT_DATE
                ) AS next_event_date
             FROM registration r
             INNER JOIN event e ON r.event_id = e.event_id
             INNER JOIN organization o
                ON e.organization_id = o.organization_id
             WHERE r.student_id = :student_id
               AND o.organization_name ILIKE :search
             ORDER BY r.registration_date DESC, r.registration_id DESC
             LIMIT :limit OFFSET :offset"
        );

        $query->bindValue(':student_id', $studentId);
        $query->bindValue(':search', '%' . $search . '%');
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        $events = $query->fetchAll(PDO::FETCH_ASSOC);

        ob_start();

        if (empty($events)) {
            echo '<p class="text-center text-slate-500 py-10">No registered events found.</p>';
        }

        foreach ($events as $event):
        ?>

            <div class="border border-slate-200 rounded-xl p-5 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div class="min-w-0">

                    <h3 class="font-bold text-lg text-[#1E3A5F]">
                        <?= htmlspecialchars($event['event_name']) ?>
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        <?= htmlspecialchars($event['organization_name']) ?>
                    </p>

                    <p class="text-sm text-slate-500 mt-2">
                        <?php if ($event['next_event_date']): ?>
                            Next schedule:
                            <?= date('F j, Y', strtotime($event['next_event_date'])) ?>
                        <?php else: ?>
                            No upcoming schedule
                        <?php endif; ?>
                    </p>

                    <span class="inline-block mt-2 text-xs font-semibold px-3 py-1 rounded-full
                        <?= $event['status'] === 'confirmed'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-slate-100 text-slate-600' ?>">
                        <?= htmlspecialchars(ucfirst($event['status'])) ?>
                    </span>

                </div>

                <div class="flex gap-2 shrink-0">

                    <a
                        href="event.php?id=<?= (int)$event['event_id'] ?>"
                        class="px-4 py-2 rounded-lg bg-[#1E3A5F] text-white text-sm font-semibold hover:bg-[#294D78]">
                        View Event
                    </a>

                    <button
                        type="button"
                        class="cancel-registration px-4 py-2 rounded-lg border border-red-300 text-red-600 text-sm font-semibold hover:bg-red-50"
                        data-event-id="<?= (int)$event['event_id'] ?>">
                        Cancel
                    </button>

                </div>

            </div>

        <?php
        endforeach;

        $html = ob_get_clean();

    } elseif ($type === 'organizations') {

        // Count joined organizations.
        $countQuery = $pdo->prepare(
            "SELECT COUNT(*)
             FROM membership m
             INNER JOIN organization o
                ON m.organization_id = o.organization_id
             WHERE m.student_id = :student_id
               AND o.organization_name ILIKE :search"
        );

        $countQuery->execute([
            ':student_id' => $studentId,
            ':search' => '%' . $search . '%'
        ]);

        $total = (int)$countQuery->fetchColumn();

        // Retrieve only the current page.
        $query = $pdo->prepare(
            "SELECT
                o.organization_id,
                o.organization_name,
                o.status,
                m.role,
                m.membership_date
             FROM membership m
             INNER JOIN organization o
                ON m.organization_id = o.organization_id
             WHERE m.student_id = :student_id
               AND o.organization_name ILIKE :search
             ORDER BY o.organization_name ASC
             LIMIT :limit OFFSET :offset"
        );

        $query->bindValue(':student_id', $studentId);
        $query->bindValue(':search', '%' . $search . '%');
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        $organizations = $query->fetchAll(PDO::FETCH_ASSOC);

        ob_start();

        if (empty($organizations)) {
            echo '<p class="text-center text-slate-500 py-10">No joined organizations found.</p>';
        }

        foreach ($organizations as $organization):
        ?>

            <div class="border border-slate-200 rounded-xl p-5 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div>

                    <h3 class="font-bold text-lg text-[#1E3A5F]">
                        <?= htmlspecialchars($organization['organization_name']) ?>
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        Role:
                        <?= htmlspecialchars(ucfirst($organization['role'])) ?>
                    </p>

                    <p class="text-sm text-slate-500 mt-1">
                        Joined:
                        <?= date('F j, Y', strtotime($organization['membership_date'])) ?>
                    </p>

                </div>

                <a
                    href="organization.php?id=<?= (int)$organization['organization_id'] ?>"
                    class="inline-flex justify-center px-4 py-2 rounded-lg bg-[#1E3A5F] text-white text-sm font-semibold hover:bg-[#294D78]">
                    View Organization
                </a>

            </div>

        <?php
        endforeach;

        $html = ob_get_clean();

    } else {
        throw new Exception('Invalid dashboard section.');
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'total' => $total,
        'page' => $page,
        'totalPages' => (int)ceil($total / $limit)
    ]);

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load dashboard information.'
    ]);
}