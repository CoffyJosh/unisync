-- Activity 5: Query Performance Analysis
-- Database: grp99_gymdb
-- Purpose: Retrieve active gym memberships with member and plan details.

EXPLAIN ANALYZE
SELECT
    m.member_id,
    m.first_name,
    m.last_name,
    mp.plan_name,
    ms.start_date,
    ms.end_date,
    ms.status
FROM members m
JOIN memberships ms
    ON m.member_id = ms.member_id
JOIN membership_plans mp
    ON ms.plan_id = mp.plan_id
WHERE ms.status = 'Active'
ORDER BY ms.start_date DESC;