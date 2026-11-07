# Activity 6 – Backup and Recovery Report

## 1. Backup Plan

**Database:** `grp10_shioricafedb`  
**Schema:** `shioricafe_schema`  
**PostgreSQL Version:** 18.6

A PostgreSQL custom-format backup was selected using `pg_dump -Fc`. The backup was stored outside the project repository to avoid committing database backup data.

The backup plan includes:
- Daily backup schedule.
- Retention of 7 daily backups and 4 weekly backups.
- Recovery Point Objective (RPO): 24 hours.
- Recovery Time Objective (RTO): 4 hours.
- Separate restoration environment for verification.
- Rechecking roles and memberships because `pg_dump` does not include cluster-wide role definitions.

## 2. Backup Creation and Inspection

The initial backup was created successfully.

| Property | Result |
|---|---|
| Backup file | `grp10_shioricafedb_activity6.dump` |
| Format | CUSTOM |
| Size | 77,417 bytes |
| TOC entries | 166 |
| Compression | gzip |
| PostgreSQL version | 18.6 |
| SHA-256 | `EE7C857BEF29A84DDDE39047A0388B7D84CE80027DA894B3D2634E59240218A8` |

The archive was inspected using `pg_restore --list`. Its contents included the application schema, extension, functions, tables, sequences, and other database objects.

## 3. Restoration Verification

The initial backup was restored into the separate database `grp10_shioricafedb_restore_test`.

Verification results:

| Check | Result |
|---|---|
| Base tables | 18 |
| Views | 3 |
| Customer records | 4 |
| Menu item records | 0 |
| Reservation records | 1 |
| Constraints | 223 |
| Representative reservation SELECT | Successful |

The checked sample record counts matched the original database.

## 4. Access Control After Restore

The restored environment retained the existing PostgreSQL roles and memberships:

- `app_runtime`
- `app_runtime_grp`
- `app_admin_grp`
- `app_readonly_grp`

The roles and memberships were already present in the same PostgreSQL instance. They were not recreated by `pg_dump`.

Access verification:
- `app_runtime` successfully passed the database CONNECT privilege check.
- A schema access attempt by `app_runtime` was denied, as expected based on the existing privileges.
- A representative SELECT using the database administrator account successfully returned the restored reservation record.

The application's runtime role still lacks the required schema access. No permissions were modified during verification.

## 5. Controlled Recovery Simulation

A disposable test record was created in the restored test database:

- Table: `shioricafe_schema.category`
- Category ID: `10`
- Marker: `ACTIVITY6_RECOVERY_TEST`

A separate custom-format recovery snapshot was created and inspected. The disposable record was then deleted to simulate data loss.

The snapshot was restored into a fresh database named `grp10_shioricafedb_recovery_test`.

A SELECT query confirmed that the deleted record was recovered with its original ID and marker.

**Recovery simulation result: Successful.**

The original database and initial backup were not modified during the simulation.

## 6. Recovery Evaluation

The initial backup was successfully inspected and restored. Database objects, sample records, constraints, and access behavior were verified.

The controlled recovery simulation also successfully recovered the deleted disposable record from the separate recovery snapshot.

**RPO:** 24 hours  
**RTO:** 4 hours

These are planned recovery targets. Actual recovery duration was not formally measured against the RTO.

## 7. Limitations and Findings

- The application's current implementation uses MySQLi and MySQL, while this activity verified a separate PostgreSQL database. Direct application connection to the restored PostgreSQL database was therefore not tested.
- The `app_runtime` role lacks schema USAGE and cannot access application tables.
- The initial recovery restore attempt reported 126 errors ignored. A subsequent verbose restore produced no matching error, warning, or fatal lines in the captured log, and the recovery record was successfully verified.
- Cluster-wide roles and memberships must be managed separately from ordinary `pg_dump` backups.

## 8. Recommendations

- Review and correct the runtime role's schema and table privileges.
- Keep database backups outside the application repository.
- Perform scheduled restore tests.
- Preserve role definitions and membership information separately.
- Monitor PostgreSQL logs and investigate recurring application errors.

## 9. Feedback to System Owners

The system owners should review the runtime role's insufficient schema privileges and the Counter POS receipt completion error involving a NULL `reservation_id`.

## 10. AI Usage Declaration

AI assistance was used to help prepare verification commands, interpret PostgreSQL outputs, organize findings, and draft the backup and recovery documentation. Commands were executed and results were checked by the student.