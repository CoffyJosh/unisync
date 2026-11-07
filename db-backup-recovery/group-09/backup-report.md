# Database Backup Report

**Project:** Batumbakal Gym Membership Management System  
**Database:** `grp99_gymdb`  
**PostgreSQL Version:** 18.6

## Backup Information

| Property | Result |
|---|---|
| Backup filename | `batumbakal_activity6.dump` |
| Backup format | Custom |
| File size | 28,700 bytes |
| Archive entries | 84 |
| Database tables | 8 |
| Original records | 35 |

## SHA256 Checksum

`6E3166C10E2A75F249931D6207C23EE8873F4CB1BF2FD5BC64FEB62D95990761`

## Backup Inspection

The backup archive was successfully inspected using `pg_restore -l`. It contained the expected tables, table data, sequences, indexes, constraints, and foreign keys.

## Recovery Test Results

The backup was restored into the temporary database `grp99_gymdb_a6_restore`.

| Table | Original Rows | Restored Rows |
|---|---:|---:|
| attendance | 4 | 4 |
| class_enrollments | 4 | 4 |
| classes | 3 | 3 |
| members | 6 | 6 |
| membership_plans | 4 | 4 |
| memberships | 5 | 5 |
| payments | 5 | 5 |
| trainers | 4 | 4 |
| **Total** | **35** | **35** |

## Result

**Success.** The backup was readable and successfully restored. All 8 tables and 35 records matched the original database.