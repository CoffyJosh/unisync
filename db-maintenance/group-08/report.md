# Activity 5: Database Maintenance Report

**Project:** Group 08 - Catering Business Management System  
**Database:** `catering_management_db`  
**DBMS:** PostgreSQL 18.6

## 1. Objective

Inspect the database condition, perform routine maintenance, verify maintenance results, and analyze a representative reservation query.

## 2. Database Baseline

| Metric | Observed Result |
|---|---:|
| Database size | 8710 kB |
| Number of tables | 12 |
| Existing indexes | 16 |
| Estimated live rows | 31 |
| Dead tuples before maintenance | 17 |

## 3. Maintenance Performed

Executed:

```sql
VACUUM (ANALYZE);
```

The command completed successfully. It cleaned up dead tuples and refreshed query-planner statistics.

### Verification

After maintenance, all 12 tables reported zero dead tuples. The `last_vacuum` and `last_analyze` timestamps were updated for every table.

## 4. Query Performance Analysis

Analyzed a reservation query joining:

- `reservation`
- `customer`
- `package`

The query retrieved reservation details, customer names, package names, event dates, statuses, and estimated costs.

### Execution Results

| Metric | Result |
|---|---:|
| Rows returned | 4 |
| Planning time | 4.509 ms |
| Execution time | 0.166 ms |
| Sort method | Quicksort |
| Sort memory | 25 kB |
| Join method | Hash Join |

PostgreSQL used sequential scans on the three tables and hash joins for their relationships. These choices are reasonable for the small test dataset.

## 5. Index Inspection

A total of 16 existing indexes were identified, including primary-key indexes, unique constraint indexes, and an index on the session expiration column.

The reservation query's foreign-key columns (`customer_id` and `package_id`) and `event_date` do not have dedicated indexes. Additional indexes may be considered if the database grows, but they were not added because the current dataset is small.

## 6. Connection Check

The database connection inspection returned one active connection, corresponding to the current `psql` session.

## 7. Findings

- Database maintenance completed successfully.
- All 12 tables had zero dead tuples after maintenance.
- Query execution completed successfully in 0.166 ms.
- Existing indexes were inspected; no additional indexes were necessary for the current dataset.
- No unusually high connection count was observed.

## 8. Result

**SUCCESS** - Database maintenance, verification, index inspection, and representative query performance analysis were completed successfully.