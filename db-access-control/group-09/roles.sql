-- Activity 4: Database Access Control
-- Project: Batumbakal Gym Membership Management System
-- Database: grp99_gymdb

-- Create group roles
CREATE ROLE gym_admin_grp NOLOGIN;
CREATE ROLE gym_runtime_grp NOLOGIN;
CREATE ROLE gym_readonly_grp NOLOGIN;

-- Create login roles
-- Test credentials only. Change before production use.
CREATE ROLE gym_admin LOGIN PASSWORD 'gym_admin123';
CREATE ROLE gym_app LOGIN PASSWORD 'gym_app123';
CREATE ROLE gym_viewer LOGIN PASSWORD 'gym_viewer123';

-- Assign group memberships
GRANT gym_admin_grp TO gym_admin;
GRANT gym_runtime_grp TO gym_app;
GRANT gym_readonly_grp TO gym_viewer;

-- Database connection privileges
GRANT CONNECT ON DATABASE grp99_gymdb
TO gym_admin_grp, gym_runtime_grp, gym_readonly_grp;

-- Schema privileges
GRANT USAGE ON SCHEMA public
TO gym_admin_grp, gym_runtime_grp, gym_readonly_grp;

-- Table privileges
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public
TO gym_admin_grp;

GRANT SELECT, INSERT, UPDATE, DELETE
ON ALL TABLES IN SCHEMA public
TO gym_runtime_grp;

GRANT SELECT ON ALL TABLES IN SCHEMA public
TO gym_readonly_grp;

-- Sequence privileges
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public
TO gym_admin_grp;

GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public
TO gym_runtime_grp;