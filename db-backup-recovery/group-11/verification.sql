-- ReserveIT Activity 6: Backup and Recovery Verification
-- Run against reserveit_recovery_test as a database administrator.

-- 1. Confirm database and PostgreSQL version.
SELECT current_database(), version();

-- 2. Verify restored row counts.
SELECT 'app_user' AS table_name, COUNT(*) AS row_count FROM public.app_user
UNION ALL
SELECT 'department', COUNT(*) FROM public.department
UNION ALL
SELECT 'material', COUNT(*) FROM public.material
UNION ALL
SELECT 'penalty', COUNT(*) FROM public.penalty
UNION ALL
SELECT 'reservation', COUNT(*) FROM public.reservation
UNION ALL
SELECT 'reservation_material', COUNT(*) FROM public.reservation_material
UNION ALL
SELECT 'room', COUNT(*) FROM public.room
UNION ALL
SELECT 'staff', COUNT(*) FROM public.staff
ORDER BY table_name;

-- 3. Verify constraint counts.
SELECT
    COUNT(*) FILTER (WHERE contype = 'p') AS primary_keys,
    COUNT(*) FILTER (WHERE contype = 'f') AS foreign_keys,
    COUNT(*) FILTER (WHERE contype = 'u') AS unique_constraints,
    COUNT(*) FILTER (WHERE contype = 'c') AS check_constraints
FROM pg_constraint
WHERE connamespace = 'public'::regnamespace;

-- 4. Verify indexes.
SELECT tablename, indexname
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY tablename, indexname;

-- 5. Verify restored table grants.
SELECT grantee, table_name, privilege_type
FROM information_schema.role_table_grants
WHERE table_schema = 'public'
ORDER BY grantee, table_name, privilege_type;

-- 6. Verify login-role group memberships.
SELECT
    member.rolname AS login_role,
    parent.rolname AS granted_group
FROM pg_auth_members m
JOIN pg_roles parent ON parent.oid = m.roleid
JOIN pg_roles member ON member.oid = m.member
WHERE parent.rolname IN (
    'app_runtime_grp',
    'app_readonly_grp',
    'app_admin_grp'
)
ORDER BY member.rolname;

-- 7. Check effective table-level privileges.
SELECT
    has_table_privilege(
        'reserveit_app',
        'public.reservation',
        'SELECT'
    ) AS app_can_select_reservation,
    has_table_privilege(
        'reserveit_app',
        'public.room',
        'UPDATE'
    ) AS app_can_update_room_table;

-- 8. Check column-level UPDATE grants on room.
SELECT
    grantee,
    column_name,
    privilege_type
FROM information_schema.column_privileges
WHERE table_schema = 'public'
  AND table_name = 'room'
  AND privilege_type = 'UPDATE'
ORDER BY grantee, column_name;