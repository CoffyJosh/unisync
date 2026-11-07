
# Group 05 - SHIORI Café
## Activity 4: Database Access Control Plan

### Database Information
- Database: `grp10_shioricafedb`
- Schema: `shioricafe_schema`
- DBMS: PostgreSQL
- Scope: Inspection of existing roles, memberships, and privileges.
- No database roles or permissions were created or modified.

### Existing Application Roles

| Role | Type | Observed Membership |
|---|---|---|
| app_runtime | LOGIN | app_runtime_grp |
| app_runtime_grp | NOLOGIN | None |
| app_admin_grp | NOLOGIN | app_runtime_grp |
| app_readonly_grp | NOLOGIN | No membership observed |

### Access Requirements

| Role | Intended Access |
|---|---|
| app_runtime | Application runtime operations |
| app_runtime_grp | Shared runtime permissions |
| app_admin_grp | Administrative application access |
| app_readonly_grp | Read-only access |

These are intended access descriptions based on role names, not verified functional permissions.

### Verification Findings

- Application roles did not have SUPERUSER, CREATEDB, or CREATEROLE privileges.
- The inspected roles had CONNECT and TEMP database privileges but lacked CREATE.
- `app_runtime` lacked USAGE and CREATE privileges on `shioricafe_schema`.
- All 18 inspected tables denied SELECT, INSERT, UPDATE, and DELETE for `app_runtime`.
- An actual runtime access test returned `permission denied for schema shioricafe_schema`.

### Conclusion

The existing role structure was present, but the runtime role lacked the schema and table privileges needed for normal database operations. Activity 4 verification resulted in an issue.

No permissions were modified during testing.
