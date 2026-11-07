# Database Backup and Recovery Plan

**Project:** Group 08 - Catering Business Management System  
**Database:** `catering_management_db`  
**DBMS:** PostgreSQL 18.6

## 1. Objective

Create a database backup, verify its contents and integrity, and test recovery using a separate database without modifying the original.

## 2. Backup Configuration

| Item | Configuration |
|---|---|
| Database | `catering_management_db` |
| Backup format | Custom (`-Fc`) |
| Compression | gzip |
| Backup file | `catering_management_db_activity6.dump` |
| Backup size | 34,024 bytes |
| PostgreSQL version | 18.6 |

## 3. Backup Procedure

The backup was created using:

```powershell
pg_dump -U postgres -Fc -d catering_management_db -f catering_management_db_activity6.dump
```

The backup contents were inspected using:

```powershell
pg_restore -l catering_management_db_activity6.dump
```

## 4. Recovery Procedure

A separate recovery database named `catering_recovery_test` was used.

```powershell
createdb -U postgres catering_recovery_test
pg_restore -U postgres -d catering_recovery_test catering_management_db_activity6.dump
```

The restored database was checked for tables, records, constraints, database accessibility, and version compatibility.

## 5. Integrity Verification

SHA256 checksum:

```text
40B79369F92A278292C9E5ABF4FDFD8FDC0C73E7C6BB61EC3982462D21E17DD5
```

## 6. Recovery Strategy

The backup can be restored into a separate PostgreSQL database for verification or recovery. The original database should remain untouched until the restored database has been checked.

## 7. Result

**SUCCESS** - Backup creation, inspection, integrity recording, and recovery testing were completed successfully.