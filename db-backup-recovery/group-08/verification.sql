-- Group 08: Catering Business Management System
-- Activity 6: Backup and Recovery Verification
-- Restored database: catering_recovery_test
-- PostgreSQL 18.6

-- 1. Confirm restored database
SELECT
    current_database() AS database_name,
    pg_size_pretty(pg_database_size(current_database())) AS database_size,
    version();

-- 2. List restored tables
SELECT
    table_name
FROM information_schema.tables
WHERE table_schema = 'public'
  AND table_type = 'BASE TABLE'
ORDER BY table_name;

-- 3. Verify restored record counts
SELECT 'admin' AS table_name, COUNT(*) FROM admin
UNION ALL SELECT 'cancellation', COUNT(*) FROM cancellation
UNION ALL SELECT 'customer', COUNT(*) FROM customer
UNION ALL SELECT 'employee', COUNT(*) FROM employee
UNION ALL SELECT 'event_assignment', COUNT(*) FROM event_assignment
UNION ALL SELECT 'inventory', COUNT(*) FROM inventory
UNION ALL SELECT 'menu_item', COUNT(*) FROM menu_item
UNION ALL SELECT 'package', COUNT(*) FROM package
UNION ALL SELECT 'payment', COUNT(*) FROM payment
UNION ALL SELECT 'reservation', COUNT(*) FROM reservation
UNION ALL SELECT 'reservation_menu', COUNT(*) FROM reservation_menu
UNION ALL SELECT 'session', COUNT(*) FROM session
ORDER BY table_name;

-- 4. Verify restored constraints
SELECT
    contype,
    CASE contype
        WHEN 'p' THEN 'Primary Key'
        WHEN 'f' THEN 'Foreign Key'
        WHEN 'u' THEN 'Unique'
        WHEN 'c' THEN 'Check'
        WHEN 'n' THEN 'Not Null'
        ELSE contype::text
    END AS constraint_type,
    COUNT(*) AS total
FROM pg_constraint
WHERE connamespace = 'public'::regnamespace
GROUP BY contype
ORDER BY contype;

-- 5. Inspect restored indexes
SELECT
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY tablename, indexname;

-- Expected verification:
-- 12 tables restored.
-- Restored row counts match the original database.
-- 81 constraints verified.
-- Recovery database is accessible.