-- Group 04 - HealthBridge Clinic Management System
-- Activity 6: Backup and Recovery Verification
-- Run while connected to grp04_projectdb_restore_test.

-- 1. Verify restored tables
\dt

-- 2. Verify record counts
SELECT 'specialties' AS table_name, COUNT(*) FROM specialties
UNION ALL SELECT 'doctors', COUNT(*) FROM doctors
UNION ALL SELECT 'patients', COUNT(*) FROM patients
UNION ALL SELECT 'appointments', COUNT(*) FROM appointments
UNION ALL SELECT 'medical_notes', COUNT(*) FROM medical_notes
UNION ALL SELECT 'prescriptions', COUNT(*) FROM prescriptions
UNION ALL SELECT 'billing', COUNT(*) FROM billing
UNION ALL SELECT 'staff_users', COUNT(*) FROM staff_users
UNION ALL SELECT 'staff_password_resets', COUNT(*) FROM staff_password_resets
UNION ALL SELECT 'patient_password_resets', COUNT(*) FROM patient_password_resets
UNION ALL SELECT 'deleted_records', COUNT(*) FROM deleted_records;

-- 3. Verify constraints
SELECT COUNT(*) AS total_constraints
FROM information_schema.table_constraints
WHERE constraint_schema = 'public';

-- 4. Verify database and current user
SELECT current_database(), current_user;