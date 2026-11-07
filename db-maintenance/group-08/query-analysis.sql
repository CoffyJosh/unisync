-- Group 08: Catering Business Management System
-- Activity 5: Query Analysis
-- Database: catering_management_db
-- PostgreSQL 18.6

-- 1. Inspect columns used by the reservation query
SELECT
    table_name,
    column_name,
    data_type
FROM information_schema.columns
WHERE table_schema = 'public'
  AND table_name IN ('reservation', 'customer', 'package')
ORDER BY table_name, ordinal_position;

-- 2. Analyze reservation query execution
EXPLAIN ANALYZE
SELECT
    r.reservation_id,
    c.first_name,
    c.last_name,
    p.package_name,
    r.event_date,
    r.status,
    r.estimated_cost
FROM reservation r
JOIN customer c
    ON r.customer_id = c.customer_id
JOIN package p
    ON r.package_id = p.package_id
ORDER BY r.event_date;

-- 3. Inspect existing indexes
SELECT
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY tablename, indexname;