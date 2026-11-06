# UniSync

A University Event Scheduling and Registration System. Students discover and join university organizations, browse events, and register. Organization Admins and Managers manage the organization, its members, and its events.

**COMP 018 – Database Administration · Activity 3 · Group 10**

| | |
|---|---|
| **Backend** | PHP (PDO, prepared statements) |
| **Database** | PostgreSQL 18.6 |
| **Frontend** | HTML, Tailwind CSS, JavaScript, jQuery (AJAX), Leaflet (maps) |
| **Repository branch to test** | `main` |

## Team

| Member | Role |
|---|---|
| Cofreros, Joshua Rei | Lead Developer: Backend, Frontend, and Database implementation |
| Aranguste, Jerome | Documentation Lead: README, user roles and setup documentation |
| Patrona, John Timothy | Database Documentation: ERD, schema and database documentation |

## Features

- Account registration and login
- Create organizations (creator becomes Admin)
- Join / leave organizations, with automatic Admin succession
- Role management and member removal (Admin / Manager)
- Edit and delete organizations (Admin)
- Create, edit, and delete events with multiple schedules and map-pinned venues
- Event status derived from schedules (Pending, Confirmed, Completed, Cancelled)
- Live countdown to the next confirmed schedule
- Event registration and cancellation
- Attendee list with removal for Admins and Managers

## Prerequisites

- PHP 8.1 or newer with the `pdo_pgsql`, `fileinfo`, and `mbstring` extensions enabled
- PostgreSQL 18 (earlier versions are untested)
- Git
- A modern web browser with internet access (map tiles and some icons load from online sources)

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd unisync
```

### 2. Create the database

Using `psql` as a PostgreSQL superuser:

```bash
psql -U postgres -c "CREATE DATABASE grp10_unisyncdb;"
```

Alternatively, create the database through pgAdmin using the name `grp10_unisyncdb`.

### 3. Run the schema script

```bash
psql -U postgres -d grp10_unisyncdb -f db/init.sql
```

### 4. Load the sample data

```bash
psql -U postgres -d grp10_unisyncdb -f db/seed.sql
```

The seed data contains 100+ student accounts, 30 organizations, 50 events, and venues with real addresses and map coordinates. The `NOTIFICATION` table is intentionally left empty.

> `db/migrate_student_to_account.sql` is only for upgrading an older database that still has the `STUDENT` table. A fresh install does **not** need it.

### 5. Configure the environment

```bash
cp .env.example .env
```

On Windows (Command Prompt), use `copy .env.example .env`.

Edit `.env` and fill in your own local values:

| Variable | Description | Example |
|---|---|---|
| `DB_HOST` | PostgreSQL host | `localhost` |
| `DB_PORT` | PostgreSQL port | `5432` |
| `DB_NAME` | Database name | `grp10_unisyncdb` |
| `DB_USER` | Runtime database user | `unisync_app` |
| `DB_PASSWORD` | Password for that user | `your_password_here` |

If you have not created the `unisync_app` runtime user (see [`docs/database-roles.md`](docs/database-roles.md)), you can use your local `postgres` account for testing.

Never commit `.env`. Only `.env.example` (placeholders) belongs in the repository.

### 6. Enable the required PHP extensions

Locate the `php.ini` file used by your standalone PHP installation:

```bash
php --ini
```

Open the listed configuration file and make sure these extensions are enabled:

```ini
extension=pdo_pgsql
extension=fileinfo
extension=mbstring
```

Save the file and restart the PHP development server if it is already running.

Verify that the extensions are enabled:

```bash
php -m
```

### 7. Make the upload folder writable

Make sure this directory exists:

```text
public/uploads/events/
```

The directory must be writable by PHP so event image uploads can be saved.

On Windows, check the folder's security permissions if uploads fail.

### 8. Start the application

From the project root, run:

```bash
php -S localhost:8000
```

Open the application in your browser:

`http://localhost:8000/`

The root `index.php` is the entry point and loads the homepage from `public/home.php`.

### Optional: PostgreSQL helper scripts (Windows)

`start_psql.bat` and `stop_psql.bat` in the project root start and stop a local PostgreSQL server. Edit the paths inside them to match your PostgreSQL installation before use.

## Folder Structure

```text
unisync/
├── index.php                  # Root entry point
├── .env.example               # Environment template (placeholders only)
├── .gitignore
├── README.md
├── start_psql.bat             # Start local PostgreSQL (Windows)
├── stop_psql.bat              # Stop local PostgreSQL (Windows)
│
├── config/
│   └── db_connection_test.php # Quick database connection check
│
├── db/
│   ├── init.sql               # Schema (tables, constraints)
│   ├── seed.sql               # Sample data
│   └── migrate_student_to_account.sql  # Upgrade script for older databases only
│
├── docs/
│   ├── user-roles.md          # Application roles mapped to database roles
│   ├── database-roles.md      # Database users, group roles, privileges
│   ├── database.md            # Tables, keys, constraints, ERD
│   └── troubleshooting.md     # Extended setup troubleshooting
│
├── php/
│   ├── db.php                 # PDO database connection
│   ├── components/            # Shared navbar and footer
│   └── app/                   # Backend handlers, grouped by feature
│       ├── account/
│       ├── auth/
│       ├── browse/
│       ├── dashboard/
│       ├── event/
│       ├── home/
│       └── organization/
│
└── public/                    # Web-facing pages
    ├── home.php
    ├── account_login.php
    ├── account_registration.php
    ├── provider_login.php
    ├── browse.php
    ├── dashboard.php
    ├── event.php
    ├── create_event.php
    ├── edit_event.php
    ├── manage_events.php
    ├── organization.php
    ├── create_organization.php
    ├── settings.php
    ├── assets/
    │   ├── javascript/        # jQuery, Tailwind, Leaflet, and app/ page scripts
    │   └── images/            # Static images (default event image)
    └── uploads/
        └── events/            # Uploaded event images (writable)
```

How the pieces connect: each page in `public/` has a matching script in `public/assets/javascript/app/`, which calls a PHP handler in `php/app/` through jQuery AJAX. Handlers use `php/db.php` to reach PostgreSQL.

## Application User Roles

Application roles describe what a person can do after logging in. They are separate from PostgreSQL roles. End users do not get their own database login; the application connects through a single runtime account.

| Application role | Responsibilities | Functions and data access | Intended database role |
|---|---|---|---|
| **Guest** (not logged in) | Browses public pages. | Read organizations and events. | Runtime account |
| **Student / Member** | Joins organizations and registers for events. | Create/update own membership and registrations; read organizations and events. | `unisync_runtime_grp` |
| **Organization Manager** | Runs events for their organization. | Everything a Member can do, plus create, update, and delete events and schedules; view attendees and remove them; remove regular Members. | `unisync_runtime_grp` |
| **Organization Admin** | Administers the organization. | Everything a Manager can do, plus edit/delete the organization, change member roles, and remove any member. | `unisync_runtime_grp` |
| **System Administrator** | Database and record maintenance outside the app. | Full administrative access to UniSync objects, not a superuser. | `unisync_admin_grp` |
| **Application runtime (system account)** | The account PHP uses to connect. | SELECT, INSERT, UPDATE on application tables; no DDL. | `unisync_runtime_grp` |
| **Viewer / auditor** | Reviews records and reports. | Read-only on approved tables and views. | `unisync_readonly_grp` |

Organization roles (Admin, Manager, Member) are stored per organization in the `MEMBERSHIP` table, so one account can hold different roles in different organizations.

Detailed database users, group roles, and privileges are in [`docs/database-roles.md`](docs/database-roles.md). The full role mapping is in [`docs/user-roles.md`](docs/user-roles.md).

## Database Overview

Database: `grp10_unisyncdb` · Schema: `public` · 8 tables

| Table | Purpose |
|---|---|
| `ACCOUNT` | User accounts |
| `ORGANIZATION` | University organizations |
| `MEMBERSHIP` | Links accounts to organizations and stores the role |
| `EVENT` | Events created by organizations |
| `VENUE` | Event locations with coordinates |
| `EVENT_SCHEDULE` | Dates, times, and venue for each event |
| `REGISTRATION` | Account-to-event registrations |
| `NOTIFICATION` | Exists in the schema but is not used by the application |

Key rules:

- An account can hold only one membership per organization.
- An account can register for an event only once.
- A schedule's end time must be later than its start time.
- Records are **soft-deleted** using a `status` column (`deleted`, `inactive`, `cancelled`), never removed from the table. Deleting an organization also marks its events as deleted.

Full documentation (columns, keys, constraints, ERD): [`docs/database.md`](docs/database.md).

## Troubleshooting

| Problem | Likely cause and fix |
|---|---|
| `could not find driver` | The `pdo_pgsql` extension is disabled. Enable it in `php.ini` and restart the PHP server. |
| `password authentication failed for user` | `DB_USER` / `DB_PASSWORD` in `.env` do not match your PostgreSQL user. |
| `database "grp10_unisyncdb" does not exist` | Step 2 was skipped, or `DB_NAME` does not match the database you created. |
| `relation "..." does not exist` | The schema script (`db/init.sql`) was not run, or it ran against a different database. |
| `psql` is not recognized | Add the PostgreSQL `bin` folder to your PATH, or use the full path to `psql.exe`. |
| Blank page or HTTP 500 | Check the PHP error output in the terminal running `php -S`. Usually a connection or missing-extension problem. |
| Event image upload fails | `public/uploads/events` is not writable. See step 7. |
| Map is blank | The browser has no internet access to load OpenStreetMap tiles. |
| Icons missing | Icons load from an online source. Check your internet connection. |
| Page loads without styling or scripts | Make sure you started the server from the project root, not from inside `public/`. |
| Port 8000 already in use | Start the server on another port, e.g. `php -S localhost:8080`. |
| Re-running the seed script fails with duplicate key errors | Drop and recreate the database, then run the schema and seed scripts again. |

More detail: [`docs/troubleshooting.md`](docs/troubleshooting.md).

## Security Notes

- Do not commit `.env` or any real credentials.
- Passwords in the seed data are hashes of sample passwords for testing only.
- All database access uses PDO prepared statements.

## AI Usage

AI tools were used during development. The full declaration (tools, tasks, and how the output was validated) is in the progress documentation PDF.

## License

For academic use in COMP 018 – Database Administration.