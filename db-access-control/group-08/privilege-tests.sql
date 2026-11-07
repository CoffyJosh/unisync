-- Activity 4: Group 08 - Catering Business Management System
-- Database: catering_management_db
-- Read-only verification queries and actual Viewer access test.

-- 1. Verify role attributes.
SELECT rolname, rolcanlogin, rolsuper, rolcreatedb, rolcreaterole
FROM pg_roles
WHERE rolname LIKE 'catering_%'
ORDER BY rolname;

-- 2. Verify login-to-group-role memberships.
SELECT r.rolname AS login_role, m.rolname AS group_role
FROM pg_auth_members am
JOIN pg_roles r ON r.oid = am.member
JOIN pg_roles m ON m.oid = am.roleid
WHERE r.rolname LIKE 'catering_%'
ORDER BY r.rolname;

-- 3. Verify representative table privileges.
SELECT
    role_name,
    has_schema_privilege(role_name, 'public', 'USAGE') AS schema_usage,
    has_table_privilege(role_name, 'public.customer', 'SELECT') AS can_select,
    has_table_privilege(role_name, 'public.customer', 'INSERT') AS can_insert,
    has_table_privilege(role_name, 'public.customer', 'UPDATE') AS can_update,
    has_table_privilege(role_name, 'public.customer', 'DELETE') AS can_delete,
    has_table_privilege(role_name, 'public.customer', 'TRUNCATE') AS can_truncate
FROM (VALUES
    ('catering_admin_grp'),
    ('catering_runtime_grp'),
    ('catering_viewer_grp')
) AS r(role_name);

-- 4. Actual Viewer access test.
-- SELECT succeeded and returned 4 customer records.
SET ROLE catering_viewer;
SELECT COUNT(*) FROM public.customer;

-- INSERT was attempted and denied:
-- ERROR: permission denied for table customer
-- No data was inserted.

RESET ROLE;