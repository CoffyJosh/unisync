-- Activity 6: Backup and Recovery Verification
-- Database: grp99_gymdb_a6_restore

-- Verify restored database
SELECT current_database();

-- Verify restored table count
SELECT COUNT(*) AS restored_table_count
FROM information_schema.tables
WHERE table_schema = 'public'
  AND table_type = 'BASE TABLE';

-- Verify restored row counts
SELECT 'attendance' AS table_name, COUNT(*) AS row_count FROM attendance
UNION ALL
SELECT 'class_enrollments', COUNT(*) FROM class_enrollments
UNION ALL
SELECT 'classes', COUNT(*) FROM classes
UNION ALL
SELECT 'members', COUNT(*) FROM members
UNION ALL
SELECT 'membership_plans', COUNT(*) FROM membership_plans
UNION ALL
SELECT 'memberships', COUNT(*) FROM memberships
UNION ALL
SELECT 'payments', COUNT(*) FROM payments
UNION ALL
SELECT 'trainers', COUNT(*) FROM trainers
ORDER BY table_name;