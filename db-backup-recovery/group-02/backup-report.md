
# Group 02 - Backup and Recovery Report

## Project
High School Event Management System

## Backup Details
- Database: grp02_event_management_db
- PostgreSQL Version: 18.6
- Backup Format: Custom
- Backup File: grp02_event_management_db.dump
- File Size: 38,274 bytes
- Archive Entries: 99
- SHA-256: 75A73CF2283D89C5D3BBD53A17BADBBCE5B795A54F4D575298ACE8A1D3C55512

## Recovery Test
- Test Database: grp02_event_management_restore_test
- Restoration Tool: pg_restore
- Restored Tables: 10
- Restoration Result: Successful

## Data Verification

| Table | Restored Rows |
|---|---:|
| attendance | 1 |
| audit_log | 17 |
| event | 1 |
| event_assignment | 0 |
| event_schedule | 1 |
| flyway_schema_history | 3 |
| participant | 1 |
| role | 2 |
| user_account | 2 |
| venue | 1 |

## Findings
The backup was successfully restored into a separate test database. All 10 tables were present, and the restored row counts matched the original database. The original database remained untouched.
