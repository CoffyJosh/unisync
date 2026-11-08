-- Group 11 - ReserveIT
-- Activity 5: Database Maintenance

-- Inspect database health
SELECT
    current_database() AS database_name,
    version() AS postgresql_version,
    pg_size_pretty(pg_database_size(current_database())) AS database_size;

-- Inspect table statistics
SELECT
    schemaname,
    relname AS table_name,
    n_live_tup AS estimated_rows,
    pg_size_pretty(
        pg_total_relation_size(schemaname || '.' || relname)
    ) AS total_size
FROM pg_stat_user_tables
ORDER BY pg_total_relation_size(schemaname || '.' || relname) DESC;

-- Inspect database activity
SELECT
    datname,
    numbackends,
    xact_commit,
    xact_rollback,
    deadlocks,
    conflicts
FROM pg_stat_database
WHERE datname = current_database();

-- Check maintenance statistics before maintenance
SELECT
    relname AS table_name,
    last_vacuum,
    last_autovacuum,
    last_analyze,
    last_autoanalyze,
    n_dead_tup
FROM pg_stat_user_tables
ORDER BY relname;

-- Maintenance was performed using reserveit_owner
ANALYZE;
VACUUM (VERBOSE);

-- Verify maintenance timestamps and dead tuple estimates
SELECT
    relname AS table_name,
    last_vacuum,
    last_analyze,
    n_dead_tup
FROM pg_stat_user_tables
ORDER BY relname;