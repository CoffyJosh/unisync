# Group 04 – HealthBridge Clinic Management System
## Activity 4: Database Access Control Plan

### Database Information
- Database: `grp04_projectdb`
- DBMS: PostgreSQL 18
- Schema: `public`
- Backend: PHP 8, Apache, and PostgreSQL
- Application database account: `hb_app`

### Database Objects
- Database: `grp04_projectdb`
- Schema: `public`
- Application tables and sequences are defined in the project's database scripts.
- Existing access-control configuration was found in `database/roles.sql`.

### Access-Control Roles

| Role | Type | Purpose |
|---|---|---|
| `hb_admin` | NOLOGIN | Group role intended for administrative database access |
| `hb_staff` | NOLOGIN | Group role for staff-level clinical data access |
| `hb_app` | LOGIN | Backend database account used by the PHP application |

`hb_app` is a member of `hb_staff`.

### Assigned Permissions
- Database CONNECT for `hb_admin`, `hb_staff`, and `hb_app`.
- Schema USAGE for the three roles.
- `hb_admin` receives all privileges on existing tables and sequences.
- `hb_staff` receives SELECT, INSERT, and UPDATE on patients, appointments, medical notes, and prescriptions.
- `hb_staff` receives SELECT on doctors and specialties.
- `hb_staff` receives DELETE on patients, appointments, medical notes, and prescriptions.
- `hb_staff` receives access to the relevant staff login, password-reset, and deleted-record tables.
- `hb_staff` receives USAGE and SELECT on applicable sequences.
- `hb_app` inherits the permissions assigned to `hb_staff`.

The application account does not have database creation privileges. Its access to doctor records is read-only.

### Application Role Considerations
HealthBridge has Patient, Staff, Doctor, and Admin application roles.

The application uses `hb_app` as its PostgreSQL connection account. The application's Admin role is separate from the PostgreSQL group role `hb_admin`.

The database account `hb_app` inherits `hb_staff`; therefore, its database permissions remain limited to the privileges assigned to that group role.

### Implementation and Verification Results

| Verification | Actual Result |
|---|---|
| Existing role inspection | Successful |
| `hb_app` membership in `hb_staff` | Confirmed |
| Database CONNECT | Allowed |
| Database CREATE | Denied |
| SELECT on doctors | Allowed |
| INSERT on doctors | Denied |
| UPDATE on doctors | Denied |
| DELETE on doctors | Denied |
| Sequence USAGE | Allowed |
| Sequence SELECT | Allowed |
| Actual UPDATE attempt on doctors | Denied as expected |

### Findings
The existing HealthBridge access-control configuration follows the principle of least privilege for the tested application account.

The `hb_app` account can connect to `grp04_projectdb` and retrieve doctor records but cannot directly insert, update, or delete doctor records.

An actual UPDATE attempt using `hb_app` returned:

`ERROR: permission denied for table doctors`

The account also cannot create databases. Its doctor ID sequence privileges were confirmed as allowed.

### Conclusion
The existing database access-control configuration and the tested restrictions were successful. The application account uses the restricted `hb_staff` group role rather than a PostgreSQL superuser account.

The verification was limited to the roles, database privileges, doctor table permissions, and sequence permissions tested during this activity. It does not constitute a complete security audit of every database object.