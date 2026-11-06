# Group 01 - DropBoxed
## Activity 5: Database Maintenance and Monitoring

### 1. Database Health
- Database: `dropboxed_db`
- PostgreSQL: 18.6
- Account: `dropboxed_dba`
- Database size: 8,886 kB
- Tables: 8
- Active sessions: 1
- Other active queries: 0
- Result: Healthy

### 2. Log Review
Reviewed PostgreSQL logs from November 5, 2026.

- Duplicate venue entries were rejected by a unique constraint.
- An invalid test query referenced a nonexistent `your_table_name`.
- Checkpoint messages were normal database activity.

Recommendation: Review duplicate submission handling and remove placeholder queries from tests.

### 3. Database Maintenance
Successfully executed:
- `ANALYZE dropboxed.complaints;`
- `VACUUM (VERBOSE, ANALYZE) dropboxed.complaints;`

The maintenance completed successfully. The table contained 61 live rows afterward, with no remaining dead tuples.

### 4. Query Performance
Query tested:

```sql
EXPLAIN
SELECT *
FROM dropboxed.complaints
WHERE status = 'pending';
```

Result: Sequential Scan (Seq Scan).

The table contains only 61 rows, so the sequential scan is reasonable. No index changes are recommended at this time.

### 5. Overall Assessment
The database was accessible and healthy during inspection. Maintenance operations completed successfully. Minor application/query errors were found in the logs, but no immediate database-level performance improvements are necessary.