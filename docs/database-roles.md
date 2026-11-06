# UniSync Database Roles and Privileges

> **Status: intended design.** These roles are documented for Activity 3 and will be created and tested in Activity 4. Until then, local setups connect with the account configured in `.env` (for example `postgres`). Do not commit real passwords.

## 1. Approach

- PostgreSQL privileges are given to **group roles** (`NOLOGIN`). Login accounts join a group and inherit its privileges.
- End users of UniSync do **not** get PostgreSQL logins. The application connects through one runtime account.
- Who may do what inside the application (Admin, Manager, Member) is decided by the application, using the `MEMBERSHIP` table. See [`user-roles.md`](user-roles.md).
- No role is a superuser.

## 2. Group Roles

| Group role | Purpose | Intended privileges |
|---|---|---|
| `unisync_admin_grp` | Database and record maintenance outside the app | All privileges on UniSync tables and sequences; CREATE on the schema. Not a superuser. |
| `unisync_runtime_grp` | What the running application needs | SELECT, INSERT, UPDATE on application tables; USAGE and SELECT on sequences. No DELETE, no DDL. |
| `unisync_readonly_grp` | Reviewing records and reports | SELECT only on approved tables; `ACCOUNT.password_hash` excluded. |

DELETE is withheld from the runtime group because UniSync uses soft deletes: rows are marked through their `status` column instead of being removed.

## 3. Login Accounts

| Login account | Member of | Used by | Password |
|---|---|---|---|
| `unisync_app` | `unisync_runtime_grp` | The PHP application (`DB_USER` in `.env`) | Placeholder, set locally |
| `unisync_dba` | `unisync_admin_grp` | Maintenance by group members | Placeholder, set locally |
| `unisync_auditor` | `unisync_readonly_grp` | Read-only review | Placeholder, set locally |

## 4. Application Role Mapping

| Application role | Database role |
|---|---|
| Guest, Student / Member, Organization Manager, Organization Admin | `unisync_runtime_grp` (through `unisync_app`) |
| Application runtime (system account) | `unisync_runtime_grp` |
| System Administrator | `unisync_admin_grp` |
| Viewer / Auditor | `unisync_readonly_grp` |

## 5. Privilege Matrix

| Table | Admin group | Runtime group | Read-only group |
|---|---|---|---|
| `ACCOUNT` | ALL | SELECT, INSERT, UPDATE | SELECT (all columns except `password_hash`) |
| `ORGANIZATION` | ALL | SELECT, INSERT, UPDATE | SELECT |
| `MEMBERSHIP` | ALL | SELECT, INSERT, UPDATE | SELECT |
| `EVENT` | ALL | SELECT, INSERT, UPDATE | SELECT |
| `VENUE` | ALL | SELECT, INSERT, UPDATE | SELECT |
| `EVENT_SCHEDULE` | ALL | SELECT, INSERT, UPDATE | SELECT |
| `REGISTRATION` | ALL | SELECT, INSERT, UPDATE | SELECT |
| `NOTIFICATION` | ALL | SELECT, INSERT, UPDATE | SELECT |
| Sequences | ALL | USAGE, SELECT | none |

## 6. Intended Setup SQL

Run as a PostgreSQL superuser, connected to `grp10_unisyncdb`, **after** `db/init.sql`. Replace the password placeholders with your own values and keep them out of the repository.

```sql
-- Group roles (no login)
CREATE ROLE unisync_admin_grp    NOLOGIN;
CREATE ROLE unisync_runtime_grp  NOLOGIN;
CREATE ROLE unisync_readonly_grp NOLOGIN;

-- Login accounts
CREATE ROLE unisync_app     LOGIN PASSWORD '<set_a_strong_password>' IN ROLE unisync_runtime_grp;
CREATE ROLE unisync_dba     LOGIN PASSWORD '<set_a_strong_password>' IN ROLE unisync_admin_grp;
CREATE ROLE unisync_auditor LOGIN PASSWORD '<set_a_strong_password>' IN ROLE unisync_readonly_grp;

-- Database and schema access
GRANT CONNECT ON DATABASE grp10_unisyncdb
    TO unisync_admin_grp, unisync_runtime_grp, unisync_readonly_grp;

REVOKE CREATE ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public
    TO unisync_admin_grp, unisync_runtime_grp, unisync_readonly_grp;
GRANT CREATE ON SCHEMA public TO unisync_admin_grp;

-- Admin group
GRANT ALL PRIVILEGES ON ALL TABLES    IN SCHEMA public TO unisync_admin_grp;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO unisync_admin_grp;

-- Runtime group (no DELETE, no DDL)
GRANT SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public TO unisync_runtime_grp;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO unisync_runtime_grp;

-- Read-only group (password_hash hidden)
GRANT SELECT ON
    organization, membership, event, venue,
    event_schedule, registration, notification
    TO unisync_readonly_grp;
GRANT SELECT (account_id, account_name, email, avatar_url, account_status)
    ON account TO unisync_readonly_grp;
```

## 7. Notes

- `db/init.sql` drops and recreates every table, which also removes the privileges granted on them. Re-run the grant statements in section 6 after each run of `init.sql`.
- Tables created by `init.sql` are owned by whoever ran it. If `unisync_app` reports `permission denied`, the grants above have not been applied yet.
- The application must not rely on DELETE. If a feature ever needs a hard delete, grant it on that one table instead of widening the runtime group.
- Further hardening (row-level rules, password policy, auditing) is outside Activity 3.