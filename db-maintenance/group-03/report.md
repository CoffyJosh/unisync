
# Group 03 – Pawpaw Clinic
## Activity 5: Database Maintenance and Monitoring

### 1. Environment
- Database: `group3_pawpaw`
- PostgreSQL: 18.6
- Account: `pawpaw_app`
- Application role: Runtime backend account

### 2. Health and Activity Inspection

| Check | Actual Result |
|---|---|
| Database connection | Successful |
| Database size | 8,782 kB |
| Tables | 14 |
| Current connections | 1 |
| PostgreSQL version | 18.6 |
| Visible database session | 1 active session |
| Activity 4 roles | All 3 roles present |
| Runtime login restrictions | No SUPERUSER, CREATEDB, or CREATEROLE |

### 3. Log Inspection

- Logging collector: Enabled.
- Log destination: `stderr`.
- Log directory: Could not be inspected.
- Reason: `pawpaw_app` lacks `pg_read_all_settings` privileges.
- No claim is made about errors or warnings in logs because the log contents were not inspected.

### 4. Routine Maintenance

ANALYZE was attempted using `pawpaw_app` but was denied due to insufficient privileges.

VACUUM was not performed. The restricted account was not bypassed by switching to a superuser.

**Recommendation:** An authorized database maintenance account should perform ANALYZE and VACUUM when appropriate.

### 5. Query Performance Analysis

Selected query:

```sql
EXPLAIN
SELECT *
FROM public.appointment
LIMIT 10;
```

Observed plan:
- Limit
- Sequential Scan on `appointment`
- Estimated rows: 270
- Estimated cost: 0.00..12.70

**Interpretation:** PostgreSQL uses a sequential scan to retrieve up to 10 rows. Based on this plan alone, there is insufficient evidence to justify adding an index.

### 6. Findings and Recommendations

| Finding | Priority | Recommendation |
|---|---|---|
| Database accessible through runtime account | No action required | Continue monitoring |
| Log directory inaccessible | Follow-up action | Authorized administrator may inspect logs |
| ANALYZE permission denied | Follow-up action | Schedule maintenance through an authorized account |
| VACUUM not performed | Follow-up action | Review maintenance needs and permissions |
| Sequential scan observed | No action required | No index change justified by current evidence |

### 7. Conclusion

The Pawpaw Clinic database was reachable and basic health checks were successful. All three Activity 4 roles remained present. The restricted runtime account could inspect database activity and execute the selected query, but could not inspect the configured log directory or perform ANALYZE.

No superuser bypass or unnecessary database changes were made.
