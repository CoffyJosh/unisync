# Group 04 – HealthBridge Clinic Management System
## Activity 5: Database Maintenance and Monitoring

### 1. Database Health
- Database: `grp04_projectdb`
- PostgreSQL: 18.6
- Account: `hb_app`
- Database size: 8,838 kB
- Tables: 11
- Active sessions: 1
- Result: Accessible and operational during inspection

### 2. Log Review
Reviewed PostgreSQL logs from November 6, 2026.

- Failed authentication attempts involving `dropboxed_backup` and `dropboxed_report`.
- Permission-denied UPDATE attempts involving `dropboxed.complaints`.
- Normal checkpoint activity was recorded.

These entries involved other database accounts and schemas, not confirmed HealthBridge errors.

### 3. Database Maintenance
Checked maintenance privileges across all 11 application tables.

- `hb_app` had no MAINTAIN privileges.
- ANALYZE and VACUUM were not executed.
- Recommendation: Use a separately authorized maintenance account.

### 4. Query Performance
Inspected the actual doctor-list query from `backend/get_doctors.php`.

Result: Hash Right Join with sequential scans on `doctors` and `specialties`.

The planner estimated 140 output rows. Sequential scans were considered reasonable based on the available plan; no immediate index changes were recommended.

### 5. Overall Assessment
Database monitoring, log inspection, and query-plan analysis were completed. The main limitation was the absence of MAINTAIN privileges for `hb_app`, preventing maintenance execution.
