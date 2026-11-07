
-- Group 05: SHIORI Café
-- Activity 5: Database Maintenance
-- Database: grp10_shioricafedb
-- Schema: shioricafe_schema
-- Account used: postgres
-- Note: app_runtime lacks schema USAGE and MAINTAIN privileges.
-- Maintenance was executed using the postgres administrative account.

-- Refresh query planner statistics.
ANALYZE VERBOSE shioricafe_schema.reservation;

-- Routine maintenance and statistics refresh.
-- VACUUM FULL was not used.
VACUUM (VERBOSE, ANALYZE) shioricafe_schema.reservation;
