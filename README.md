# UniSync

A University Event Scheduling and Registration System. Students discover and join university organizations, browse events, and register. Organization Admins and Managers manage the organization, its members, and its events.

**COMP 018 – Database Administration · Activity 3 · Group 10**

| | |
|---|---|
| **Backend** | PHP (PDO, prepared statements) |
| **Database** | PostgreSQL 18.6 |
| **Frontend** | HTML, Tailwind CSS, JavaScript, jQuery (AJAX) |
| **Repository branch to test** | `main` |

## Team

| Member | Role |
|---|---|
| Cofreros, Joshua Rei | Lead Developer: Backend, Frontend, and Database implementation |
| Name | Documentation Lead: README, user roles and setup documentation |
| Name | Database Documentation: ERD, schema and database documentation |
| Name | QA and Progress Report: setup testing, PDF progress document, tracker |

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
- A modern web browser with internet access (map tiles and icons load from CDNs)

## Project Structure

```
unisync/
├── public/              Pages opened in the browser (entry point)
│   └── uploads/events/  Uploaded event images (created automatically)
├── php/
│   ├── db.php           Database connection
│   ├── app/             Backend handlers (organization, event, account, ...)
│   └── components/      Shared navbar and footer
├── javascript/          jQuery / AJAX scripts and vendored libraries
├── database/            SQL schema and seed scripts  <!-- TODO: confirm folder name -->
├── docs/                Database and role documentation
├── .env.example
└── README.md
```

<!-- TODO: adjust the tree to match the actual repository -->

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd unisync
```

### 2. Create the database

Using `psql` as a PostgreSQL superuser:

```bash
psql -U postgres -c "CREATE DATABASE unisync;"
```

### 3. Run the schema script

```bash
psql -U postgres -d unisync -f database/schema.sql
```

<!-- TODO: replace with the real script path(s) and order -->

### 4. Load the sample data

```bash
psql -U postgres -d unisync -f database/seed.sql
```

<!-- TODO: replace with the real seed script path -->

The seed data contains 100+ student accounts, 30 organizations, 50 events, and venues with real addresses and map coordinates. The `NOTIFICATION` table is intentionally left empty.

### 5. Configure the environment

```bash
cp .env.example .env
```

Edit `.env` and fill in your own local values:

| Variable | Description | Example |
|---|---|---|
| `DB_HOST` | PostgreSQL host | `localhost` |
| `DB_PORT` | PostgreSQL port | `5432` |
| `DB_NAME` | Database name | `unisync` |
| `DB_USER` | Runtime database user | `unisync_app` |
| `DB_PASSWORD` | Password for that user | `your_password_here` |

Never commit `.env`. Only `.env.example` (placeholders) belongs in the repository.

### 6. Make the upload folder writable

```bash
mkdir -p public/uploads/events
chmod -R 775 public/uploads
```

### 7. Start the application

```bash
php -S localhost:8000 -t public
```

Open <http://localhost:8000> in your browser.

## Sample Accounts

| Account | Email | Password | Purpose |
|---|---|---|---|
| <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | Organization Admin |
| <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | Organization Manager |
| <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | Regular Member / Student |

These are sample credentials for local testing only.

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

Detailed database users, group roles, and privileges are in [`docs/database-roles.md`](docs/database-roles.md). <!-- TODO: create this file; no real passwords -->

## Database Overview

Database: `unisync` · Schema: `public` · 8 tables

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

Full documentation (columns, keys, constraints, ERD): [`docs/database.md`](docs/database.md). <!-- TODO: create this file -->

## Troubleshooting

| Problem | Likely cause and fix |
|---|---|
| `could not find driver` | The `pdo_pgsql` extension is disabled. Enable it in `php.ini` (`extension=pdo_pgsql`) and restart PHP. |
| `password authentication failed for user` | `DB_USER` / `DB_PASSWORD` in `.env` do not match your PostgreSQL user. |
| `database "unisync" does not exist` | Step 2 was skipped, or `DB_NAME` does not match the database you created. |
| `relation "..." does not exist` | The schema script was not run, or it ran against a different database. |
| Blank page or HTTP 500 | Check the PHP error output in the terminal running `php -S`. Usually a connection or missing-extension problem. |
| Event image upload fails | `public/uploads/events` is not writable. See step 6. |
| Map is blank | The browser has no internet access to load OpenStreetMap tiles and Leaflet. |
| Icons missing | Font Awesome is loaded from a CDN. Check your internet connection. |
| Port 8000 already in use | Start the server on another port, e.g. `php -S localhost:8080 -t public`. |
| Re-running the seed script fails with duplicate key errors | Drop and recreate the database, then run the schema and seed scripts again. |

## Security Notes

- Do not commit `.env` or any real credentials.
- Passwords in the seed data are hashes of sample passwords for testing only.
- All database access uses PDO prepared statements.

## AI Usage

AI tools were used during development. The full declaration (tools, tasks, and how the output was validated) is in the progress documentation PDF. <!-- TODO: add link -->

## License

For academic use in COMP 018 – Database Administration.