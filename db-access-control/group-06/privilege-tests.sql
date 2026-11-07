-- Group 06: ByaheRentals
-- Activity 4: Privilege Verification
-- Queries used to inspect existing table permissions.
-- No database permissions were modified.

-- Check runtime and read-only permissions on customer.
SELECT
    'app_runtime_grp' AS role,
    has_table_privilege('app_runtime_grp', 'public.customer', 'SELECT') AS can_select,
    has_table_privilege('app_runtime_grp', 'public.customer', 'INSERT') AS can_insert,
    has_table_privilege('app_runtime_grp', 'public.customer', 'UPDATE') AS can_update,
    has_table_privilege('app_runtime_grp', 'public.customer', 'DELETE') AS can_delete

UNION ALL

SELECT
    'app_readonly_grp',
    has_table_privilege('app_readonly_grp', 'public.customer', 'SELECT'),
    has_table_privilege('app_readonly_grp', 'public.customer', 'INSERT'),
    has_table_privilege('app_readonly_grp', 'public.customer', 'UPDATE'),
    has_table_privilege('app_readonly_grp', 'public.customer', 'DELETE');

-- Observed results:
-- app_runtime_grp: SELECT, INSERT, UPDATE allowed; DELETE denied.
-- app_readonly_grp: All four privileges denied on customer.


-- Inspect table-level privileges for all three group roles.
SELECT
    grantee,
    table_schema,
    table_name,
    privilege_type
FROM information_schema.role_table_grants
WHERE grantee IN (
    'app_runtime_grp',
    'app_readonly_grp',
    'app_admin_grp'
)
ORDER BY
    grantee,
    table_name,
    privilege_type;

-- Observed result:
-- 185 rows returned.
-- app_admin_grp: Full table privileges.
-- app_readonly_grp: SELECT privileges on 15 tables.
-- app_runtime_grp: Limited application privileges,
-- with DELETE explicitly granted on vehicle.