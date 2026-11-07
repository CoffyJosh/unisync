-- Group 04: HealthBridge Clinic Management System
-- Activity 4: Database Access Control
-- Inspection of existing roles and memberships.
-- Executed against grp04_projectdb.
-- No roles or permissions were created or modified.

-- List existing PostgreSQL roles.
\du

-- Check membership in HealthBridge application roles.
SELECT
    member.rolname AS login_role,
    granted.rolname AS inherited_role
FROM pg_auth_members m
JOIN pg_roles granted ON granted.oid = m.roleid
JOIN pg_roles member ON member.oid = m.member
WHERE granted.rolname IN ('hb_admin', 'hb_staff');

-- Check database-level privileges.
SELECT
    datname,
    has_database_privilege('hb_app', datname, 'CONNECT') AS hb_app_connect,
    has_database_privilege('hb_app', datname, 'CREATE') AS hb_app_create
FROM pg_database
WHERE datname = 'grp04_projectdb';