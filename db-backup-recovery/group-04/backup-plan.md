# Group 04 - HealthBridge Clinic Management System
## Activity 6: Backup and Recovery Plan

### Database Information
- Database: `grp04_projectdb`
- DBMS: PostgreSQL 18.6
- Backup Format: Custom (`pg_dump -F c`)
- Backup File: `grp04_projectdb_backup.dump`

### Backup Procedure
A custom-format backup is created using `pg_dump` with the PostgreSQL administrator account.

```powershell
pg_dump -U postgres -h localhost -d grp04_projectdb -F c -f "$env:TEMP\grp04_projectdb_backup.dump"
```

The backup is stored temporarily outside the project repository to avoid accidentally committing database data or credentials.

### Recovery Procedure
1. Create a separate test database.
2. Restore the backup using `pg_restore`.
3. Verify the restored tables and records.
4. Compare row counts against the original database.
5. Inspect database constraints and permissions.

### Backup Schedule and Retention
- Recommended frequency: Daily
- Recommended retention: 7 daily backups and 4 weekly backups.
- Recommended RPO: 24 hours.
- Recommended RTO: 4 hours.

### Security and Limitations
- Only authorized database administrators should access backup files.
- Backup files must not be committed to the public repository.
- PostgreSQL roles are cluster-level objects and must be managed separately from individual database backups.
- Restores should first be tested using a separate database to protect production data.