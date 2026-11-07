
# Group 02 - Backup and Recovery Plan

## Project
High School Event Management System

## Database Information
- Database: grp02_event_management_db
- PostgreSQL Version: 18.6
- Backup Format: Custom Format

## Backup Procedure
A custom-format database backup was created using `pg_dump`.

## Recovery Procedure
The backup was restored using `pg_restore` into a separate test database named `grp02_event_management_restore_test`. The original database was left untouched.
