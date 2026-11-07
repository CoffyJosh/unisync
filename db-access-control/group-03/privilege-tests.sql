
-- Group 03: Pawpaw Clinic
-- Activity 4: Privilege Verification
-- Results below are based on tests actually performed.

-- Check schema and table permissions.
SELECT
    has_schema_privilege('pawpaw_app', 'public', 'USAGE') AS schema_access,
    has_table_privilege('pawpaw_app', 'public.user_account', 'SELECT') AS can_read_users,
    has_table_privilege('pawpaw_app', 'public.user_account', 'TRUNCATE') AS can_truncate;

-- Expected:
-- schema_access = true
-- can_read_users = true
-- can_truncate = false

-- Restricted CREATE TABLE test:
-- SET ROLE pawpaw_app;
-- CREATE TABLE public.activity4_test (id INT);
-- Expected: permission denied for schema public.
-- RESET ROLE;

-- Login verification:
-- psql -U pawpaw_app -d group3_pawpaw -h localhost
-- SELECT current_user, current_database();
-- Expected: pawpaw_app | group3_pawpaw
