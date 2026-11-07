# Backup and Recovery Report – Group 03: Pawpaw Clinic

## 1. Backup Creation

- Database: group3_pawpaw
- PostgreSQL version: 18.6
- Method: pg_dump custom format (-F c)
- Backup file: group3_pawpaw_backup.dump
- Location: Windows temporary folder
- File size: 45,977 bytes
- Date: November 7, 2026, 12:19 PM
- SHA-256: F0E16ED46212DF4D81802E4CD6705C0BD4325B9578E71C885B50ED48503A0212

## 2. Archive Inspection

Used pg_restore --list to inspect the archive.

Results:
- 127 TOC entries
- 14 tables and their data
- 12 sequences
- Primary key, unique, and foreign key constraints
- Index, trigger, and function entries

The archive was readable and contained the expected database objects.

## 3. Restoration

Restored the backup into the separate database:
group3_pawpaw_restore_test

Results:
- 14 tables successfully restored.
- All 14 expected table names were present.
- Sample record counts:
  - user_account: 2
  - pet: 0
  - appointment: 0
  - service: 10
- 118 table constraints found.
- Restored access check:
  - SELECT: Allowed
  - TRUNCATE: Denied
- Representative SELECT query returned five service records.

The application itself was not tested against the restored database.

## 4. Controlled Recovery Simulation

A disposable table named activity6_recovery_test was created in the restore-test database.

Timeline:
1. Inserted test record (1, 'RECOVERY_TEST_RECORD').
2. Backed up the disposable table using pg_dump.
3. Deleted the test record.
4. Restored the table using pg_restore.
5. Queried the table to verify recovery.

Result:
The deleted test record was successfully recovered with its original values.

The original group3_pawpaw database was never modified.

## 5. Evaluation and Recommendations

The custom-format logical backup was successfully inspected and restored.

Proposed deployment schedule:
- Daily backups.
- Retain 7 daily and 4 weekly backups.
- Proposed RPO: 24 hours.
- Proposed RTO: 4 hours.

More frequent backups and shorter retention intervals may be necessary for systems with more demanding recovery requirements. Backups should be stored securely and periodically tested.

## 6. Limitations

- The application was not connected to the restored database for testing.
- The restore verification used selected tables and records rather than comparing every row with the original.
- Cluster-wide roles are not included in a single-database dump and must be recreated separately when needed.

## 7. AI Usage Declaration

AI assistance was used to explain PostgreSQL backup, restoration, verification, and recovery commands and help prepare documentation. Commands were reviewed and executed in the local test environment. Results were checked using PostgreSQL command outputs.