# Group 01 - DropBoxed Access Control Plan

## Database Information
- Database: dropboxed_db
- Owner: postgres
- Schema: dropboxed
- Application: DropBoxed
- Repository: https://github.com/joshxaaaaaa/GRP1_Dropboxed
- Branch: main

## Database Objects
The database contains eight tables:
- users
- staff
- complaint_categories
- locations
- complaints
- complaint_assignments
- status_logs
- attachments

Identity sequences are used for generated primary keys.

## Existing Role Structure

| Role | Purpose |
|---|---|
| dropboxed_app | Application runtime login |
| dropboxed_runtime_grp | Runtime permissions |
| dropboxed_report | Reporting login |
| dropboxed_readonly_grp | Read-only permissions |
| dropboxed_dba | Database maintenance login |
| dropboxed_dba_grp | DBA permissions |
| dropboxed_backup | Backup login |
| dropboxed_backup_grp | Backup permissions |

## Access Control Strategy
- Runtime accounts receive only the permissions needed by the application.
- Read-only accounts can retrieve data but cannot modify it.
- Backup accounts receive read access for backup operations.
- DBA accounts receive broader maintenance permissions.
- Group roles separate permissions from login accounts.
- Application accounts must not use PostgreSQL superuser privileges.
- Accounts must not access unrelated project databases.

## Initial Findings
- Four non-login group roles are present.
- Login roles are assigned to their corresponding group roles.
- Database CONNECT privileges are granted to the four group roles.
- Table permissions differ according to account responsibilities.
- Runtime permissions include DELETE on complaint_assignments.
- Cross-database access was detected. The dropboxed_app account can CONNECT to unrelated databases because PostgreSQL's default PUBLIC privileges are in effect.
- Recommended improvement: Revoke unnecessary PUBLIC CONNECT privileges and grant CONNECT only to authorized roles, coordinated with each database owner.
- No unrelated database privileges were modified during testing.