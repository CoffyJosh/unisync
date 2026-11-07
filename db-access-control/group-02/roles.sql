-- Group 02 - High School Event Management System
-- Activity 4: Database Role Verification

-- Check project database roles and attributes.
SELECT rolname, rolsuper, rolcreatedb, rolcreaterole, rolcanlogin
FROM pg_roles
WHERE rolname IN ('event_owner', 'event_app')
ORDER BY rolname;

-- Check schema ownership.
SELECT
    nspname AS schema_name,
    pg_get_userbyid(nspowner) AS schema_owner
FROM pg_namespace
WHERE nspname = 'school_event_management_db';

-- Check schema privileges.
SELECT
    has_schema_privilege('event_app', 'school_event_management_db', 'USAGE') AS can_use_schema,
    has_schema_privilege('event_app', 'school_event_management_db', 'CREATE') AS can_create_objects;
 | Set-Content "db-access-control\group-02\roles.sql"