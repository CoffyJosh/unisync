
-- Group 11 - ReserveIT
-- Activity 4: Representative Privilege Tests
--
-- Execute each section using the indicated login account.
-- UPDATE and DELETE statements use WHERE FALSE to avoid modifying data.

-- ==========================================
-- A. Runtime Account: reserveit_app
-- ==========================================

-- Expected: Allowed
SELECT COUNT(*) FROM room;

-- Expected: Allowed (no records modified)
UPDATE room
SET room_name = room_name
WHERE FALSE;

-- Expected: Permission denied
DELETE FROM reservation
WHERE FALSE;

-- Expected: Permission denied
CREATE DATABASE activity4_permission_test;


-- ==========================================
-- B. Reporter Account: reserveit_reporter
-- ==========================================

-- Expected: Allowed
SELECT COUNT(*) FROM room;

-- Expected: Permission denied
SELECT email FROM app_user;

-- Expected: Permission denied
UPDATE room
SET room_name = room_name
WHERE FALSE;


-- ==========================================
-- C. DBA Account: reserveit_dba
-- ==========================================

-- Expected: Allowed
SELECT COUNT(*) FROM room;

-- Expected: Allowed (no records modified)
UPDATE room
SET room_name = room_name
WHERE FALSE;

-- Expected: Permission denied on schema public
CREATE TABLE activity4_permission_test (
    id INT
);


-- ==========================================
-- D. Cross-Database Access Verification
-- ==========================================

-- Expected / Observed: TRUE
SELECT has_database_privilege(
    'reserveit_app',
    'grp10_unisyncdb',
    'CONNECT'
);


-- Verify whether PUBLIC has CONNECT on the
-- unrelated database.

-- Observed: TRUE
SELECT
    datname,
    COALESCE(
        (
            SELECT bool_or(privilege_type = 'CONNECT')
            FROM aclexplode(
                COALESCE(datacl, acldefault('d', datdba))
            )
            WHERE grantee = 0
        ),
        FALSE
    ) AS public_connect
FROM pg_database
WHERE datname = 'grp10_unisyncdb';


-- Actual connection was also tested using:
-- psql -U reserveit_app -d grp10_unisyncdb
--
-- SELECT current_user, current_database();
--
-- Observed:
-- current_user  = reserveit_app
-- current_database = grp10_unisyncdb
--
-- Finding:
-- PUBLIC CONNECT allows the runtime account to
-- connect to the unrelated database.
-- No privileges were changed.
