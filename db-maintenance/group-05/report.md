# Group 05 - SHIORI Café

## Activity 5: Database Maintenance and Monitoring

### A. Database Health and Activity

| Check | Actual Result |
|---|---|
| Database | `grp10_shioricafedb` |
| PostgreSQL Version | PostgreSQL 18.6 (64-bit Windows) |
| Database Size | 9806 kB |
| Active Backends | 1 |
| Transaction Commits | 1479 |
| Transaction Rollbacks | 19 |
| Database Objects | 18 base tables and 3 views |
| Active Session | PostgreSQL `psql` session using `postgres` |
| Long-running Queries | None observed during inspection |

### B. Log Review

**Log configuration:**
- Destination: `stderr`
- Logging collector: Enabled
- Log directory: `log`
- Log filename pattern: `postgresql-%Y-%m-%d_%H%M%S.log`
- Inspected file: `postgresql-2026-11-07_000000.log`

**Findings:**
- Failed password authentication attempts for `postgres` were recorded.
- Several missing relation and column errors occurred during testing, indicating incorrect or mismatched queries.
- A `reservation_id` NOT NULL constraint violation was recorded during Counter POS receipt completion. This matches the Activity 3 finding.
- A schema permission error was recorded during the Activity 4 access restriction test.

These entries were reviewed as evidence from testing. Failed authentication attempts were not automatically classified as unauthorized access.

### C. Database Maintenance

The following operations completed successfully on `shioricafe_schema.reservation` using the `postgres` administrative account:

| Operation | Result |
|---|---|
| `ANALYZE VERBOSE` | Successful |
| `VACUUM (VERBOSE, ANALYZE)` | Successful |
| Pages scanned | 1 |
| Live rows | 1 |
| Dead rows | 0 |
| `VACUUM FULL` | Not used |

The maintenance operations completed without errors. However, `app_runtime` does not have schema `USAGE` or table `MAINTAIN` privileges. Maintenance was therefore performed using `postgres`, not the application role.

### D. Query Performance Analysis

A representative menu listing query was prepared using the actual columns of `shioricafe_schema.menu_item`.

The query retrieves active menu items and orders them by item name.

**Result:** The `EXPLAIN` operation was blocked when executed under `app_runtime` because the role lacks `USAGE` permission on `shioricafe_schema`.

No execution plan was produced, and no permissions were changed to bypass the restriction.

### E. Findings and Recommendations

1. Routine maintenance commands completed successfully using the administrative account.
2. The application role has insufficient schema permissions for the representative query.
3. The existing access restrictions should be reviewed by the database administrator if query analysis or application access is intended.
4. The Counter POS receipt completion error involving `reservation_id` remains a finding from Activity 3.
5. Continue monitoring PostgreSQL logs for recurring application errors and failed authentication attempts.

### Overall Result

**Issue**

Database health inspection, log review, ANALYZE, and VACUUM were completed. However, query performance analysis under `app_runtime` was blocked by the existing schema permissions. Maintenance was performed using the administrative account rather than the application role.