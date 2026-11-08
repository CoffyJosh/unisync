# ReserveIT Backup and Recovery Report

## 1. Backup Details

| Item | Result |
|---|---|
| Source Database | `ReserveIT` |
| PostgreSQL Version | 18.6 |
| Backup Format | Custom |
| Backup File | `reserveit_activity6.dump` |
| Backup Location | System temporary directory |
| File Size | 39,921 bytes |
| Created | November 8, 2026, 4:42:59 PM |
| SHA-256 | `DD042F5ABB12F21B94C400C7293DA0BE9E60D15E1D4AABA63A5D312F49A8B5C7` |

The backup completed successfully. Archive inspection confirmed the presence of table definitions, table data, sequences, constraints, and indexes.

## 2. Recovery Test

A separate database named `reserveit_recovery_test` was created.

The custom-format backup was restored into this database using `pg_restore`. The restore completed without reported errors, and the original database was not used as the restore destination.

## 3. Restored Data Verification

| Table | Restored Rows |
|---|---:|
| app_user | 3 |
| department | 5 |
| material | 6 |
| penalty | 1 |
| reservation | 4 |
| reservation_material | 10 |
| room | 6 |
| staff | 2 |
| **Total** | **37** |

## 4. Schema and Index Verification

| Object | Verified Count |
|---|---:|
| Tables | 8 |
| Primary Keys | 8 |
| Foreign Keys | 7 |
| Unique Constraints | 6 |
| Check Constraints | 20 |
| Indexes | 19 |

The restored database retained its table structures, constraints, and indexes.

## 5. Privilege Verification

The restored database contained the expected table grants:

- `app_admin_grp`: 32 table privileges.
- `app_readonly_grp`: 6 table privileges.
- `app_runtime_grp`: 18 table privileges.
- `reserveit_owner`: 56 table privileges.

The expected login-role memberships were also present:

- `reserveit_app` belongs to `app_runtime_grp`.
- `reserveit_reporter` belongs to `app_readonly_grp`.
- `reserveit_dba` belongs to `app_admin_grp`.

These role definitions and memberships are cluster-wide PostgreSQL objects, not objects stored inside an individual database backup.

The `reserveit_app` account successfully connected to the restored database and retrieved 4 reservation records.

## 6. Finding: Column-Level UPDATE Privileges

The runtime group has column-level UPDATE privileges on the following `room` columns:

- `room_name`
- `building`
- `capacity`
- `room_type`
- `status`

The `room_id` column does not have an UPDATE grant.

Although the runtime account does not have table-level UPDATE permission, it can update the five individually granted columns.

The same five column-level grants were found in both the original and restored databases. This confirms that the backup preserved the existing privilege configuration. It is a pre-existing least-privilege finding, not a restore failure.

A same-value UPDATE test affected one room record but assigned its existing value back to itself. No intentional data change was made.

## 7. Conclusion

The custom-format backup was successfully created, inspected, and restored into a separate recovery database.

All 8 tables, 37 records, constraints, indexes, group-role grants, and application account access were verified.

The existing column-level UPDATE grants on the `room` table were documented for review. The original database remained preserved.

**Recovery Result: Success**