# UniSync User Roles

This document separates two kinds of roles:

- **Application roles**: what a person can do after logging in to UniSync.
- **Database roles**: what a PostgreSQL account can do. These are the *intended* roles, to be implemented in Activity 4.

End users do **not** get their own PostgreSQL login. The application connects through a single runtime account, and permissions for each person are enforced by the application code.

## 1. Application Roles

### Account-level

| Role | Description |
|---|---|
| **Guest** | Not logged in. Can browse public pages. |
| **Student / Member** | Any logged-in account. Can join organizations and register for events. |

### Organization-level

Organization roles are stored per organization in the `MEMBERSHIP` table, so one account can be an Admin in one organization and a Member in another.

| Role | Description |
|---|---|
| **Member** | Regular member of an organization. |
| **Manager** | Runs events and attendees for the organization. |
| **Admin** | Administers the organization. The creator of an organization becomes its Admin. If the last Admin leaves, the role passes to another member (automatic succession). |

### Maintenance

| Role | Description |
|---|---|
| **System Administrator** | Database and record maintenance outside the application. Not a superuser. |
| **Viewer / Auditor** | Reviews records and reports. Read-only. |
| **Application runtime (system account)** | The account PHP uses to connect to PostgreSQL. |

## 2. Functions and Data Access

| Function | Guest | Member | Manager | Admin |
|---|:---:|:---:|:---:|:---:|
| Browse organizations and events | Yes | Yes | Yes | Yes |
| Register an account / log in | Yes | n/a | n/a | n/a |
| Create an organization | No | Yes | Yes | Yes |
| Join / leave an organization | No | Yes | Yes | Yes |
| Register for / cancel an event registration | No | Yes | Yes | Yes |
| View own dashboard and registrations | No | Yes | Yes | Yes |
| Create, edit, delete events and schedules | No | No | Yes | Yes |
| View event attendees and remove them | No | No | Yes | Yes |
| Remove regular Members | No | No | Yes | Yes |
| Change member roles | No | No | No | Yes |
| Remove any member | No | No | No | Yes |
| Edit / delete the organization | No | No | No | Yes |

Notes:

- Manager and Admin permissions apply only to the organization where the person holds that role.
- Deleting an organization is a soft delete: the organization and its events are marked `deleted`, not removed.

## 3. Mapping to Intended Database Roles

| Application role | Intended database role | Notes |
|---|---|---|
| Guest | `unisync_runtime_grp` (via runtime account) | No database login of their own. |
| Student / Member | `unisync_runtime_grp` | Through the runtime account. |
| Organization Manager | `unisync_runtime_grp` | Through the runtime account. Extra permissions are enforced by application code. |
| Organization Admin | `unisync_runtime_grp` | Through the runtime account. Extra permissions are enforced by application code. |
| Application runtime (system account) | `unisync_runtime_grp` | SELECT, INSERT, UPDATE on application tables; no DDL. |
| System Administrator | `unisync_admin_grp` | Administrative access to UniSync objects, not a superuser. |
| Viewer / Auditor | `unisync_readonly_grp` | SELECT only on approved tables and views. |

Guests, Members, Managers and Admins share one database role because the differences between them are application-level rules (checked against `MEMBERSHIP`), not database privileges.

Database users, group roles, and privileges are documented in [`database-roles.md`](database-roles.md).