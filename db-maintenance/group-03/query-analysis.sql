
-- Group 03 - Pawpaw Clinic
-- Activity 5: Query Performance Analysis

-- Selected application table: appointment
-- Account: pawpaw_app

EXPLAIN
SELECT *
FROM public.appointment
LIMIT 10;

-- Actual execution plan:
-- Limit (cost=0.00..0.47 rows=10 width=266)
--   -> Seq Scan on appointment
--      (cost=0.00..12.70 rows=270 width=266)

-- Interpretation:
-- PostgreSQL uses a sequential scan.
-- Estimated table rows: 270.
-- No index recommendation is justified from this
-- limited query plan alone.
