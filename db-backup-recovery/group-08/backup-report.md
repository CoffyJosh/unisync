# Database Backup and Recovery Report

**Project:** Group 08 - Catering Business Management System  
**Database:** `catering_management_db`  
**Date:** November 8, 2026

## 1. Backup Creation

A PostgreSQL custom-format backup was successfully created.

| Metric | Result |
|---|---|
| Backup filename | `catering_management_db_activity6.dump` |
| File size | 34,024 bytes |
| Format | Custom |
| Compression | gzip |
| TOC entries | 93 |
| PostgreSQL version | 18.6 |

## 2. Backup Contents

Inspection using `pg_restore -l` confirmed that the backup included:

- 12 database tables
- Table data
- Sequences and sequence values
- Primary keys and unique constraints
- Foreign keys
- Indexes
- Schema permissions

## 3. Recovery Test

The backup was restored into the separate database `catering_recovery_test`.

| Verification | Observed Result |
|---|---|
| Restored tables | 12 |
| Admin records | 1 |
| Cancellation records | 0 |
| Customer records | 4 |
| Employee records | 3 |
| Event assignment records | 3 |
| Inventory records | 4 |
| Menu item records | 3 |
| Package records | 3 |
| Payment records | 0 |
| Reservation records | 4 |
| Reservation menu records | 6 |
| Session records | 0 |

All restored table counts matched the original database's recorded counts.

## 4. Constraint Verification

| Constraint Type | Count |
|---|---:|
| Primary keys | 12 |
| Foreign keys | 8 |
| Unique constraints | 3 |
| Check constraints | 8 |
| Not-null constraints | 50 |
| **Total** | **81** |

## 5. Restored Database Verification

| Metric | Result |
|---|---|
| Database | `catering_recovery_test` |
| Database size | 8646 kB |
| PostgreSQL version | 18.6 |
| Connection | Successful |

## 6. Backup Checksum

**Algorithm:** SHA256

```text
40B79369F92A278292C9E5ABF4FDFD8FDC0C73E7C6BB61EC3982462D21E17DD5
```

## 7. Findings

- Backup creation completed without reported errors.
- Backup contents were successfully inspected.
- All 12 tables and their recorded data were restored.
- Restored constraints were verified.
- The recovery database was accessible and running PostgreSQL 18.6.
- The original database was not modified during recovery testing.

## 8. Result

**SUCCESS** - The database backup was successfully created and restored into a separate recovery database. Table counts, constraints, and database accessibility were verified.