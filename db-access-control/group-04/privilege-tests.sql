-- Group 04: HealthBridge Clinic Management System
-- Activity 4: Privilege Verification
-- Results are based on checks actually performed.
-- No database permissions were modified.

-- Check table privileges for hb_app.
SELECT
    has_table_privilege('hb_app', 'doctors', 'SELECT') AS can_read_doctors,
    has_table_privilege('hb_app', 'doctors', 'INSERT') AS can_insert_doctors,
    has_table_privilege('hb_app', 'doctors', 'UPDATE') AS can_update_doctors,
    has_table_privilege('hb_app', 'doctors', 'DELETE') AS can_delete_doctors;

-- Expected observed results:
-- SELECT = true
-- INSERT = false
-- UPDATE = false
-- DELETE = false

-- Check sequence privileges for hb_app.
SELECT
    has_sequence_privilege('hb_app', 'doctors_doctor_id_seq', 'USAGE') AS can_use_sequence,
    has_sequence_privilege('hb_app', 'doctors_doctor_id_seq', 'SELECT') AS can_read_sequence;

-- Expected observed results:
-- USAGE = true
-- SELECT = true

-- Actual write-permission test:
-- Connected to grp04_projectdb as hb_app and executed:
-- UPDATE doctors SET first_name = first_name WHERE doctor_id = 1;
-- Observed result: ERROR: permission denied for table doctors.

-- Database connection and identity can be checked with:
-- SELECT current_user, current_database();