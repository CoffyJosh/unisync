-- Group 01: DropBoxed
-- Activity 4: Existing role configuration inspection

-- 1. Check login and elevated role attributes
SELECT
    rolname,
    rolcanlogin,
    rolsuper,
    rolcreatedb,
    rolcreaterole
FROM pg_roles
WHERE rolname LIKE 'dropboxed%'
ORDER BY rolname;

-- 2. Check role memberships
SELECT
    parent.rolname AS granted_role,
    member.rolname AS member_role
FROM pg_auth_members m
JOIN pg_roles parent ON parent.oid = m.roleid
JOIN pg_roles member ON member.oid = m.member
WHERE parent.rolname LIKE 'dropboxed%'
ORDER BY parent.rolname, member.rolname;

-- 3. Check database access
SELECT
    datname,
    datacl
FROM pg_database
WHERE datname = 'dropboxed_db';