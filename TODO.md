# UniSync - Project Documentation & Codex Reference

## 1. Project Overview

**Official Project Title:**

UniSync: A University Event Scheduling and Registration System

**Project Type:** Web-based platform

**Primary Purpose:**

UniSync is a public, multi-university platform that allows students and organizations to discover, create, schedule, and register for university-related events.

The platform connects:
- Students looking for events to attend.
- Organizations that create and schedule events.
- Venue providers who want to list their venues for organizations to use.
- Platform administrators who oversee the system.

UniSync is not owned by one particular university. It is intended to support users and organizations from different universities.

### Core Concept

Organizations create and schedule events. Students browse available events and register for them. Organizations can manage their memberships and events, while venue providers can make their venues available for event scheduling.

The system uses a shared backend and database, with different interfaces depending on the user's role.

---

## 2. System Architecture

UniSync uses one central platform with three main interfaces.

### A. Main UniSync Portal

**Primary users:** Students and organization members.

This is the main public-facing interface.

Expected functionality:
- Browse available events.
- Search for events.
- View event details.
- Register for events.
- Create organizations.
- Join organizations.
- View organization information.
- View organization members.
- View organization events.
- Manage organization events according to membership permissions.
- Manage personal account settings.

### B. Venue Provider Portal

**Primary users:** Venue owners and authorized venue renters.

Venue providers should have a separate portal experience, but it should share the same backend and database as the main UniSync platform.

Expected functionality:
- Register as a venue provider.
- Log in to the venue provider portal.
- Submit venue information.
- Manage their venue listings.
- View venue listing status.
- Receive approval or rejection decisions from the Platform Admin.

Important design decisions:
- Venue providers should be able to self-register.
- Venue provider account verification and venue listing approval are separate processes.
- A provider's account can be approved while individual venue submissions are still pending.
- Do not assume that full online venue booking is already part of the project.

The exact rental functionality is still to be finalized. Do not implement a full booking marketplace without explicit instructions.

### C. Admin Portal

**Primary users:** Platform administrators.

The Platform Admin oversees the entire UniSync platform.

Expected responsibilities:
- Review and verify accounts.
- Review organization submissions if approval is required.
- Review venue provider registrations.
- Review venue listings.
- Manage platform records.
- Disable accounts when necessary.
- Oversee platform activity.

There is no separate University Admin role in the current design.

---

## 3. User Roles and Account Structure

### General Account

The old STUDENT table has been replaced by the ACCOUNT table.

An account represents a person's identity on UniSync. It is no longer exclusively a student record.

Current account information:
- Account ID
- Account name
- Email address
- Password hash
- Avatar URL
- Account status

### Students

Students use their general ACCOUNT identity to:
- Browse events.
- Register for events.
- Join organizations.
- Participate in organization activities.
- Receive notifications.

### Organization Members

An account can join an organization through the MEMBERSHIP table.

The membership role determines the user's permissions within that organization.

Current membership roles:
- Admin
- Manager
- Member

The membership table is separate from ACCOUNT because one account can belong to multiple organizations and can have different roles in each organization.

### Venue Providers

Venue providers are intended to have a separate portal.

They should not require a completely separate authentication system. The intended direction is to use the shared ACCOUNT system and associate provider-specific information with the account.

A dedicated VENUE_PROVIDER table has not yet been added to the current database.

### Platform Administrators

Platform administrators oversee UniSync globally.

The current eight-table schema does not yet include a dedicated administrator role or role-management table. This still needs to be designed.

Do not assume that organization membership roles are equivalent to global platform administrator permissions.

---

## 4. Important Account Rules

The ACCOUNT table has an `account_status` field.

Allowed values:

| Status | Meaning |
|---|---|
| active | Account can access the platform. |
| disabled | Account is restricted from accessing the platform. |
| deleted | Account is marked as deleted and cannot access the platform. |

Only accounts with `account_status = 'active'` should be allowed to log in.

This is important because simply having a valid email and password should not be enough to authenticate an account that has been disabled or deleted.

The login process should:
1. Find the account using its email.
2. Verify the password using `password_verify()`.
3. Check whether the account status is `active`.
4. Regenerate the session ID after successful authentication.
5. Store account information in the session.

Use `password_hash()` when creating or updating passwords.

Use `password_verify()` when authenticating passwords.

Never store plaintext passwords.

---

## 5. Database Structure

**Database:** `grp10_unisyncdb`

**Database Engine:** PostgreSQL 18.6

**Connection:** PHP PDO

The current database has exactly eight tables.

### Table 1: ACCOUNT

Stores the general identity and authentication information of UniSync users.

| Column | Type | Description |
|---|---|---|
| account_id | SERIAL PRIMARY KEY | Unique account identifier |
| account_name | VARCHAR(150) NOT NULL | User's display name |
| email | VARCHAR(255) NOT NULL UNIQUE | Account email |
| password_hash | TEXT NOT NULL | Hashed password |
| avatar_url | TEXT | Optional profile image URL |
| account_status | VARCHAR(30) NOT NULL DEFAULT 'active' | Account status |

Allowed account statuses:
- active
- disabled
- deleted

`avatar_url` is nullable. The frontend should display a default avatar when no image URL is available.

### Table 2: ORGANIZATION

Stores organizations registered on UniSync.

| Column | Type | Description |
|---|---|---|
| organization_id | SERIAL PRIMARY KEY | Unique organization identifier |
| organization_name | VARCHAR(150) NOT NULL | Organization name |
| university_name | VARCHAR(150) NOT NULL | University associated with the organization |
| description | TEXT | Organization introduction |
| avatar_url | TEXT | Optional organization profile image |
| banner_url | TEXT | Optional organization banner |
| status | VARCHAR(30) NOT NULL DEFAULT 'active' | Organization status |

Allowed organization statuses:
- active
- inactive
- deleted

Both image URL fields are nullable.

Organizations are not restricted to a single university within UniSync.

### Table 3: MEMBERSHIP

Connects accounts to organizations.

| Column | Type | Description |
|---|---|---|
| membership_id | SERIAL PRIMARY KEY | Unique membership identifier |
| account_id | INTEGER NOT NULL | References ACCOUNT |
| organization_id | INTEGER NOT NULL | References ORGANIZATION |
| role | VARCHAR(20) NOT NULL DEFAULT 'Member' | Organization role |
| membership_date | DATE NOT NULL DEFAULT CURRENT_DATE | Date joined |
| status | VARCHAR(30) NOT NULL DEFAULT 'active' | Membership status |

Foreign keys:
- `account_id` references `ACCOUNT(account_id)` with ON DELETE CASCADE.
- `organization_id` references `ORGANIZATION(organization_id)` with ON DELETE CASCADE.

Unique constraint:
- `(account_id, organization_id)`

Allowed roles:
- Admin
- Manager
- Member

Allowed statuses:
- active
- inactive
- deleted

### Table 4: EVENT

Stores events created by organizations.

| Column | Type | Description |
|---|---|---|
| event_id | SERIAL PRIMARY KEY | Unique event identifier |
| organization_id | INTEGER NOT NULL | Organization that created the event |
| event_name | VARCHAR(200) NOT NULL | Event title |
| description | TEXT | Event description |
| event_image | TEXT | Optional event image |
| status | VARCHAR(20) NOT NULL DEFAULT 'pending' | Event status |

Foreign key:
- `organization_id` references `ORGANIZATION(organization_id)` with ON DELETE CASCADE.

Allowed event statuses:
- pending
- confirmed
- cancelled
- completed
- deleted

### Table 5: VENUE

Stores venues that can be used for events.

| Column | Type | Description |
|---|---|---|
| venue_id | SERIAL PRIMARY KEY | Unique venue identifier |
| venue_name | VARCHAR(150) NOT NULL UNIQUE | Venue name |
| location | TEXT NOT NULL | Venue address or location |
| capacity | INT | Maximum venue capacity |
| latitude | DECIMAL(10,7) | Optional latitude |
| longitude | DECIMAL(10,7) | Optional longitude |
| status | VARCHAR(30) NOT NULL DEFAULT 'active' | Venue status |

Constraints:
- Capacity must be greater than zero if provided.
- Latitude must be between -90 and 90 if provided.
- Longitude must be between -180 and 180 if provided.
- Latitude and longitude must either both be provided or both be NULL.

Allowed statuses:
- active
- inactive
- deleted

Important: The current VENUE table does not have an owner/provider foreign key. Venue provider ownership must be addressed in a future database update.

### Table 6: EVENT_SCHEDULE

Stores the scheduling information for events.

| Column | Type | Description |
|---|---|---|
| schedule_id | SERIAL PRIMARY KEY | Unique schedule identifier |
| event_id | INTEGER NOT NULL | Associated event |
| venue_id | INTEGER NOT NULL | Associated venue |
| event_date | DATE NOT NULL | Event date |
| start_time | TIME NOT NULL | Starting time |
| end_time | TIME NOT NULL | Ending time |
| status | VARCHAR(30) NOT NULL DEFAULT 'active' | Schedule status |

Foreign keys:
- `event_id` references `EVENT(event_id)` with ON DELETE CASCADE.
- `venue_id` references `VENUE(venue_id)`.

Constraint:
- `end_time` must be greater than `start_time`.

Allowed statuses:
- active
- cancelled
- deleted

### Table 7: REGISTRATION

Stores event registrations made by accounts.

| Column | Type | Description |
|---|---|---|
| registration_id | SERIAL PRIMARY KEY | Unique registration identifier |
| account_id | INTEGER NOT NULL | Registered account |
| event_id | INTEGER NOT NULL | Registered event |
| registration_date | TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP | Registration timestamp |
| status | VARCHAR(30) NOT NULL DEFAULT 'active' | Registration status |

Foreign keys:
- `account_id` references `ACCOUNT(account_id)` with ON DELETE CASCADE.
- `event_id` references `EVENT(event_id)` with ON DELETE CASCADE.

Unique constraint:
- `(account_id, event_id)`

Allowed statuses:
- active
- cancelled
- deleted

### Table 8: NOTIFICATION

Stores notifications sent to accounts.

| Column | Type | Description |
|---|---|---|
| notification_id | SERIAL PRIMARY KEY | Unique notification identifier |
| account_id | INTEGER NOT NULL | Notification recipient |
| organization_id | INTEGER NOT NULL | Related organization |
| event_id | INTEGER | Optional related event |
| notification_message | TEXT NOT NULL | Notification content |
| is_read | BOOLEAN NOT NULL DEFAULT FALSE | Read status |
| created_at | TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| status | VARCHAR(30) NOT NULL DEFAULT 'active' | Notification status |

Foreign keys:
- `account_id` references `ACCOUNT(account_id)` with ON DELETE CASCADE.
- `organization_id` references `ORGANIZATION(organization_id)` with ON DELETE CASCADE.
- `event_id` references `EVENT(event_id)` with ON DELETE SET NULL.

Allowed statuses:
- active
- deleted

---

## 6. Database Relationships

The current relationships are:

- ACCOUNT to MEMBERSHIP: One-to-many.
- ORGANIZATION to MEMBERSHIP: One-to-many.
- ORGANIZATION to EVENT: One-to-many.
- EVENT to EVENT_SCHEDULE: One-to-many.
- VENUE to EVENT_SCHEDULE: One-to-many.
- ACCOUNT to REGISTRATION: One-to-many.
- EVENT to REGISTRATION: One-to-many.
- ACCOUNT to NOTIFICATION: One-to-many.
- ORGANIZATION to NOTIFICATION: One-to-many.
- EVENT to NOTIFICATION: One-to-many, optional on the notification side.

Simplified relationship diagram:

```text
ACCOUNT
  |
  |---- MEMBERSHIP ---- ORGANIZATION
  |                         |
  |                         |---- EVENT
  |                                |
  |                                |---- EVENT_SCHEDULE ---- VENUE
  |                                |
  |                                |---- REGISTRATION
  |
  |---- REGISTRATION
  |
  |---- NOTIFICATION ---- ORGANIZATION
             |
             |---- EVENT (optional)
```

### Important Database Rules

- Use foreign keys to preserve relationships.
- Respect existing UNIQUE constraints.
- Respect CHECK constraints and allowed status values.
- Do not physically delete records when the intended behavior is soft deletion.
- Use the existing status fields for soft deletion where appropriate.
- Remember that ON DELETE CASCADE applies to physical deletion, not status changes.
- Use PostgreSQL-compatible SQL.
- Use `CURRENT_DATE` and `CURRENT_TIMESTAMP`, not MySQL-specific equivalents such as `CURDATE()`.

### Existing Indexes

The database initialization script creates:

- `idx_membership_organization` on MEMBERSHIP(organization_id)
- `idx_membership_account` on MEMBERSHIP(account_id)
- `idx_event_organization` on EVENT(organization_id)
- `idx_schedule_date_venue` on EVENT_SCHEDULE(event_date, venue_id)
- `idx_registration_event` on REGISTRATION(event_id)
- `idx_registration_account` on REGISTRATION(account_id)
- `idx_notification_account_created` on NOTIFICATION(account_id, created_at DESC)

---

## 7. Technology Stack

### Frontend
- HTML
- Tailwind CSS
- jQuery
- JavaScript

### Backend
- PHP

### Database
- PostgreSQL 18.6

### Database Connectivity
- PHP PDO

### Development Environment
- Windows
- XAMPP
- PHP
- PostgreSQL
- Local development environment

### Important Coding Preferences

- Use jQuery rather than vanilla JavaScript for existing frontend interactions.
- Use `$(document).ready()` when following the existing jQuery structure.
- Use `$.ajax()` for AJAX requests.
- Use prepared statements with PDO for database queries.
- Use `htmlspecialchars()` when displaying untrusted text in HTML.
- Use PHP's password hashing and verification functions.
- Preserve the existing Tailwind styling and layout unless explicitly asked to redesign it.

---

## 8. Project Folder Structure

The current project uses separate public-facing files, PHP backend files, and JavaScript files.

Expected structure:

```text
UniSync/
│
├── public/
│   ├── home.php
│   ├── browse.php
│   ├── login.php
│   ├── register.php
│   ├── settings.php
│   ├── organization.php
│   ├── event.php
│   └── ...
│
├── php/
│   ├── db.php
│   ├── components/
│   │   ├── navbar.php
│   │   └── footer.php
│   │
│   └── app/
│       ├── account/
│       │   ├── settings.php
│       │   └── ...
│       │
│       ├── organization/
│       │   ├── get_organization.php
│       │   └── ...
│       │
│       └── ...
│
├── javascript/
│   ├── jquery.js
│   ├── tailwind.js
│   │
│   └── app/
│       ├── settings.js
│       ├── organization.js
│       └── ...
│
└── ...
```

This is a reference structure, not a complete verified inventory of every file.

### Relative Paths

From a file inside `public/`:

- `../php/db.php` points to the PHP database connection.
- `../php/components/navbar.php` points to the navbar component.
- `../php/components/footer.php` points to the footer component.
- `../javascript/jquery.js` points to jQuery.
- `../javascript/tailwind.js` points to the Tailwind script.
- `../javascript/app/` contains page-specific JavaScript.

Use `__DIR__` for PHP include paths where appropriate.

---

## 9. Existing Authentication and Session Conventions

The new authentication system uses account-based session variables.

Current session variables:

```php
$_SESSION['account_id'];
$_SESSION['account_name'];
$_SESSION['email'];
```

Legacy session variable names (no longer used by the application):

```php
$_SESSION['student_id'];
$_SESSION['student_name'];
```

These names are listed for historical reference only. Application authentication and account-related foreign keys use `account_id` and `account_name`.

### Login Process

The current account login implementation:
- Checks whether an account is already logged in.
- Validates email format.
- Requires a password.
- Queries the ACCOUNT table.
- Verifies the password hash.
- Rejects accounts whose status is not `active`.
- Regenerates the session ID.
- Stores account information in the session.
- Returns JSON responses for AJAX login requests.

Do not remove the account status validation.

### Authentication Reminder

Pages and backend scripts that require an authenticated account should verify the account session.

Sensitive backend operations should not rely solely on frontend restrictions.

Where an operation changes account data or performs an authorized action, the backend must verify the current account and its relevant permissions.

---

## 10. Main User Workflows

### Student Workflow

1. Visit UniSync.
2. Browse or search for events.
3. Open an event's details.
4. Log in or register if authentication is required.
5. Register for the event.
6. View registration information.
7. Receive relevant notifications.

### Organization Workflow

1. Create an account.
2. Create an organization.
3. Join an organization or manage one the account created.
4. View organization information.
5. Manage members according to organization permissions.
6. Create events.
7. Assign schedules and venues.
8. Manage event information and registrations.

### Venue Provider Workflow

1. Open the Venue Provider entry point from the main UniSync website.
2. Register as a venue provider.
3. Wait for account verification or approval.
4. Access the Venue Provider portal after approval.
5. Submit venue listings.
6. Wait for individual venue listing review.
7. Manage approved listings.

The exact venue rental and booking process is not finalized.

### Platform Admin Workflow

1. Log in to the Admin portal.
2. Review submitted accounts and registrations.
3. Verify venue providers.
4. Review venue listings.
5. Manage platform records.
6. Disable accounts when necessary.
7. Monitor platform activity.

---

## 11. Current Implementation Progress

The following work has been established in the current development discussion.

### Database

- The ACCOUNT table replaces STUDENT.
- Account avatar URL has been added.
- Organization avatar and banner URL fields have been added.
- Image URL fields are nullable.
- MEMBERSHIP references ACCOUNT.
- REGISTRATION references ACCOUNT.
- NOTIFICATION references ACCOUNT.
- The current initialization script contains eight tables.

### PHP Files Already Updated or Discussed

#### Account Settings Backend

The settings backend has been migrated from:
- `student_id` to `account_id`
- `student_name` to `account_name`
- STUDENT table queries to ACCOUNT table queries
- Student session variables to account session variables

It retains the original AJAX behavior and password-update functionality.

#### Login Backend

The login backend has been migrated to ACCOUNT.

It also checks `account_status` and rejects disabled or deleted accounts.

#### Public Settings Page

The public settings page was updated to:
- Use `$account` instead of `$student`.
- Use `account_name` instead of `student_name`.
- Use account-related input IDs and form names.

The settings backend remains responsible for enforcing account access restrictions.

#### Public Organization Page

The public organization page was updated to:
- Use `$member['account_name']` instead of `$member['student_name']`.
- Use `$member['account_id']` instead of `$member['student_id']`.
- Use `$accountId` instead of `$studentId`.
- Change student-specific wording to account-specific wording.

Its backend dependency, `get_organization.php`, joins MEMBERSHIP to ACCOUNT and returns `account_id` and `account_name`.

The application now uses ACCOUNT for authentication, memberships, registrations, and account profile data. Student references that describe the platform's audience remain valid.

---

## 12. Important Current Design Gaps

These are known areas that still need decisions or implementation.

### A. Venue Provider Ownership

The current VENUE table has no `account_id` or provider relationship.

A future schema update will be needed to identify which provider owns each venue.

A possible direction is a separate VENUE_PROVIDER profile table associated with ACCOUNT, plus a provider reference in VENUE.

Do not implement this without reviewing the intended provider design first.

### B. Platform Administrator Permissions

The current schema has no global account role or administrator table.

Organization roles are not sufficient to represent global administrator permissions.

A role-management design still needs to be finalized.

### C. Student and Venue Provider Profiles

ACCOUNT is now a general identity table.

Student-specific profile data and venue-provider-specific profile data have not yet been fully modeled.

Do not reintroduce STUDENT as the authentication table.

### D. Organization and Account Verification

The current ORGANIZATION table has only:
- active
- inactive
- deleted

It does not currently have a pending status.

If organization verification is introduced, the schema and workflows must be updated deliberately.

### E. Venue Rental Scope

The exact level of venue rental functionality remains undecided.

Do not assume that UniSync currently supports:
- Online payments.
- Automatic booking confirmation.
- Rental contracts.
- Provider payouts.
- Full reservation management.

These require separate scope and design decisions.

---

## 13. Codex Instructions: How to Work on This Project

The following instructions are important for any remaining legacy references.

### General Rule

Understand the existing implementation before changing it.

Do not rewrite entire features unnecessarily.

Preserve the current architecture, code structure, styling, and behavior unless a change is required.

### When Updating Existing Scripts

The database and application use ACCOUNT as the primary identity entity.

When given a script:

1. Check whether it contains old student-related references.
2. If no changes are needed, simply report that no changes are needed.
3. If changes are needed, provide the complete rewritten code.
4. Replace legacy identity references with their ACCOUNT equivalents. The current application migration is complete; these mappings are retained as a reference for future files or scripts.

Examples:

| Old reference | New reference |
|---|---|
| `student` table | `account` table |
| `student_id` | `account_id` |
| `student_name` | `account_name` |
| `$student` | `$account` |
| `$studentId` | `$accountId` |
| `$_SESSION['student_id']` | `$_SESSION['account_id']` |
| `$_SESSION['student_name']` | `$_SESSION['account_name']` |

Also update related SQL joins, SELECT columns, INSERT statements, UPDATE statements, DELETE conditions, form field names, input IDs, AJAX parameters, and error references when they are tied to account identity.

Do not mechanically rename unrelated variables or wording that genuinely refers to students as a user group.

For example, a sentence explaining that an organization is intended for students may remain unchanged. The goal is to replace old database identity references, not erase the concept of students from the platform.

### Necessary Additions Are Allowed

Do not blindly preserve an old script if it would violate an important rule of the new system.

Add or modify logic only when genuinely necessary for functionality, security, data integrity, or business rules.

Example:

The new ACCOUNT table introduced:

```sql
account_status IN ('active', 'disabled', 'deleted')
```

Therefore, login must reject accounts whose status is not `active`.

This is a necessary addition, even if the old login script did not have that check.

Other necessary protections should be considered when relevant:
- Verify authentication on protected backend operations.
- Verify ownership before modifying organization or venue records.
- Enforce membership permissions for organization management.
- Prevent unauthorized access to another user's data.
- Respect database status constraints.

Do not add speculative features or unnecessary improvements.

### Preserve Existing Code

Unless a change is necessary:
- Do not refactor.
- Do not optimize.
- Do not rename unrelated variables.
- Do not change the HTML layout.
- Do not redesign the UI.
- Do not replace jQuery with vanilla JavaScript.
- Do not change AJAX response formats.
- Do not change existing routes.
- Do not modify unrelated validation rules.
- Do not introduce new dependencies.

### Database Compatibility

Always use PostgreSQL-compatible SQL.

Use PDO prepared statements.

Respect existing foreign keys, unique constraints, check constraints, and status values.

Do not assume a table or column exists if it is not in the current schema.

When a feature requires a new database column or table, identify the requirement before writing code that depends on it.

### When Something Is Unclear

Do not invent missing business rules.

Explain what is missing and ask for clarification only when necessary.

Do not silently change the project's intended behavior.

---

## 14. Current Source of Truth

When working on UniSync, use the following priority:

1. The latest database initialization script supplied by the developer.
2. The latest explicit design decisions supplied by the developer.
3. Existing project code and folder structure.
4. This documentation as project context.

If existing code conflicts with the latest database schema, update the code to match the schema while preserving its original functionality.

If a later instruction changes a design decision, follow the newer instruction.

**The current database has eight tables, with ACCOUNT replacing STUDENT.**

**The current platform direction is a shared multi-university system with a Main UniSync portal, a Venue Provider portal, and an Admin portal.**
