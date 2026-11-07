# Group 06 – ByaheRentals
## Activity 5: Database Maintenance and Monitoring

### 1. Database Health
- Database: `car_rental_db`
- PostgreSQL: 18.6
- Account: `app_runtime`
- Database size: 9,742 kB
- Tables: 17
- Connections: 11 (10 idle, 1 active inspection session)
- Result: Accessible and operational during inspection

### 2. Log Review
Checked PostgreSQL logging configuration.

- `logging_collector`: ON
- `log_destination`: stderr
- Access to `log_directory` and `log_filename` was denied.
- Actual PostgreSQL log files could not be inspected because `app_runtime` lacks `pg_read_all_settings`.

No conclusions about failed logins or SQL errors could be made from the available evidence.

### 3. Database Maintenance
Checked table statistics and attempted maintenance on `public.customer`.

- ANALYZE was skipped due to insufficient permissions.
- VACUUM was skipped due to insufficient permissions.
- Both operations require an appropriately authorized maintenance account.

Recommendation: Use a separately authorized maintenance account to perform routine maintenance.

### 4. Query Performance
Analyzed a customer rental lookup using `public.rental`.

Result: Index Scan using `idx_rental_customer`, followed by a Sort on `pickup_datetime DESC`.

The planner estimated 1 output row with a total cost of 8.18. The existing index supports the customer lookup, and the estimated sorting cost is low.

No immediate index changes were recommended.

### 5. Overall Assessment
Database health, activity, table statistics, and Activity 4 role memberships were inspected. Query-plan analysis was completed successfully.

The main limitations were the lack of maintenance permissions for `app_runtime` and the inability to inspect actual PostgreSQL log files. An authorized maintenance account and administrator-assisted log review are recommended.