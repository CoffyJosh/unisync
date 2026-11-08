# Group 11 - ReserveIT
## Activity 5: Database Maintenance and Performance Monitoring

### 1. Database Health

- Database: ReserveIT
- PostgreSQL Version: 18.6
- Database Size: 9030 kB
- Application Tables: 8

### 2. Database Activity

| Metric | Result |
|---|---:|
| Active Connections | 1 |
| Transaction Commits | 184 |
| Transaction Rollbacks | 75 |
| Deadlocks | 0 |
| Conflicts | 0 |

Transaction rollbacks were observed, but the statistics alone do not establish that they represent application errors.

### 3. Maintenance Results

Initial inspection showed that maintenance timestamps were NULL for the application tables. Dead tuple estimates included:

- material: 9
- penalty: 1
- reservation: 3
- room: 4

The reserveit_dba account could not execute ANALYZE and VACUUM on the application tables because it lacked the required privileges.

Maintenance was subsequently performed using reserveit_owner.

**Result:** ANALYZE and VACUUM completed. Post-maintenance inspection showed updated maintenance timestamps and zero estimated dead tuples across all eight application tables.

### 4. Query Performance

Tested query:

    SELECT *
    FROM reservation
    WHERE status = 'Pending';

| Metric | Result |
|---|---|
| Execution Plan | Sequential Scan |
| Rows Scanned | 4 |
| Rows Returned | 0 |
| Rows Removed by Filter | 4 |
| Planning Time | 0.648 ms |
| Execution Time | 0.033 ms |

The sequential scan was reasonable for the small reservation table. No additional index was recommended based on this test alone.

### 5. PostgreSQL Log Review

The PostgreSQL logging collector was enabled.

| Setting | Value |
|---|---|
| Log Destination | stderr |
| Logging Collector | on |
| Statement Logging | none |
| Duration Logging | disabled (-1) |

The server log contained failed authentication attempts, SQL errors, missing-role references, permission errors, and attempts to create existing objects.

These entries were server-wide observations and were not all attributable to ReserveIT.

The reserveit_owner account could not inspect protected configuration parameters such as log_directory, log_filename, and data_directory.

### 6. Findings and Recommendations

1. Continue routine ANALYZE and VACUUM maintenance.
2. Use the appropriate database owner or grant the intended maintenance privileges to the DBA role.
3. Review repeated failed authentication attempts and verify their source.
4. Correct invalid SQL statements and references to nonexistent roles.
5. Consider enabling appropriate statement logging when query-level auditing is required.
6. Continue monitoring database activity and query execution performance.

### 7. Overall Result

Database health inspection, maintenance, query performance analysis, and PostgreSQL log review were completed. Maintenance succeeded using the database owner account, while the DBA role's maintenance limitation was documented.