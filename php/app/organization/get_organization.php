
<?php

require_once __DIR__ . '/../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$studentId = $_SESSION['student_id'] ?? null;

$organizationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$organizationSlug = $_GET['name'] ?? '';

if (
    !$organizationId ||
    $organizationId <= 0 ||
    !is_string($organizationSlug) ||
    trim($organizationSlug) === ''
) {
    http_response_code(400);
    exit('Invalid organization URL.');
}

// Organization details
$sql = "
    SELECT
        organization_id,
        organization_name,
        description,
        status
    FROM organization
    WHERE organization_id = :organization_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':organization_id' => $organizationId
]);

$organization = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$organization) {
    http_response_code(404);
    exit('Organization not found.');
}

// Validate organization slug against its actual name.
$expectedSlug = strtolower(
    trim(
        preg_replace(
            '/[^a-z0-9]+/i',
            '-',
            $organization['organization_name']
        ),
        '-'
    )
);

if (!hash_equals($expectedSlug, strtolower(trim($organizationSlug)))) {
    http_response_code(404);
    exit('Organization not found.');
}

// Current student's membership
$membership = null;
$role = null;

if ($studentId !== null) {
    $membershipSql = "
        SELECT role, membership_date
        FROM membership
        WHERE student_id = :student_id
          AND organization_id = :organization_id
    ";

    $membershipStmt = $pdo->prepare($membershipSql);
    $membershipStmt->execute([
        ':student_id' => $studentId,
        ':organization_id' => $organizationId
    ]);

    $membership = $membershipStmt->fetch(PDO::FETCH_ASSOC);

    if ($membership) {
        $role = strtolower(trim($membership['role']));
    }
}

$isMember = $membership !== null && $membership !== false;

$isAdmin = $role === 'admin';

$canManageEvents = in_array($role, [
    'admin',
    'event manager',
    'event_manager'
], true);

// Organization members
$memberSql = "
    SELECT
        s.student_id,
        s.student_name,
        m.role,
        m.membership_date
    FROM membership m
    INNER JOIN student s
        ON m.student_id = s.student_id
    WHERE m.organization_id = :organization_id
    ORDER BY
        CASE
            WHEN LOWER(m.role) = 'admin' THEN 1
            WHEN LOWER(m.role) IN ('event manager', 'event_manager') THEN 2
            ELSE 3
        END,
        s.student_name ASC
";

$memberStmt = $pdo->prepare($memberSql);
$memberStmt->execute([
    ':organization_id' => $organizationId
]);

$members = $memberStmt->fetchAll(PDO::FETCH_ASSOC);

// Organization events and schedules
$eventSql = "
    SELECT
        e.event_id,
        e.event_name,
        e.description,
        e.status,
        STRING_AGG(
            TO_CHAR(es.event_date, 'Mon DD, YYYY')
            || ' | ' ||
            TO_CHAR(es.start_time, 'HH12:MI AM')
            || ' - ' ||
            TO_CHAR(es.end_time, 'HH12:MI AM')
            || ' | ' ||
            COALESCE(v.venue_name, 'No venue'),
            E'\n'
            ORDER BY es.event_date, es.start_time
        ) AS schedules
    FROM event e
    LEFT JOIN event_schedule es
        ON e.event_id = es.event_id
    LEFT JOIN venue v
        ON es.venue_id = v.venue_id
    WHERE e.organization_id = :organization_id
    GROUP BY
        e.event_id,
        e.event_name,
        e.description,
        e.status
    ORDER BY e.event_id DESC
";

$eventStmt = $pdo->prepare($eventSql);
$eventStmt->execute([
    ':organization_id' => $organizationId
]);

$events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

?>
