# Database Maintenance and Query Analysis Report

**Project:** Batumbakal Gym Membership Management System  
**Database:** `grp99_gymdb`

## Database Overview

- Database size: 8,494 kB
- Tables inspected: 8
- Indexes identified: 11
- Initial dead tuples: 7 total
  - `members`: 5
  - `trainers`: 2

## Maintenance Results

Executed `VACUUM (ANALYZE)` successfully.

Post-maintenance verification showed:
- All 8 tables had zero dead tuples.
- `last_vacuum` and `last_analyze` timestamps were updated.
- No maintenance errors were encountered.

## Query Analysis

Analyzed a query joining `members`, `memberships`, and `membership_plans` to retrieve active membership information.

| Metric | Result |
|---|---:|
| Rows returned | 4 |
| Planning time | 2.019 ms |
| Execution time | 0.109 ms |
| Execution method | Hash joins and sequential scans |
| Sort method | Quicksort |

The query executed successfully. Sequential scans were used on the small tables, which is reasonable for the current dataset.

## Conclusion

Database maintenance completed successfully, and all inspected tables reported zero dead tuples afterward. The representative membership query returned the expected records with a measured execution time of 0.109 ms.