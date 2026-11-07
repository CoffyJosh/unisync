
-- Group 05: SHIORI Café
-- Activity 4: Database Access Control
-- Inspection of existing roles and memberships.
-- Executed against grp10_shioricafedb.
-- No roles or permissions were created or modified.

-- Inspect application role attributes
SELECT
    rolname,
    rolsuper,
    rolcreatedb,
    rolcreaterole,
    rolcanlogin
FROM pg_roles
WHERE rolname IN (
    'app_admin_grp',
    'app_readonly_grp',
    'app_runtime_grp',
    'app_runtime'
)
ORDER BY rolname;

-- Inspect role memberships
SELECT
    member.rolname AS member,
    granted.rolname AS granted_role
FROM pg_auth_members m
JOIN pg_roles member ON member.oid = m.member
JOIN pg_roles granted ON granted.oid = m.roleid
WHERE member.rolname IN (
    'app_admin_grp',
    'app_readonly_grp',
    'app_runtime_grp',
    'app_runtime'
)
OR granted.rolname IN (
    'app_admin_grp',
    'app_readonly_grp',
    'app_runtime_grp',
    'app_runtime'
)
ORDER BY member.rolname;
