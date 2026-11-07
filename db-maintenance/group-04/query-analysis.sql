-- Group 04: HealthBridge Clinic Management System
-- Activity 5: Query Performance Analysis
-- Based on the actual backend query in get_doctors.php.

EXPLAIN
SELECT d.doctor_id, d.first_name, d.last_name, d.email, s.specialty_name
FROM doctors d
LEFT JOIN specialties s ON d.specialty_id = s.specialty_id;

-- Observed plan:
-- Hash Right Join
-- Sequential Scan on specialties
-- Sequential Scan on doctors
-- Estimated output: 140 rows