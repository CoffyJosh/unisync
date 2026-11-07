-- Group 02 - High School Event Management System
-- Activity 4: Database Privilege Verification

-- Check granted table privileges.
SELECT table_name, grantee, privilege_type
FROM information_schema.role_table_grants
WHERE table_schema = 'school_event_management_db'
  AND grantee IN ('event_owner', 'event_app')
ORDER BY table_name, grantee, privilege_type;

-- Verify audit_log restrictions.
SELECT
    has_table_privilege('event_app', 'school_event_management_db.audit_log', 'SELECT') AS can_read_audit,
    has_table_privilege('event_app', 'school_event_management_db.audit_log', 'INSERT') AS can_append_audit,
    has_table_privilege('event_app', 'school_event_management_db.audit_log', 'UPDATE') AS can_update_audit,
    has_table_privilege('event_app', 'school_event_management_db.audit_log', 'DELETE') AS can_delete_audit;

-- Verify role table restrictions.
SELECT
    has_table_privilege('event_app', 'school_event_management_db.role', 'SELECT') AS can_read_roles,
    has_table_privilege('event_app', 'school_event_management_db.role', 'INSERT') AS can_insert_roles,
    has_table_privilege('event_app', 'school_event_management_db.role', 'UPDATE') AS can_update_roles,
    has_table_privilege('event_app', 'school_event_management_db.role', 'DELETE') AS can_delete_roles;
 | Set-Content "db-access-control\group-02\privilege-tests.sql"