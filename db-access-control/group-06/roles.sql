-- Group 06: ByaheRentals
-- Activity 4: Database Role Inspection
-- Executed against car_rental_db.
-- No roles or permissions were created or modified.

-- List existing PostgreSQL roles.
\du


-- Inspect login capability and role information.
SELECT
    r.rolname AS role_name,
    r.rolcanlogin AS can_login
FROM pg_roles r
WHERE r.rolname IN (
    'app_runtime',
    'app_runtime_grp',
    'app_readonly_grp',
    'app_admin_grp'
)
ORDER BY r.rolname;


-- Check actual role memberships.
SELECT
    member::regrole AS member,
    roleid::regrole AS granted_role
FROM pg_auth_members
WHERE member IN (
    'app_runtime'::regrole,
    'app_admin_grp'::regrole,
    'app_readonly_grp'::regrole
)
ORDER BY member;

-- Observed memberships:
-- app_runtime belongs to app_runtime_grp.
-- app_admin_grp belongs to app_runtime_grp.
-- No membership returned for app_readonly_grp.