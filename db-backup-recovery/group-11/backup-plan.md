# ReserveIT Backup Plan

## 1. Objective

Create a PostgreSQL backup of the ReserveIT database and verify that it can be restored into a separate recovery database without modifying the original database.

## 2. Database Information

- **Database:** `ReserveIT`
- **PostgreSQL Version:** 18.6
- **Host:** `localhost`
- **Port:** `5432`
- **Backup Account:** `reserveit_owner`
- **Backup Format:** Custom (`pg_dump -F c`)

## 3. Backup Procedure

Create a custom-format backup using:

```powershell
pg_dump -U reserveit_owner -h localhost -p 5432 -F c -v -f "$env:TEMP\reserveit_activity6.dump" ReserveIT
```

Inspect the backup archive:

```powershell
pg_restore -l "$env:TEMP\reserveit_activity6.dump"
```

Verify the backup file size and checksum:

```powershell
Get-Item "$env:TEMP\reserveit_activity6.dump"
Get-FileHash "$env:TEMP\reserveit_activity6.dump" -Algorithm SHA256
```

## 4. Recovery Procedure

Create a separate recovery database:

```powershell
psql -U postgres -h localhost -p 5432 -d postgres -c "CREATE DATABASE reserveit_recovery_test;"
```

Restore the backup into the recovery database:

```powershell
pg_restore -U postgres -h localhost -p 5432 -d reserveit_recovery_test -v "$env:TEMP\reserveit_activity6.dump"
```

The original `ReserveIT` database must remain untouched during recovery testing.

## 5. Verification

After restoration, verify:

- Restored table and record counts.
- Primary keys, foreign keys, unique constraints, and check constraints.
- Indexes and table privileges.
- Login-role memberships.
- Application account access to restored data.
- Column-level privileges against the original database.

## 6. Backup Handling

Keep the backup outside the Git repository. Store backups in a protected location, restrict access to authorized database personnel, and retain a separate copy according to the project's backup retention policy.