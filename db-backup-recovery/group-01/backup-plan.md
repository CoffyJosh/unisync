# Group 01 - DropBoxed
## Activity 6: Backup and Recovery Plan

- **Database:** `dropboxed_db`
- **PostgreSQL:** 18.6
- **Backup account:** `dropboxed_backup`
- **Backup method:** `pg_dump` using custom archive format (`-Fc`).
- **Backup storage:** Protected local folder outside the UniSync repository.
- **Access:** Restricted to authorized backup and DBA accounts.

### Backup Verification
Inspect the archive using `pg_restore --list`, then restore it into a separate test database. Verify tables, records, constraints, permissions, and sample queries.

### Proposed Backup Schedule
- Daily backups.
- Keep 7 daily backups and 4 weekly backups.
- Store backups securely and remove expired copies according to the retention policy.

### Recovery Objectives
- **RPO:** 24 hours maximum acceptable data loss.
- **RTO:** 4 hours maximum target restoration time.

### Role Recreation
Recreate the Activity 4 login roles, group roles, memberships, and database privileges using the project's access-control documentation or setup scripts. PostgreSQL roles are cluster-wide and are not included in a single-database dump.

### Safety
The original `dropboxed_db` will remain untouched. All restoration and destructive recovery tests will use separate disposable databases. Backup files will not be committed to Git.