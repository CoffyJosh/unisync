-- Group 07 – Applicant Management System
-- Activity 4: Privilege Verification
-- Queries used to inspect existing table permissions.
-- No database permissions were modified.

-- Activity 4: Effective Table Privilege Inspection

SELECT
    r.rolname,
    has_table_privilege(
        r.rolname, 'public.applicant', 'SELECT'
    ) AS can_select,
    has_table_privilege(
        r.rolname, 'public.applicant', 'INSERT'
    ) AS can_insert,
    has_table_privilege(
        r.rolname, 'public.applicant', 'UPDATE'
    ) AS can_update,
    has_table_privilege(
        r.rolname, 'public.applicant', 'DELETE'
    ) AS can_delete
FROM pg_roles r
WHERE r.rolname IN (
    'app_admin_grp',
    'app_runtime_grp',
    'app_readonly_grp'
);