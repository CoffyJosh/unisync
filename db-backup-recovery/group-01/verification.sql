-- Group 01: DropBoxed
-- Activity 6: Restoration Verification

-- Verify restored tables
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'dropboxed'
AND table_type = 'BASE TABLE'
ORDER BY table_name;

-- Verify restored record counts
SELECT 'users', COUNT(*) FROM dropboxed.users
UNION ALL SELECT 'staff', COUNT(*) FROM dropboxed.staff
UNION ALL SELECT 'complaints', COUNT(*) FROM dropboxed.complaints
UNION ALL SELECT 'locations', COUNT(*) FROM dropboxed.locations
UNION ALL SELECT 'attachments', COUNT(*) FROM dropboxed.attachments
UNION ALL SELECT 'categories', COUNT(*) FROM dropboxed.complaint_categories
UNION ALL SELECT 'assignments', COUNT(*) FROM dropboxed.complaint_assignments
UNION ALL SELECT 'status_logs', COUNT(*) FROM dropboxed.status_logs;

-- Verify constraints
SELECT COUNT(*)
FROM information_schema.table_constraints
WHERE constraint_schema = 'dropboxed'
AND constraint_type IN ('PRIMARY KEY', 'FOREIGN KEY', 'UNIQUE', 'CHECK');

-- Verify DropBoxed roles
SELECT rolname
FROM pg_roles
WHERE rolname LIKE 'dropboxed_%'
ORDER BY rolname;

-- Verify restored group privileges
SELECT grantee, COUNT(*) AS privileges
FROM information_schema.role_table_grants
WHERE table_schema = 'dropboxed'
AND grantee IN (
    'dropboxed_runtime_grp',
    'dropboxed_readonly_grp',
    'dropboxed_dba_grp',
    'dropboxed_backup_grp'
)
GROUP BY grantee
ORDER BY grantee;