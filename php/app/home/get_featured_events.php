
<?php
require_once __DIR__ . '/../../db.php';

// Retrieve up to six upcoming confirmed events.
$sql = "
    SELECT
        e.event_id,
        e.event_name,
        e.description,
        e.status,
        o.organization_name,
        v.venue_name,
        v.location AS venue_location,
        es.event_date,
        es.start_time,
        es.end_time
    FROM event e
    INNER JOIN organization o
        ON e.organization_id = o.organization_id
    INNER JOIN event_schedule es
        ON e.event_id = es.event_id
    INNER JOIN venue v
        ON es.venue_id = v.venue_id
    WHERE e.status = 'confirmed'
      AND o.status = 'active'
      AND es.event_date >= CURRENT_DATE
    ORDER BY es.event_date ASC, es.start_time ASC
    LIMIT 6
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format event date and time.
function formatEventDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatEventTime($start, $end) {
    return date('g:i A', strtotime($start))
        . ' - ' .
        date('g:i A', strtotime($end));
}
