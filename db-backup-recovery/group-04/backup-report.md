# Group 04 - HealthBridge Clinic Management System
## Activity 6: Backup and Recovery Report

### 1. Backup Information
- Database: `grp04_projectdb`
- PostgreSQL Version: 18.6
- Format: Custom
- Backup File: `grp04_projectdb_backup.dump`
- Archive TOC Entries: 132
- Result: Successful

### 2. Archive Inspection
The backup archive was inspected using `pg_restore --list`.

The archive included:
- 11 tables and their data
- 11 sequences
- 2 views
- Primary keys, unique constraints, and foreign keys
- Indexes
- The `pgcrypto` extension
- Database access-control entries

### 3. Restoration
The backup was restored into a separate database named `grp04_projectdb_restore_test`.

The original database was not modified.

Restoration completed without reported errors.

### 4. Verification Results

| Verification | Result |
|---|---|
| Restored tables | 11 |
| Original and restored row counts | Matching across all 11 tables |
| Total constraints | 90 |
| Archive inspection | Successful |
| Database restoration | Successful |

### 5. Data Verification

| Table | Restored Records |
|---|---:|
| specialties | 4 |
| doctors | 3 |
| patients | 2 |
| appointments | 4 |
| medical_notes | 2 |
| prescriptions | 2 |
| billing | 0 |
| staff_users | 5 |
| staff_password_resets | 0 |
| patient_password_resets | 0 |
| deleted_records | 0 |

All verified row counts matched the original database.

### 6. Limitations
- Application-level connection to the restored database was not tested.
- Individual application-role permissions were not independently retested during restoration.
- PostgreSQL roles are cluster-level objects and require separate management.
- No destructive operation was performed on the original database.

### 7. Conclusion
The custom-format backup was successfully created, inspected, and restored into a separate test database. All 11 tables were recovered, and row counts matched the original database. The restoration process was successful without affecting the original database.