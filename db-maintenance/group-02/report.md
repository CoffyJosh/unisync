@'
# Group 02 - Database Maintenance Report

## Project
High School Event Management System

## Database Information
- Database: grp02_event_management_db
- PostgreSQL Version: 18.6
- Database Size: 9222 kB

## Maintenance Performed
- Checked database size and table statistics.
- Checked PostgreSQL version and active connections.
- Executed ANALYZE VERBOSE successfully.
- Executed VACUUM (VERBOSE, ANALYZE) successfully.

## Query Performance
Tested query:
SELECT * FROM school_event_management_db.event;

- Execution Time: 0.102 ms
- Rows Returned: 1
- Execution Plan: Sequential Scan
- Result: Successful

## Findings
Database maintenance completed without errors. The tested query performed acceptably for the current data volume.
'@ | Set-Content "db-maintenance\group-02\report.md"