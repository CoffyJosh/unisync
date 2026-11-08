-- Group 11 - ReserveIT
-- Activity 5: Query Performance Analysis

EXPLAIN ANALYZE
SELECT *
FROM reservation
WHERE status = 'Pending';