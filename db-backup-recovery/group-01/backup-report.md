# Group 01 - DropBoxed
## Activity 6: Backup, Restoration, and Recovery Report

### 1. Backup Information

- **Database:** `dropboxed_db`
- **PostgreSQL Version:** 18.6
- **Backup Method:** `pg_dump` custom archive (`-Fc`)
- **Backup Date:** November 7, 2026
- **Backup File:** `dropboxed_db_activity6.dump`
- **File Size:** 50,491 bytes
- **SHA256:** `E359434911C860DF6D3EB13B94B728714657F0AD29316DDE2CAAC359C1239052`
- **Archive Verification:** Successful using `pg_restore --list`

### 2. Restoration Verification

Restored the backup into a separate database named `dropboxed_restore_test`.

| Verification | Result |
|---|---|
| Tables restored | 8/8 |
| Users | 11 |
| Staff | 7 |
| Complaints | 61 |
| Locations | 11 |
| Attachments | 2 |
| Complaint categories | 4 |
| Assignments | 62 |
| Status logs | 108 |
| Constraints | 72 |

**Result:** Successful. All expected tables, records, and constraints were restored.

### 3. Roles and Permissions

Verified the presence of all 8 DropBoxed roles, including the runtime, read-only, DBA, and backup group roles.

Restored table privileges were verified:

| Group Role | Privileges |
|---|---:|
| `dropboxed_backup_grp` | 8 |
| `dropboxed_dba_grp` | 32 |
| `dropboxed_readonly_grp` | 6 |
| `dropboxed_runtime_grp` | 18 |

Access tests using `dropboxed_report`:
- SELECT operation: Allowed.
- UPDATE operation: Denied as expected.

**Result:** Successful.

### 4. Controlled Recovery Simulation

A temporary category record was inserted into the restored test database:

- **Category ID:** 9
- **Category Name:** `ACTIVITY6_RECOVERY_TEST`

A separate recovery backup was created:

- **File:** `dropboxed_recovery_test.dump`
- **Size:** 49,887 bytes
- **SHA256:** `E9C6DA348D9437CB9A4A1F7A56E9FADAE410644D93A71B0FBB8D06F368DD1776`

The temporary record was deleted from the test database to simulate data loss. The recovery backup was then restored into a separate database named `dropboxed_recovery_test`.

The record was successfully recovered with its original ID and name.

**Result:** Successful. The recovery simulation confirmed that the backup could restore deleted test data.

### 5. Recovery Objectives

| Objective | Target |
|---|---|
| Recovery Point Objective (RPO) | 24 hours |
| Recovery Time Objective (RTO) | 4 hours |
| Backup Schedule | Daily |
| Retention | 7 daily and 4 weekly backups |

These are proposed recovery targets, not measured recovery times.

### 6. Overall Findings

The backup archive was created and verified successfully. Restoration preserved all 8 tables, their records, constraints, roles, and table privileges. Read-only access restrictions were also confirmed.

The controlled recovery simulation successfully recovered a deleted temporary record. The original `dropboxed_db` remained untouched throughout the activity.

**Overall Result: SUCCESS**