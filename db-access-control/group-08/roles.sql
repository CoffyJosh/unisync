-- Activity 4: Group 08 - Catering Business Management System
-- Database: catering_management_db
-- Schema: public
-- These statements record the role setup performed during verification.
-- Already executed. Do not rerun against the existing roles.

CREATE ROLE catering_admin_grp NOLOGIN;
CREATE ROLE catering_runtime_grp NOLOGIN;
CREATE ROLE catering_viewer_grp NOLOGIN;

CREATE ROLE catering_admin LOGIN;
CREATE ROLE catering_runtime LOGIN;
CREATE ROLE catering_viewer LOGIN;

GRANT catering_admin_grp TO catering_admin;
GRANT catering_runtime_grp TO catering_runtime;
GRANT catering_viewer_grp TO catering_viewer;

GRANT CONNECT ON DATABASE catering_management_db
TO catering_admin_grp, catering_runtime_grp, catering_viewer_grp;

GRANT USAGE ON SCHEMA public
TO catering_admin_grp, catering_runtime_grp, catering_viewer_grp;

GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public
TO catering_admin_grp;

GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public
TO catering_admin_grp;

GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public
TO catering_runtime_grp;

GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA public
TO catering_runtime_grp;

GRANT SELECT ON ALL TABLES IN SCHEMA public
TO catering_viewer_grp;

GRANT SELECT ON ALL SEQUENCES IN SCHEMA public
TO catering_viewer_grp;