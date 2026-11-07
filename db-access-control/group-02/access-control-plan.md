@'
# Group 02 - Access Control Plan

## Project
High School Event Management System

## Database
- Database: grp02_event_management_db
- Schema: school_event_management_db
- PostgreSQL Version: 18.6

## Database Roles

| Role | Responsibility |
|---|---|
| event_owner | Owns the project schema and manages Flyway migrations. |
| event_app | Handles application runtime database operations. |

## Access Control Design

- `event_owner` owns the `school_event_management_db` schema.
- `event_app` has USAGE permission on the schema but cannot create schema objects.
- `event_app` has SELECT-only access to the `role` table.
- `event_app` can SELECT and INSERT into `audit_log`, but cannot UPDATE or DELETE records.
- `event_app` has the required runtime permissions on other application tables.

## Verification Result

All tested role attributes, schema ownership, and table privileges matched the expected access-control design.

**Overall Result: Success**
'@ | Set-Content "db-access-control\group-02\access-control-plan.md"