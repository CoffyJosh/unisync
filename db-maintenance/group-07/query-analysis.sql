-- Group 07 – Applicant Management System
-- Activity 5: Query Performance Analysis
-- Purpose: Inspect the execution plan of an applicant email lookup.
-- Changes: Read-only SELECT and EXPLAIN commands; no database modifications.

-- Estimated execution plan.
EXPLAIN
SELECT *
FROM applicant
WHERE email = 'applicant1@example.com';

-- Actual execution plan and buffer usage.
EXPLAIN (ANALYZE, BUFFERS)
SELECT *
FROM applicant
WHERE email = 'applicant1@example.com';