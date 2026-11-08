
# Group 07 – Applicant Management System

## Activity 6: Database Backup, Restoration, and Recovery Report

### 1. Backup Creation

A logical backup of `grp07_applicantdb` was created using PostgreSQL `pg_dump` in custom archive format.

| Detail | Result |
|---|---|
| Database | `grp07_applicantdb` |
| Backup file | `grp07_applicantdb_activity6.dump` |
| Backup account | `grp07_backup` |
| Backup method | `pg_dump -Fc` |
| File size | 36,586 bytes |
| Backup date | November 8, 2026, 3:33 PM |
| SHA-256 | `1434F9BEADE3F18B05C77DC3A6083F42F954AE51F949368E9FDE368773466AB2` |
| Archive format | CUSTOM |
| Compression | gzip |
| TOC entries | 102 |
| Dump version | 1.16-0 |
| PostgreSQL version | 18.6 |

The archive was inspected using `pg_restore --list`. The listing completed and displayed the database objects.

### 2. Restoration and Verification

The initial backup was restored into a separate database named `grp07_applicantdb_restore_test`.

| Verification | Actual Result |
|---|---|
| Restore operation | Successful |
| Restored application tables | 9 |
| Applicant records | 10 |
| Application records | 12 |
| User records | 3 |
| Public constraints | 70 |

The restored database contained the nine application tables:

- applicant
- applicant_requirement
- application
- application_status
- department
- interview
- position
- requirement
- users

### 3. Access Control After Restoration

The three existing Activity 4 group roles were found after restoration:

- `app_runtime_grp`
- `app_admin_grp`
- `app_readonly_grp`

All three roles had `NOLOGIN` status and schema USAGE. However, the privilege checks showed that none had effective SELECT or INSERT privileges on `public.applicant`.

A separate backup account was used to test access:

| Operation | Result |
|---|---|
| SELECT from applicant | Allowed; 10 records returned |
| UPDATE on applicant | Denied; permission denied for table applicant |

The UPDATE attempt used `WHERE FALSE`, so no records were modified.

**Finding:** The backup account had the expected read-only behavior. The existing application group roles did not have the checked table-level privileges in the restored database. Their actual application access requirements should be reviewed by the project owners.

### 4. Recovery Simulation

A disposable table named `activity6_recovery_test` was created inside the restored test database. One record was inserted:

`1 | ACTIVITY6_RECOVERY_TEST`

A separate recovery snapshot was created:

| Detail | Result |
|---|---|
| Recovery file | `grp07_recovery_simulation.dump` |
| Size | 37,516 bytes |
| Created | November 8, 2026, 3:37 PM |
| SHA-256 | `B2B2CFEFE1CC1317FFC4F0B79F34A2DBB197123055BD96211166E35B794E10A4` |

The disposable record was then deleted from `grp07_applicantdb_restore_test`. The record count became zero.

A fresh database named `grp07_applicantdb_recovery_test` was created, and the recovery snapshot was restored into it.

The record was found again:

`1 | ACTIVITY6_RECOVERY_TEST`

**Result:** Recovery simulation successful. The deleted disposable record was recovered from the separate recovery snapshot.

### 5. Evaluation and Recommendations

The initial backup archive was successfully inspected and restored into a separate test database. The restored tables, representative records, and constraints were verified. The recovery simulation also demonstrated that a deleted disposable record could be recovered from a backup.

Recommendations:

1. Review the effective table-level privileges of the existing application roles.
2. Maintain a dedicated backup account with least-privilege access.
3. Automate the proposed daily backup schedule.
4. Enforce the proposed retention policy.
5. Periodically perform isolated restore and recovery tests.
6. Keep backup files outside the application repository and restrict access to their storage location.

### 6. Limitations and Issues

- The original database was not modified during the restore and recovery tests.
- Application-level connection testing after restoration was not performed.
- The three Activity 4 group roles existed, but the checked table-level privileges were not present.
- Automated backup scheduling and retention were not implemented.
- The proposed RPO of 24 hours and RTO of 4 hours were planning targets, not measured recovery guarantees.
- Recovery was demonstrated using a disposable test table and a separate recovery snapshot, not actual application records.

### 7. Conclusion

Group 07's Activity 6 backup, restoration, access-control, and recovery checks were completed in a local test environment. The initial backup was successfully restored, representative database objects and records were verified, and a deleted disposable record was successfully recovered in a fresh database.

The original database and initial backup remained untouched.
