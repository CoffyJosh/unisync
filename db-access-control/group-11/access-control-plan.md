
# Group 11 - ReserveIT: Classroom and Material Reservation System
## Activity 4 - Database Access-Control Plan

### Project Information

| Item | Details |
|---|---|
| Project | ReserveIT: Classroom and Material Reservation System |
| Database | ReserveIT |
| DBMS | PostgreSQL 18.6 |
| Application | Python 3.11 / Flask 3.1.3 |
| Host | localhost |
| Port | 5432 |

### 1. Existing Role Design

The project implements separate login accounts and group roles to enforce least-privilege access.

| Role / Account | Type | Responsibility |
|---|---|---|
| reserveit_owner | Login role | Database and object ownership; setup and maintenance |
| reserveit_app | Login role | Application runtime operations |
| reserveit_reporter | Login role | Read-only reporting |
| reserveit_dba | Login role | Database data maintenance |
| app_runtime_grp | NOLOGIN | Restricted application operations |
| app_readonly_grp | NOLOGIN | Read-only access |
| app_admin_grp | NOLOGIN | Administrative data operations |

### 2. Role Membership

| Login Account | Assigned Group Role |
|---|---|
| reserveit_app | app_runtime_grp |
| reserveit_reporter | app_readonly_grp |
| reserveit_dba | app_admin_grp |

The owner account remains separate from the application group roles.

All four login roles were verified to have:
- LOGIN enabled
- NOSUPERUSER
- NOCREATEDB
- NOCREATEROLE

All three group roles were verified as NOLOGIN roles.

### 3. Privilege Plan

**Runtime Group (`app_runtime_grp`)**
- SELECT and INSERT on designated application tables.
- UPDATE restricted to specific columns where necessary.
- DELETE permitted only on selected tables.
- No DELETE privilege on reservations.
- USAGE on required sequences.

**Read-only Group (`app_readonly_grp`)**
- SELECT on approved reporting tables.
- Restricted column-level SELECT on `app_user` and `staff`.
- No INSERT, UPDATE, or DELETE permissions.

**Administrative Group (`app_admin_grp`)**
- SELECT, INSERT, UPDATE, and DELETE on application tables.
- USAGE, SELECT, and UPDATE on sequences.
- No schema creation privileges.

**Owner (`reserveit_owner`)**
- Owns the database and database objects.
- Used for setup and maintenance operations.
- Kept separate from normal application accounts.

### 4. Verification Results

| Account | Test | Expected Result | Actual Result |
|---|---|---|---|
| reserveit_app | SELECT from room | Allowed | Success; 6 records |
| reserveit_app | UPDATE room_name | Allowed | Success; 0 rows affected |
| reserveit_app | DELETE reservation | Denied | Denied |
| reserveit_reporter | SELECT from room | Allowed | Success; 6 records |
| reserveit_reporter | SELECT email from app_user | Denied | Denied |
| reserveit_reporter | UPDATE room | Denied | Denied |
| reserveit_dba | SELECT from room | Allowed | Success; 6 records |
| reserveit_dba | UPDATE room_name | Allowed | Success; 0 rows affected |
| reserveit_dba | CREATE TABLE in public | Denied | Denied |
| reserveit_app | CREATE DATABASE | Denied | Denied |

### 5. Findings

The representative permission tests confirmed that the existing role design works as intended.

One cross-database access finding was identified. The `reserveit_app` account was able to connect to the separate `grp10_unisyncdb` database because that database grants CONNECT to PUBLIC by default.

The connection was verified using both `has_database_privilege()` and an actual PostgreSQL login.

This finding concerns cross-database connection isolation. It does not establish that the account can access or modify UniSync tables. No permissions were changed on either database.

### 6. Conclusion

The ReserveIT database access-control configuration passed the representative Activity 4 tests. Runtime, reporting, and administrative permissions behaved according to their intended restrictions.

A cross-database CONNECT finding was documented for review by the owning group.

### 7. AI Declaration

AI assistance was used to help prepare verification queries, interpret PostgreSQL permission results, and organize the access-control documentation. All recorded tests were executed and verified by the student.
