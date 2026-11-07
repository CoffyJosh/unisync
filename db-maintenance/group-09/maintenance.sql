-- Activity 5: Database Maintenance
-- Database: grp99_gymdb

VACUUM (ANALYZE);

-- Verify maintenance results
SELECT
    relname AS table_name,
    n_live_tup AS live_rows,
    n_dead_tup AS dead_rows,
    last_vacuum,
    last_analyze
FROM pg_stat_user_tables
ORDER BY relname;