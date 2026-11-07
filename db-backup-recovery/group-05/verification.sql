-- COMP 018 Activity 6
-- Group 05 - SHIORI Café
-- Read-only verification queries
-- Run against grp10_shioricafedb_restore_test unless noted otherwise.

-- 1. Verify restored tables and views
SELECT table_type, COUNT(*) AS total
FROM information_schema.tables
WHERE table_schema = 'shioricafe_schema'
GROUP BY table_type
ORDER BY table_type;

-- 2. Verify representative restored record counts
SELECT 'customer' AS table_name, COUNT(*) AS total
FROM shioricafe_schema.customer
UNION ALL
SELECT 'menu_item', COUNT(*)
FROM shioricafe_schema.menu_item
UNION ALL
SELECT 'reservation', COUNT(*)
FROM shioricafe_schema.reservation;

-- 3. Verify restored constraints
SELECT
    contype,
    COUNT(*) AS total
FROM pg_constraint c
JOIN pg_class t ON t.oid = c.conrelid
JOIN pg_namespace n ON n.oid = t.relnamespace
WHERE n.nspname = 'shioricafe_schema'
GROUP BY contype
ORDER BY contype;

-- 4. Verify existing application roles
SELECT
    rolname,
    rolcanlogin,
    rolsuper,
    rolcreatedb,
    rolcreaterole
FROM pg_roles
WHERE rolname IN (
    'app_runtime',
    'app_runtime_grp',
    'app_admin_grp',
    'app_readonly_grp'
)
ORDER BY rolname;

-- 5. Verify role memberships
SELECT
    parent.rolname AS granted_role,
    member.rolname AS member_role
FROM pg_auth_members m
JOIN pg_roles parent ON parent.oid = m.roleid
JOIN pg_roles member ON member.oid = m.member
WHERE parent.rolname IN (
    'app_runtime_grp',
    'app_admin_grp',
    'app_readonly_grp'
)
OR member.rolname IN (
    'app_runtime',
    'app_runtime_grp',
    'app_admin_grp',
    'app_readonly_grp'
)
ORDER BY granted_role, member_role;

-- 6. Verify runtime database and schema privileges
SELECT
    current_database() AS database_name,
    has_database_privilege(
        'app_runtime',
        current_database(),
        'CONNECT'
    ) AS can_connect,
    has_schema_privilege(
        'app_runtime',
        'shioricafe_schema',
        'USAGE'
    ) AS schema_usage,
    has_schema_privilege(
        'app_runtime',
        'shioricafe_schema',
        'CREATE'
    ) AS schema_create;

-- 7. Representative restored reservation query
SELECT
    reservation_id,
    customer_id,
    party_size,
    status,
    reservation_start_at
FROM shioricafe_schema.reservation;

-- 8. Verify recovery marker
-- Run this section against grp10_shioricafedb_recovery_test.
SELECT
    category_id,
    category_name,
    is_drink_category
FROM shioricafe_schema.category
WHERE category_name = 'ACTIVITY6_RECOVERY_TEST';

-- Expected recovery result: category_id = 10
-- and category_name = ACTIVITY6_RECOVERY_TEST.

-- Note:
-- app_runtime database CONNECT was permitted.
-- app_runtime schema access was denied during the actual SET ROLE test.
-- No permissions or original database records were modified by this script.