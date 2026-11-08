
-- Group: 07
-- Project: Applicant Management System
-- Activity: 6 - Database Backup, Restoration, and Recovery
-- Purpose: Verify restored database objects, records, constraints,
--          existing roles, and recovery simulation results.
-- Changes: Read-only verification queries. No original database changes.

-- 1. List restored application tables.
SELECT tablename
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY tablename;

-- 2. Check representative restored record counts.
SELECT 'applicant' AS table_name, COUNT(*) AS record_count
FROM applicant
UNION ALL
SELECT 'application', COUNT(*)
FROM application
UNION ALL
SELECT 'users', COUNT(*)
FROM users;

-- 3. Count restored constraints.
SELECT COUNT(*) AS public_constraints
FROM pg_constraint
WHERE connamespace = 'public'::regnamespace;

-- 4. Check existing Activity 4 group roles.
SELECT rolname, rolinherit, rolcanlogin
FROM pg_roles
WHERE rolname IN (
    'app_runtime_grp',
    'app_admin_grp',
    'app_readonly_grp'
);

-- 5. Check effective schema and table privileges.
SELECT
    r.rolname,
    has_schema_privilege(r.rolname, 'public', 'USAGE') AS schema_usage,
    has_table_privilege(
        r.rolname, 'public.applicant', 'SELECT'
    ) AS can_select_applicant,
    has_table_privilege(
        r.rolname, 'public.applicant', 'INSERT'
    ) AS can_insert_applicant
FROM pg_roles r
WHERE r.rolname IN (
    'app_runtime_grp',
    'app_admin_grp',
    'app_readonly_grp'
);

-- 6. Check the recovery record in the fresh recovery database.
-- Run this query while connected to grp07_applicantdb_recovery_test.
SELECT *
FROM activity6_recovery_test
WHERE test_id = 1;
