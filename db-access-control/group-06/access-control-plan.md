# Group 06 – ByaheRentals Car Rental Management System

## Activity 4: Database Access Control Plan

### Database Information

- Database: `car_rental_db`
- DBMS: PostgreSQL 18.6
- Schema: `public`
- Backend: Java Spring Boot and PostgreSQL
- Application database account: `app_runtime`

### Database Objects

- Database: `car_rental_db`
- Schema: `public`
- Total tables: 17
- Existing access-control configuration: `Database/db_roles_and_grants.sql`

### Access-Control Roles

| Role | Type | Purpose |
|---|---|---|
| `app_admin_grp` | NOLOGIN | Group role intended for administrative database access |
| `app_runtime_grp` | NOLOGIN | Group role for application database operations |
| `app_readonly_grp` | NOLOGIN | Group role intended for read-only database access |
| `app_runtime` | LOGIN | Backend database account used by the Spring Boot application |

Verified role memberships:

- `app_runtime` is a member of `app_runtime_grp`.
- `app_admin_grp` is also a member of `app_runtime_grp`.
- No membership was returned for `app_readonly_grp`.

### Assigned Permissions

- Database CONNECT is granted to the three group roles.
- Schema USAGE is granted to the three group roles.
- `app_admin_grp` receives full privileges on existing tables.
- `app_runtime_grp` receives SELECT, INSERT, and UPDATE on existing tables.
- UPDATE and DELETE are revoked from `app_runtime_grp` on `audit_log`.
- DELETE is explicitly granted to `app_runtime_grp` on `vehicle`.
- `app_runtime_grp` receives USAGE and SELECT on applicable sequences.
- `app_readonly_grp` receives SELECT access to 15 tables.
- `app_runtime` inherits permissions from `app_runtime_grp`.
- `app_admin_grp` inherits permissions from `app_runtime_grp`.

### Implementation and Verification Results

| Verification | Actual Result |
|---|---|
| Existing role inspection | Successful |
| `app_runtime` membership | Confirmed |
| `app_admin_grp` membership | Confirmed |
| `app_readonly_grp` membership | No member returned |
| Runtime SELECT on customer | Allowed |
| Runtime INSERT