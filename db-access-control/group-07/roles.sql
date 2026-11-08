-- Group 07 – Applicant Management System
-- Activity 4: Database Role Inspection
-- Executed against grp07_applicantdb.
-- No roles or permissions were created or modified.

-- Activity 4: Existing Database Role Inspection

SELECT rolname, rolcanlogin
FROM pg_roles
WHERE rolname IN (
    'app_admin_grp',
    'app_runtime_grp',
    'app_readonly_grp'
);

-- Inspect role memberships
SELECT
    r.rolname AS group_role,
    m.rolname AS member_role
FROM pg_auth_members am
JOIN pg_roles r ON r.oid = am.roleid
JOIN pg_roles m ON m.oid = am.member
WHERE r.rolname IN (
    'app_admin_grp',
    'app_runtime_grp',
    'app_readonly_grp'
);

-- Inspect database ownership and privileges
SELECT
    datname,
    pg_get_userbyid(datdba) AS owner,
    datacl
FROM pg_database
WHERE datname = current_database();

-- Inspect public schema ownership and privileges
SELECT
    nspname,
    pg_get_userbyid(nspowner) AS owner,
    nspacl
FROM pg_namespace
WHERE nspname = 'public';