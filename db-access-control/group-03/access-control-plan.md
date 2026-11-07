
# Group 03 – Pawpaw Clinic
## Activity 4: Database Access Control Plan

### Database Information
- Database: `group3_pawpaw`
- DBMS: PostgreSQL 18.6
- Schema: `public`
- Database owner: `postgres`
- Backend: Node.js, Express, and node-postgres (`pg`)

### Database Objects
- 14 application tables
- 12 identity sequences
- Existing application connection initially used `postgres`.

### Access-Control Roles

| Role | Type | Purpose |
|---|---|---|
| `app_runtime_grp` | NOLOGIN | Group role for routine application access |
| `app_admin_grp` | NOLOGIN | Reserved elevated group role; no privileges assigned |
| `pawpaw_app` | LOGIN | Restricted backend database account |

`pawpaw_app` is a member of `app_runtime_grp`.

### Assigned Permissions
- Database CONNECT for `pawpaw_app`.
- Schema USAGE for `app_runtime_grp`.
- SELECT, INSERT, UPDATE, DELETE on existing tables.
- USAGE and SELECT on existing sequences.
- Default privileges for future tables and sequences created by `postgres`.

The runtime account does not receive schema CREATE, TRUNCATE, SUPERUSER, CREATEDB, or CREATEROLE privileges.

### Application Role Considerations
Pawpaw uses a shared PostgreSQL connection pool. PostgreSQL sees the shared database account rather than individual application users.

Administrator, Staff, Veterinarian, and Pet Owner permissions therefore remain enforced by the application backend. The shared runtime account is not assigned the elevated `app_admin_grp`.

### Implementation and Verification Results

| Verification | Actual Result |
|---|---|
| Role creation | Successful |
| Runtime role membership | Granted |
| Database connection | Successful |
| Schema USAGE | Allowed |
| SELECT on user_account | Allowed |
| TRUNCATE on user_account | Denied |
| CREATE TABLE in public schema | Denied |
| Login as pawpaw_app | Successful |

### Findings
The restricted account successfully connected to `group3_pawpaw`. Basic permission checks confirmed that it can access the schema and read user accounts but cannot truncate tables or create tables in the public schema.

The initial attempt to grant CONNECT before creating `pawpaw_app` failed. The role was subsequently created and the grant succeeded.

PUBLIC database CONNECT and TEMPORARY privileges were restored after an initial revocation.

### Conclusion
Basic database access-control implementation and verification were successful. The runtime account has restricted access instead of requiring the PostgreSQL superuser account.
