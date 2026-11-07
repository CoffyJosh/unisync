# Backup Plan – Group 03: Pawpaw Clinic

## Database
- Database: group3_pawpaw
- Platform: PostgreSQL 18.6
- Objects: 14 tables, sequences, indexes, constraints, triggers, functions, and data.

## Backup Method
Use pg_dump with custom archive format (-F c).
This format supports selective restoration and inspection through pg_restore.

## Storage and Access
- Backup location: Windows temporary folder ($env:TEMP).
- Backup access: Authorized database administrators.
- Backup files are excluded from Git.

## Verification
- Inspect archive using pg_restore --list.
- Restore into a separate test database.
- Verify tables, records, constraints, privileges, and representative queries.

## Proposed Schedule and Retention
- Daily backups.
- Retain 7 daily backups and 4 weekly backups.
- Store backups securely outside the application repository.

## Recovery Objectives
- Proposed RPO: 24 hours.
- Proposed RTO: 4 hours.

## Access Control Recovery
PostgreSQL roles are cluster-wide and are not included in a single-database dump.
Recreate roles and memberships using the Activity 4 roles.sql script when needed. Restore database privileges and verify permitted and denied operations.

## Safety
The original database remains untouched. All restoration and destructive recovery tests use disposable databases and test records.