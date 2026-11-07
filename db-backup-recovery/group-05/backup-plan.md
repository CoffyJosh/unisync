# Group 05 - SHIORI Café

## Activity 6: Backup and Recovery Plan

### 1. Database and Backup Scope

- Database: `grp10_shioricafedb`
- Schema: `shioricafe_schema`
- Scope: All database objects and data, including tables, sequences, views, indexes, constraints, and related schema definitions.

### 2. Backup Method

Use PostgreSQL `pg_dump` with the custom archive format (`-Fc`). This format supports archive inspection using `pg_restore --list` and flexible restoration using `pg_restore`.

### 3. Backup Storage and Access

- Storage: Protected local backup directory outside the project repository.
- Access: Authorized database administrator or designated backup operator.
- Backup files will not be committed to Git or included in the repository.

### 4. Backup Verification

The backup will be inspected using `pg_restore --list`, then restored into a separate test database. Verification will include database objects, sample records, constraints, representative queries, and access-control checks.

### 5. Proposed Schedule and Retention

- Schedule: Daily full logical backup.
- Retention: Seven daily backups and four weekly backups.
- Older backups should be securely removed according to the retention policy.

### 6. Recovery Objectives

- Recovery Point Objective (RPO): 24 hours.
- Recovery Time Objective (RTO): 4 hours.

These are proposed classroom planning targets and should be adjusted for actual business requirements.

### 7. Access Control and Role Recreation

PostgreSQL roles and memberships are cluster-wide and are not included in a normal single-database `pg_dump` archive.

The existing Activity 4 roles and memberships will therefore be checked separately and recreated from approved role definitions if necessary:

- `app_runtime`
- `app_runtime_grp`
- `app_admin_grp`
- `app_readonly_grp`

The application runtime account will not be used as the backup or restore administrator. No application permissions will be expanded just to complete the backup task.

### 8. Safety Measures

The original database and initial backup will remain unchanged. Restoration and destructive recovery tests will use separate disposable databases only. No real credentials or backup data files will be committed to the repository.