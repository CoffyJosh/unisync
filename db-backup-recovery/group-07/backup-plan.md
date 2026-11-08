
# Group 07 – Applicant Management System

## Activity 6: Database Backup, Restoration, and Recovery

### 1. Backup Plan

| Item | Plan |
|---|---|
| Database | `grp07_applicantdb` |
| Backup scope | All database tables, data, sequences, indexes, constraints, and other supported database objects |
| Backup method | PostgreSQL logical backup using `pg_dump` custom format (`-Fc`) |
| Backup location | `D:\db-backups\group-07\` |
| Backup account | `grp07_backup` |
| Backup schedule | Daily at 2:00 AM (proposed) |
| Retention | 7 daily, 4 weekly, and 3 monthly backups (proposed) |
| Proposed RPO | 24 hours |
| Proposed RTO | 4 hours |
| Restore method | `pg_restore` into a separate test database |
| Verification | Archive inspection, table and record checks, constraints, privileges, and recovery simulation |

### 2. Backup Account

Activity 4 did not identify a dedicated backup role. A separate local backup account was therefore created:

- Role: `grp07_backup`
- Login: Enabled
- Database access: CONNECT on `grp07_applicantdb`
- Schema access: USAGE on `public`
- Table access: SELECT on existing tables in `public`
- Sequence access: SELECT on existing sequences in `public`

The account was used for the initial logical backup. Its password is not included in this documentation.

### 3. Backup Commands

Initial backup:

```powershell
pg_dump -U grp07_backup -h localhost -p 5432 -Fc -d grp07_applicantdb -f "D:\db-backups\group-07\grp07_applicantdb_activity6.dump"
```

Inspect archive:

```powershell
pg_restore --list "D:\db-backups\group-07\grp07_applicantdb_activity6.dump"
```

### 4. Restoration Plan

1. Create a separate empty test database.
2. Restore the custom archive using `pg_restore`.
3. Verify restored tables, sample records, and constraints.
4. Inspect the existing Activity 4 roles and their effective privileges.
5. Test permitted and denied operations using the backup account.

### 5. Recovery Plan

A disposable recovery record will be created only in the restored test database. A separate recovery snapshot will be created, the test record will be deleted, and the snapshot will be restored into a fresh recovery database.

The original database and initial backup will remain untouched.

### 6. Limitations

The proposed backup schedule, retention policy, RPO, and RTO were documented as plans. Automated scheduling and retention enforcement were not implemented or tested.
