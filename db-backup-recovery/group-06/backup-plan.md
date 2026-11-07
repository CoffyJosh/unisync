# Group 06 - ByaheRentals
## Activity 6: Database Backup Plan

### 1. Database Information

| Item | Details |
|---|---|
| Database | `car_rental_db` |
| DBMS | PostgreSQL 18.6 |
| Host | localhost |
| Port | 5432 |
| Backup Format | Custom (`-Fc`) |
| Backup Utility | `pg_dump` |
| Restore Utility | `pg_restore` |

### 2. Backup Strategy

A full database backup will be created using PostgreSQL's custom-format backup option. This includes the database schema, tables, records, sequences, constraints, indexes, extensions, and database object permissions.

Backup files will be stored outside the project repository in a protected local directory:

`D:\DB_Backups\Group06\`

The backup files must not be committed to Git because they contain database information.

### 3. Backup Schedule and Retention

| Item | Proposed Policy |
|---|---|
| Backup Frequency | Daily |
| Daily Retention | 7 daily backups |
| Weekly Retention | 4 weekly backups |
| Storage | Protected local backup directory |
| Verification | Inspect backup archive and perform test restoration |

These are proposed backup policies and were not configured as an automated schedule during the activity.

### 4. Access Control

The PostgreSQL `postgres` administrator account was used for the backup and restoration operations in this activity.

The application account `app_runtime` is intended for application access and should not be used for unrestricted database backup or restoration.

The existing Activity 4 roles are:

- `app_admin_grp`
- `app_runtime_grp`
- `app_readonly_grp`
- `app_runtime`

For restoration to another PostgreSQL server, the required roles and memberships must be recreated separately. The existing `Database\db_roles_and_grants.sql` script can be used as the role configuration reference.

### 5. Recovery Objectives

| Objective | Proposed Target |
|---|---|
| Recovery Point Objective (RPO) | 24 hours |
| Recovery Time Objective (RTO) | 4 hours |

The RPO and RTO are proposed targets, not measured recovery results.

### 6. Backup Verification

Backups should be verified through:

1. Checking the backup file's existence and size.
2. Generating a SHA-256 checksum.
3. Inspecting the custom-format archive using `pg_restore --list`.
4. Restoring the backup into a separate test database.
5. Verifying restored tables, records, constraints, indexes, and permissions.
6. Performing a controlled recovery test using disposable test data.

### 7. Recovery Procedure

1. Create a separate empty recovery database.
2. Restore the selected backup using `pg_restore`.
3. Verify the restored database objects and representative records.
4. Confirm the required PostgreSQL roles and permissions.
5. Test application-account connectivity.
6. Compare the recovered data against the expected backup state.

The original database must remain untouched during restoration and recovery testing.