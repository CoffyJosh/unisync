-- Group 08: Catering Business Management System
-- Activity 5: Database Maintenance
-- Database: catering_management_db
-- PostgreSQL 18.6

-- 1. Check database size
SELECT
    current_database() AS database,
    pg_size_pretty(pg_database_size(current_database())) AS database_size;

-- 2. Inspect table sizes and estimated row counts
SELECT
    schemaname,
    relname AS table_name,
    pg_size_pretty(
        pg_total_relation_size(schemaname || '.' || relname)
    ) AS total_size,
    n_live_tup AS estimated_rows
FROM pg_stat_user_tables
ORDER BY pg_total_relation_size(schemaname || '.' || relname) DESC;

-- 3. Inspect dead tuples and maintenance history
SELECT
    relname AS table_name,
    n_live_tup AS live_rows,
    n_dead_tup AS dead_rows,
    last_vacuum,
    last_autovacuum,
    last_analyze,
    last_autoanalyze
FROM pg_stat_user_tables
ORDER BY relname;

-- 4. Perform database maintenance
VACUUM (ANALYZE);

-- 5. Verify maintenance results
SELECT
    relname AS table_name,
    n_live_tup AS live_rows,
    n_dead_tup AS dead_rows,
    last_vacuum,
    last_analyze
FROM pg_stat_user_tables
ORDER BY relname;

-- 6. Inspect existing indexes
SELECT
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY tablename, indexname;

-- 7. Check active database connections
SELECT
    state,
    COUNT(*) AS connections
FROM pg_stat_activity
WHERE datname = current_database()
GROUP BY state
ORDER BY state;