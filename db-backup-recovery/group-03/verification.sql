-- Group 03: Pawpaw Clinic
-- Activity 6: Backup, Restoration, and Recovery

-- 1. Check restored tables
\dt

-- 2. Verify selected record counts
SELECT 'user_account' AS table_name, COUNT(*) AS records FROM user_account
UNION ALL
SELECT 'pet', COUNT(*) FROM pet
UNION ALL
SELECT 'appointment', COUNT(*) FROM appointment
UNION ALL
SELECT 'service', COUNT(*) FROM service;

-- 3. Verify constraints
SELECT COUNT(*) AS constraints
FROM information_schema.table_constraints
WHERE constraint_schema = 'public';

-- 4. Verify Activity 4 privileges
SELECT
    has_table_privilege('pawpaw_app', 'public.user_account', 'SELECT') AS can_select,
    has_table_privilege('pawpaw_app', 'public.user_account', 'TRUNCATE') AS can_truncate;

-- 5. Representative query
SELECT service_id, service_name
FROM service
LIMIT 5;

-- 6. Verify controlled recovery
SELECT *
FROM activity6_recovery_test;