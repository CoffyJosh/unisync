
-- Group 11 - ReserveIT
-- Activity 4: Role Configuration Verification
-- Reference: database/roles_setup.sql
--
-- These are verification queries for the existing role setup.
-- No roles or permissions are modified by this file.

-- 1. Verify login and group role attributes.

SELECT
    rolname,
    rolcanlogin,
    rolsuper,
    rolcreatedb,
    rolcreaterole
FROM pg_roles
WHERE rolname IN (
    'reserveit_owner',
    'reserveit_app',
    'reserveit_reporter',
    'reserveit_dba',
    'app_runtime_grp',
    'app_readonly_grp',
    'app_admin_grp'
)
ORDER BY rolname;


-- 2. Verify login account memberships.

SELECT
    member.rolname AS login_account,
    granted.rolname AS group_role
FROM pg_auth_members AS m
JOIN pg_roles AS member
    ON member.oid = m.member
JOIN pg_roles AS granted
    ON granted.oid = m.roleid
WHERE member.rolname LIKE 'reserveit_%'
ORDER BY member.rolname;


-- 3. Verify that the runtime account cannot create databases.

SELECT
    rolname,
    rolcreatedb
FROM pg_roles
WHERE rolname = 'reserveit_app';
