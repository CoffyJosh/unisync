# UniSync

A University Event Scheduling and Registration System. Students discover organizations, request to join them, browse events, and register. Organization Admins review join requests and manage the organization, its members, and its events. Managers help run the organization's events.

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
- Request to join organizations (requests stay pending until an Admin reviews them)
- Admins accept or reject join requests
- Cancel your own pending join request
- Leave organizations, with automatic Admin succession
- Role management and member removal (Admin)
- Edit and delete organizations (Admin)
- Create, edit, and delete events with multiple schedules and map-pinned venues
- Event status derived from schedules (Pending, Confirmed, Completed, Cancelled)
- Live countdown to the next confirmed schedule
- Event registration and cancellation
- Attendee list with removal for Admins and Managers

## Documentation

| Document | Description |
|---|---|
| [Progress Documentation](docs/documentation.pdf) | System development progress, database implementation, application evidence, issues and resolutions, cross-group verification, and AI usage declaration. |
| [Database Documentation](docs/database.md) | Database structure, columns, keys, constraints, and ERD. |
| [Database Roles](docs/database-roles.md) | PostgreSQL users, group roles, and database privileges. |
| [Application User Roles](docs/user-roles.md) | Application roles and their responsibilities. |
| [Troubleshooting Guide](docs/troubleshooting.md) | Common setup and application issues. |

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

> `db/init.sql` drops and recreates every UniSync table. Re-run it (and the seed script) after every update. See [Updating to a New Version](#updating-to-a-new-version-testers).

### 4. Load the sample data

```bash
psql -U postgres -d grp10_unisyncdb -f db/seed.sql
```

The seed data contains 100+ student accounts, 30 organizations, 50 events, and venues with real addresses and map coordinates.

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

## Updating to a New Version (Testers)

> **Important:** For **every new update** you pull, re-run `db/init.sql` and then `db/seed.sql` before testing. The database design changes between updates, so an outdated database will cause errors (for example, missing columns or rejected status values).
>
> Doing this **clears all data created during testing** (accounts, organizations, memberships, join requests, events, registrations) and restores the original sample data.

```bash
git pull
psql -U postgres -d grp10_unisyncdb -f db/init.sql
psql -U postgres -d grp10_unisyncdb -f db/seed.sql
```

You do not need to recreate the database. `init.sql` drops and recreates the tables itself. Always run `init.sql` first, then `seed.sql`.

## Sample Accounts

The seed data includes test accounts you can log in with:

| Email | Password |
|---|---|
| testaccount1@gmail.com | `Password123!` |
| testaccount2@gmail.com | `Pass123` |
| testaccount3@gmail.com | `HelloSekai` |
| testaccount4@gmail.com | `Kurourushi` |
| testaccount5@gmail.com | `OpenUpTheSkye` |
| testaccount6@gmail.com | `MalevolentKitchen` |
| testaccount7@gmail.com | `HeimerDonger` |
| testaccount8@gmail.com | `Lol123` |
| testaccount9@gmail.com | `password246` |
| testaccount10@gmail.com | `JkLababo123` |

These are demo credentials for testing only.

## Folder Structure

```text
unisync/
├── config/                  # Database connection and application configuration
├── db/                      # UniSync database initialization, seed data, and access-control scripts
├── db-access-control/       # Cross-group database roles, privileges, and access-control verification
├── db-backup-recovery/      # Cross-group backup plans, reports, and recovery verification
├── db-maintenance/          # Cross-group database maintenance and query analysis documentation
├── docs/                    # Project documentation, including the progress PDF and database guides
├── php/                     # Backend PHP application code
│   ├── app/                 # PHP request handlers and application logic
│   ├── components/          # Reusable PHP interface components
│   └── services/            # Shared application services
├── public/                  # Publicly accessible application pages and assets
│   └── assets/              # Frontend resources, images, and JavaScript
│       └── javascript/      # jQuery, libraries, and page-specific scripts
└── index.php                # Root application entry point
```

How the pieces connect: each page in `public/` has a matching script in `public/assets/javascript/app/`, which calls a PHP handler in `php/app/` through jQuery AJAX. Handlers use `config/db.php` to reach PostgreSQL.

## Application User Roles

Application roles describe what a person can do after logging in. They are separate from PostgreSQL roles. End users do not get their own database login; the application connects through a single runtime account.

| Application role | Responsibilities | Functions and data access | Intended database role |
|---|---|---|---|
| **Guest** (not logged in) | Browses public pages. | Read organizations and events. | Runtime account |
| **Student / Member** | Requests to join organizations and registers for events. | Submit and cancel own join requests; create/update own membership and registrations; read organizations and events. | `unisync_runtime_grp` |
| **Organization Manager** | Runs events for their organization. | Everything a Member can do, plus create, update, and delete events and schedules; view attendees and remove them; remove regular Members. Managers **cannot** review join requests. | `unisync_runtime_grp` |
| **Organization Admin** | Administers the organization. | Everything a Manager can do, plus accept or reject join requests, edit/delete the organization, change member roles, and remove any member. | `unisync_runtime_grp` |
| **System Administrator** | Database and record maintenance outside the app. | Full administrative access to UniSync objects, not a superuser. | `unisync_admin_grp` |
| **Application runtime (system account)** | The account PHP uses to connect. | SELECT, INSERT, UPDATE on application tables; no DDL. | `unisync_runtime_grp` |
| **Viewer / auditor** | Reviews records and reports. | Read-only on approved tables and views. | `unisync_readonly_grp` |

Organization roles (Admin, Manager, Member) are stored per organization in the `MEMBERSHIP` table, so one account can hold different roles in different organizations. Join requests are stored in the same table with a `pending` status until an Admin reviews them.

Detailed database users, group roles, and privileges are in [`docs/database-roles.md`](docs/database-roles.md). The full role mapping is in [`docs/user-roles.md`](docs/user-roles.md).

## Database Overview

Database: `grp10_unisyncdb` · Schema: `public` · 8 tables

| Table | Purpose |
|---|---|
| `ACCOUNT` | User accounts |
| `ORGANIZATION` | University organizations |
| `MEMBERSHIP` | Links accounts to organizations; stores the role and join-request status |
| `EVENT` | Events created by organizations |
| `VENUE` | Event locations with coordinates |
| `EVENT_SCHEDULE` | Dates, times, and venue for each event |
| `REGISTRATION` | Account-to-event registrations |
| `NOTIFICATION` | Exists in the schema but is not used by the application |

Key rules:

- An account can hold only one membership per organization.
- Joining an organization is a request: it creates a `pending` membership that only an Admin can accept (it becomes `active`) or reject (it becomes `rejected`). The requester can cancel a pending request, and a rejected user can request again.
- A pending or rejected request never carries an elevated role.
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
| `column "..." does not exist`, or a check constraint violation after pulling an update | Your database is outdated. Re-run `db/init.sql`, then `db/seed.sql` (this clears tester-made data). See [Updating to a New Version](#updating-to-a-new-version-testers). |
| `psql` is not recognized | Add the PostgreSQL `bin` folder to your PATH, or use the full path to `psql.exe`. |
| Blank page or HTTP 500 | Check the PHP error output in the terminal running `php -S`. Usually a connection or missing-extension problem. |
| Event image upload fails | `public/uploads/events` is not writable. See step 7. |
| Map is blank | The browser has no internet access to load OpenStreetMap tiles. |
| Icons missing | Icons load from an online source. Check your internet connection. |
| Page loads without styling or scripts | Make sure you started the server from the project root, not from inside `public/`. |
| Port 8000 already in use | Start the server on another port, e.g. `php -S localhost:8080`. |
| Re-running the seed script fails with duplicate key errors | Run `db/init.sql` first to reset the tables, then run `db/seed.sql` again. |

More detail: [`docs/troubleshooting.md`](docs/troubleshooting.md).

## Security Notes

- Do not commit `.env` or any real credentials.
- Passwords in the seed data are hashes of sample passwords for testing only.
- All database access uses PDO prepared statements.

## AI Usage

AI tools were used to assist with development, database documentation, debugging, and technical explanations.

- **Claude:** Coding assistance, debugging, and implementation support.
- **Gemini:** Technical explanations and database-related assistance.
- **ChatGPT:** Documentation assistance, explanations, and development support.

All AI-generated suggestions and outputs were reviewed and validated by the group. The detailed declaration is available in the [Progress Documentation](docs/documentation.pdf).

## License

For academic use in COMP 018 – Database Administration.