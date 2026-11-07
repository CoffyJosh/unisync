# Activity 4 – Access Control Plan

**Project:** Group 08 - Catering Business Management System  
**Database:** `catering_management_db`  
**DBMS:** PostgreSQL 18.6  
**Schema:** `public`

## Objective

Establish and verify separate PostgreSQL roles for administrative, application runtime, and read-only access.

## Role Design

| Group Role | Login Role | Intended Access |
|---|---|---|
| `catering_admin_grp` | `catering_admin` | Full table and sequence privileges |
| `catering_runtime_grp` | `catering_runtime` | SELECT, INSERT, UPDATE, DELETE; sequence usage |
| `catering_viewer_grp` | `catering_viewer` | Read-only access |

## Implemented Privileges

- All three group roles receive database CONNECT and schema USAGE.
- Administrator receives ALL PRIVILEGES on existing tables and sequences.
- Runtime receives SELECT, INSERT, UPDATE, DELETE on tables and USAGE, SELECT, UPDATE on sequences.
- Viewer receives SELECT on tables and sequences.
- Application roles do not have SUPERUSER, CREATEDB, or CREATEROLE attributes.

## Verification

Role memberships and privileges were inspected through PostgreSQL system catalogs and privilege-checking functions.

An actual test using `SET ROLE catering_viewer` confirmed that SELECT on `public.customer` succeeded while INSERT was denied.

## Findings and Limitations

- The database initially had no Catering-specific roles.
- Existing roles belonging to other projects were left unchanged.
- All 12 tables are owned by `postgres`.
- The login roles were created with LOGIN but no passwords were assigned during this exercise.
- The application itself was not reconfigured to use these newly created roles.

**Result:** Success