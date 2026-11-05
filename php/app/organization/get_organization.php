
<?php

require_once __DIR__ . '/../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$accountId = $_SESSION['account_id'] ?? null;

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
        university_name,
        description,
        status
    FROM organization
    WHERE organization_id = :organization_id
    AND status = 'active'
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

// Current account's membership
$membership = null;
$role = null;

if ($accountId !== null) {
    $membershipSql = "
        SELECT
            role,
            membership_date
        FROM membership
        WHERE account_id = :account_id
            AND organization_id = :organization_id
            AND status = 'active'
    ";

    $membershipStmt = $pdo->prepare($membershipSql);
    $membershipStmt->execute([
        ':account_id' => $accountId,
        ':organization_id' => $organizationId
    ]);

    $membership = $membershipStmt->fetch(PDO::FETCH_ASSOC);

    if ($membership) {
        $role = trim($membership['role']);
    }
}

// Membership status
// Membership status
$isMember = $membership !== false && $membership !== null;

// Normalize role
$role = $membership
    ? ucfirst(strtolower(trim($membership['role'])))
    : null;

// Permissions
$isAdmin = $role === 'Admin';

$canManageEvents = in_array($role, [
    'Admin',
    'Manager'
], true);

// Organization members
$memberSql = "
    SELECT
        m.membership_id,
        a.account_id,
        a.account_name,
        m.role,
        m.membership_date
    FROM membership m
    INNER JOIN account a
        ON m.account_id = a.account_id
    WHERE m.organization_id = :organization_id
        AND m.status = 'active'
        AND a.account_status = 'active'
    ORDER BY
        CASE
            WHEN m.role = 'Admin' THEN 1
            WHEN m.role = 'Manager' THEN 2
            ELSE 3
        END,
        a.account_name ASC
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
        ) FILTER (
            WHERE es.status IN ('pending', 'confirmed', 'completed')
            AND v.status = 'active'
        ) AS schedules
    FROM event e
    LEFT JOIN event_schedule es
        ON e.event_id = es.event_id
    LEFT JOIN venue v
        ON es.venue_id = v.venue_id
    WHERE e.organization_id = :organization_id
      AND e.status <> 'deleted'
      AND EXISTS (
          SELECT 1
          FROM organization o
          WHERE o.organization_id = e.organization_id
            AND o.status = 'active'
      )
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
