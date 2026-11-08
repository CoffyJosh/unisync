**# Group 07 – Applicant Management System**

**## Activity 5: Database Maintenance and Monitoring**

## 1. Environment and Database Information

- Database: `grp07_applicantdb`
- PostgreSQL Version: 18.6
- Schema: `public`
- Database Owner: `postgres`
- Inspection Account: `postgres`
- Database Platform: Local Windows PostgreSQL installation

The inspection was performed using the PostgreSQL database owner account. The application-specific role was not used because Activity 4 inspection found no effective privileges for the inspected group roles on `public.applicant`.

## 2. Activity 4 Role Review

Existing roles:

- `app_runtime_grp`
- `app_admin_grp`
- `app_readonly_grp`

All three are NOLOGIN roles.

Existing memberships included `app_admin_grp`, `pawpaw_app`, and `app_runtime` as members of `app_runtime_grp`.

No roles or permissions were modified during this activity.

## 3. Database Health and Activity

- Nine application tables were inspected.
- The observed table statistics showed zero dead rows.
- Twenty-two indexes were identified.
- One database connection was visible during the inspection.
- The visible active session belonged to the inspection account and was executing the monitoring command.

The connection count and activity output represent a single inspection snapshot.

## 4. PostgreSQL Log Review

The latest available PostgreSQL log file was inspected for FATAL, ERROR, and WARNING entries.

The log contained:

- Authentication failures involving `gym_viewer`, `gym_app`, and `gym_admin`.
- A permission-denied UPDATE involving a table named `members`.
- Two autovacuum worker warnings.

These entries could not be attributed to `grp07_applicantdb` because the configured log prefix only records timestamps and does not identify the database.

No Group 07-specific application error was confirmed from the available log evidence.

## 5. Maintenance Performed

Executed:

- `ANALYZE VERBOSE;`
- `VACUUM (VERBOSE, ANALYZE);`

The database-wide VACUUM operation produced table processing and completion messages. The inspected output showed no PostgreSQL ERROR or FATAL messages.

The operations refreshed planner statistics and performed routine vacuum maintenance. No schema changes or application data modifications were made.

## 6. Query Performance Analysis

Query inspected:

```sql
SELECT *
FROM applicant
WHERE email = 'applicant1@example.com';