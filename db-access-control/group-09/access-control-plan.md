# Activity 4 - Database Access Control Plan

## Project
Group 09 - Batumbakal Gym Membership Management System

## Database
- Database: grp99_gymdb
- DBMS: PostgreSQL 18
- Schema: public
- Database Owner: postgres

## Access Control Design

Three group roles and three corresponding login roles were created.

| Group Role | Login Role | Permissions |
|---|---|---|
| gym_admin_grp | gym_admin | Full table and sequence privileges |
| gym_runtime_grp | gym_app | SELECT, INSERT, UPDATE, DELETE on tables; USAGE and SELECT on sequences |
| gym_readonly_grp | gym_viewer | SELECT on all tables |

## Implementation

- Group roles use NOLOGIN.
- Login roles inherit their corresponding group-role privileges.
- All three groups have CONNECT access to grp99_gymdb.
- All three groups have USAGE access to the public schema.
- No existing roles from other group projects were modified.
- The postgres account remains the database owner.

## Verification Results

| Test | Expected Result | Actual Result |
|---|---|---|
| Viewer SELECT | Allowed | Passed |
| Viewer UPDATE | Denied | Passed |
| Runtime SELECT | Allowed | Passed |
| Runtime INSERT | Allowed | Passed |
| Runtime UPDATE | Allowed | Passed |
| Runtime DELETE | Allowed | Passed |
| Admin SELECT | Allowed | Passed |
| Admin INSERT | Allowed | Passed |
| Admin DELETE | Allowed | Passed |

## Findings

The access-control configuration successfully separates administrative, application runtime, and read-only privileges.

The admin group has full table and sequence privileges but does not have superuser or schema-owner privileges.

Test credentials were created specifically for this verification and should be changed before production use.