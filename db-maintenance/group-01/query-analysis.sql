-- Group 01: DropBoxed
-- Activity 5: Query Performance Analysis

EXPLAIN
SELECT *
FROM dropboxed.complaints
WHERE status = 'pending';