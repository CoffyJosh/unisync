
-- Group 03: Pawpaw Clinic
-- Activity 4: Database Access Control
-- Executed on group3_pawpaw as postgres.

CREATE ROLE app_runtime_grp NOLOGIN;
CREATE ROLE app_admin_grp NOLOGIN;
CREATE ROLE pawpaw_app LOGIN;

-- Restore PUBLIC privileges after the initial revocation.
GRANT CONNECT, TEMPORARY ON DATABASE group3_pawpaw TO PUBLIC;

-- Runtime account
GRANT CONNECT ON DATABASE group3_pawpaw TO pawpaw_app;
GRANT app_runtime_grp TO pawpaw_app;

-- Schema and table permissions
GRANT USAGE ON SCHEMA public TO app_runtime_grp;

GRANT SELECT, INSERT, UPDATE, DELETE
ON ALL TABLES IN SCHEMA public TO app_runtime_grp;

-- Sequence permissions
GRANT USAGE, SELECT
ON ALL SEQUENCES IN SCHEMA public TO app_runtime_grp;

-- Default privileges for future objects created by postgres
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public
GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO app_runtime_grp;

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public
GRANT USAGE, SELECT ON SEQUENCES TO app_runtime_grp;

-- app_admin_grp intentionally has no assigned privileges.
