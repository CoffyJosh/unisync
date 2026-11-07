# Group 06 - ByaheRentals
## Activity 6: Database Backup and Recovery Report

### 1. Backup Creation

A full custom-format backup of `car_rental_db` was created using `pg_dump`.

**Backup Details:**

| Item | Result |
|---|---|
| Database | `car_rental_db` |
| Backup File | `car_rental_db_activity6.dump` |
| Backup Format | Custom (`-Fc`) |
| Backup Size | 80,163 bytes |
| Backup Date | November 7, 2026 |
| Backup Time | 3:34:11 PM |
| SHA-256 | `657CA412587141F1A6D9CDDF8F5EDB7B2127BC3D63A0AEAC9711B7E939533F7E` |
| Storage | `D:\DB_Backups\Group06\` |

The archive was inspected using `pg_restore --list`.

The inspection showed:
- 220 TOC entries.
- 17 tables and their data.
- Sequences and sequence values.
- Primary keys, foreign keys, unique constraints, and indexes.
- The `btree_gist` extension.
- Database object permissions.

### 2. Database Restoration

The backup was restored into a separate database:

`car_rental_db_restore_test`

The restoration completed successfully.

**Verification Results:**

| Verification | Result |
|---|---:|
| Restored public tables | 17 |
| Customers | 4 |
| Vehicles | 8 |
| Rentals | 1 |
| Primary key constraints | 17 |
| Foreign key constraints | 24 |

The rental table also retained its six listed indexes and constraints:

- `excl_rental_no_overlap`
- `idx_rental_customer`
- `idx_rental_dates`
- `idx_rental_status`
- `idx_rental_vehicle`
- `rental_pkey`

### 3. Access Control Verification

The four Activity 4 roles were verified after restoration.

| Role | Login |
|---|---|
| `app_admin_grp` | NOLOGIN |
| `app_runtime_grp` | NOLOGIN |
| `app_readonly_grp` | NOLOGIN |
| `app_runtime` | LOGIN |

All four roles had superuser and database-creation privileges disabled.

The `app_runtime_grp` role retained SELECT, INSERT, and UPDATE permissions on the `customer` table. The `app_readonly_grp` role had no listed permissions on that table, consistent with the original access restrictions.

The `app_runtime` account also successfully connected to the restored database and executed representative SELECT queries:

| Query | Result |
|---|---:|
| Customers | 4 |
| Vehicles | 8 |

The roles were already present in the same PostgreSQL instance. Recreating roles on a separate server was not tested.

### 4. Recovery Simulation

A disposable table named `activity6_recovery_test` was created in `car_rental_db_restore_test`.

A test record was inserted:

`Activity 6 recovery simulation record`

A separate recovery-point backup was created before deleting the record.

**Recovery-point Backup Details:**

| Item | Result |
|---|---|
| Backup File | `car_rental_db_recovery_point.dump` |
| Backup Size | 82,850 bytes |
| Backup Date | November 7, 2026 |
| Backup Time | 3:37:16 PM |
| SHA-256 | `4B87960CDFC37E2B8685B60DF8AD8B459BF2F4CA879A46975F1CD1A5531E7647` |

The test record was then deleted from `car_rental_db_restore_test`.

The recovery-point backup was restored into a second separate database:

`car_rental_db_recovery_test`

**Recovery Verification:**

| Check | Result |
|---|---|
| Record before deletion | 1 |
| Record after deletion | 0 |
| Record after recovery | 1 |
| Recovery result | Successful |

The deleted test record was successfully recovered from the backup.

### 5. Original Database Integrity

The original `car_rental_db` was checked after the restoration and recovery tests.

| Table | Original Record Count |
|---|---:|
| customer | 4 |
| vehicle | 8 |
| rental | 1 |

The original database remained intact throughout the activity.

### 6. Evaluation and Recommendations

The custom-format backup was successfully created and inspected. Restoration into a separate database preserved the tested database objects, records, constraints, indexes, and access permissions.

The controlled recovery simulation also demonstrated that a deleted test record could be recovered from a previously created backup.

Recommendations:

- Implement the proposed daily backup schedule and retention policy.
- Keep backup files in protected storage outside the project repository.
- Regularly verify backup checksums and perform test restorations.
- Recreate required PostgreSQL roles and memberships when restoring to another server.
- Record actual recovery times during future recovery drills.

### 7. Limitations

- The backup schedule and retention policy were proposed but not automated.
- RPO and RTO were proposed targets; actual recovery time was not measured.
- Role recreation on a separate PostgreSQL server was not tested.
- The backup and recovery operations used the local PostgreSQL administrator account.
- Both backup files were stored locally rather than in a separate off-site location.

### 8. Conclusion

Activity 6 was successfully completed. The original database was backed up, the archive was inspected, and the backup was restored into a separate test database. Database objects, records, constraints, indexes, and access permissions were verified. A controlled data-loss simulation also confirmed successful recovery of the deleted test record without affecting the original database.