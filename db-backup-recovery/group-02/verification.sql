
-- Group 02 - Backup and Recovery Verification
-- Run against grp02_event_management_restore_test

-- Verify restored tables
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'school_event_management_db'
  AND table_type = 'BASE TABLE'
ORDER BY table_name;

-- Verify restored row counts
SELECT 'attendance' AS table_name, COUNT(*) FROM school_event_management_db.attendance
UNION ALL SELECT 'audit_log', COUNT(*) FROM school_event_management_db.audit_log
UNION ALL SELECT 'event', COUNT(*) FROM school_event_management_db.event
UNION ALL SELECT 'event_assignment', COUNT(*) FROM school_event_management_db.event_assignment
UNION ALL SELECT 'event_schedule', COUNT(*) FROM school_event_management_db.event_schedule
UNION ALL SELECT 'flyway_schema_history', COUNT(*) FROM school_event_management_db.flyway_schema_history
UNION ALL SELECT 'participant', COUNT(*) FROM school_event_management_db.participant
UNION ALL SELECT 'role', COUNT(*) FROM school_event_management_db.role
UNION ALL SELECT 'user_account', COUNT(*) FROM school_event_management_db.user_account
UNION ALL SELECT 'venue', COUNT(*) FROM school_event_management_db.venue
ORDER BY table_name;
