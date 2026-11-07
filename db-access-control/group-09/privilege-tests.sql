-- Activity 4: Privilege Verification
-- Project: Batumbakal Gym Membership Management System
-- Database: grp99_gymdb

-- Check role memberships
SELECT
    pg_has_role('gym_admin', 'gym_admin_grp', 'MEMBER')
        AS admin_membership,
    pg_has_role('gym_app', 'gym_runtime_grp', 'MEMBER')
        AS runtime_membership,
    pg_has_role('gym_viewer', 'gym_readonly_grp', 'MEMBER')
        AS readonly_membership;

-- Check viewer privileges
SELECT
    has_table_privilege('gym_viewer', 'public.members', 'SELECT')
        AS viewer_select,
    has_table_privilege('gym_viewer', 'public.members', 'INSERT')
        AS viewer_insert,
    has_table_privilege('gym_viewer', 'public.members', 'UPDATE')
        AS viewer_update,
    has_table_privilege('gym_viewer', 'public.members', 'DELETE')
        AS viewer_delete;

-- Check runtime privileges
SELECT
    has_table_privilege('gym_app', 'public.members', 'SELECT')
        AS runtime_select,
    has_table_privilege('gym_app', 'public.members', 'INSERT')
        AS runtime_insert,
    has_table_privilege('gym_app', 'public.members', 'UPDATE')
        AS runtime_update,
    has_table_privilege('gym_app', 'public.members', 'DELETE')
        AS runtime_delete;

-- Check runtime sequence privileges
SELECT
    has_sequence_privilege(
        'gym_app',
        'public.members_member_id_seq',
        'USAGE'
    ) AS runtime_sequence_usage;

-- Check admin privileges
SELECT
    has_table_privilege('gym_admin', 'public.members', 'SELECT')
        AS admin_select,
    has_table_privilege('gym_admin', 'public.members', 'INSERT')
        AS admin_insert,
    has_table_privilege('gym_admin', 'public.members', 'UPDATE')
        AS admin_update,
    has_table_privilege('gym_admin', 'public.members', 'DELETE')
        AS admin_delete;

-- Expected:
-- All role memberships: TRUE
-- Viewer: SELECT TRUE, INSERT/UPDATE/DELETE FALSE
-- Runtime: SELECT/INSERT/UPDATE/DELETE TRUE
-- Admin: SELECT/INSERT/UPDATE/DELETE TRUE