# UniSync

A University Event Scheduling and Registration System. Students discover and join university organizations, browse events, and register. Organization Admins and Managers manage their organizations, members, and events.

**COMP 018 – Database Administration · Activity 3 · Group 10**

| | |
|---|---|
| **Backend** | PHP (PDO, prepared statements) |
| **Database** | PostgreSQL 18.6 |
| **Frontend** | HTML, Tailwind CSS, JavaScript, jQuery (AJAX) |
| **Local PHP Environment** | XAMPP |
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

- XAMPP with PHP 8.1 or newer
- PostgreSQL 18.6
- PHP extensions enabled:
  - `pdo_pgsql`
  - `fileinfo`
  - `mbstring`
- Git
- A modern web browser
- Internet connection for map tiles and externally loaded icons

## Installation

### 1. Clone the repository

Clone the repository using Git:

```bash
git clone <repository-url>
```

Place the cloned project folder inside XAMPP's `htdocs` directory.

For example:

```text
C:\xampp\htdocs\unisync
```

### 2. Configure PHP in XAMPP

1. Open the XAMPP Control Panel.
2. Click **Config** beside Apache.
3. Open `php.ini`.
4. Find the following extension:

```ini
;extension=pdo_pgsql
```

5. Remove the semicolon (`;`) to enable it:

```ini
extension=pdo_pgsql
```

6. Make sure the following extensions are also enabled:

```ini
extension=fileinfo
extension=mbstring
```

7. Save the file.
8. Restart Apache through the XAMPP Control Panel.

**Note:** The extension names or their existing configuration may vary depending on the PHP version bundled with XAMPP. If an extension is already enabled, do not add another duplicate entry.

### 3. Create the PostgreSQL database

You can create the database using either **pgAdmin 4** or the PostgreSQL command-line tool (`psql`).

**Option A: Using pgAdmin 4**

1. Open pgAdmin 4 and connect to your PostgreSQL server.
2. Right-click **Databases**.
3. Select **Create → Database**.
4. Enter the database name:

```text
unisync
```

5. Save.

**Option B: Using psql**

Open a terminal and connect using a PostgreSQL account with permission to create databases:

```bash
psql -U postgres
```

Then execute:

```sql
CREATE DATABASE unisync;
```

You may use another database name, provided that the application's database configuration uses the same name.

### 4. Create the database tables and schema

You can also use either pgAdmin or `psql` to execute the database scripts.

**Option A: Using pgAdmin 4**

1. Select the `unisync` database.
2. Open **Tools → Query Tool**.
3. Open the project's database schema SQL script.
4. Execute the script to create the required tables, relationships, and constraints.
5. If a seed script is provided, execute it afterward to insert the sample records.

**Option B: Using psql**

If the project includes `database/schema.sql`, execute:

```bash
psql -U postgres -d unisync -f database/schema.sql
```

If a seed script is provided, execute it after the schema:

```bash
psql -U postgres -d unisync -f database/seed.sql
```

Use the actual SQL script filenames and locations available in the repository. The schema must be created before inserting seed data.

### 5. Configure the database connection

Configure the application's database connection using the environment configuration file provided by the project.

If the repository includes `.env.example`, create a local `.env` file from it and update the values.

Example configuration:

| Variable | Description | Example |
|---|---|---|
| `DB_HOST` | PostgreSQL host | `localhost` |
| `DB_PORT` | PostgreSQL port | `5432` |
| `DB_NAME` | Database name | `unisync` |
| `DB_USER` | PostgreSQL username | `postgres` |
| `DB_PASSWORD` | Password for that PostgreSQL user | Your local PostgreSQL password |

Make sure the database name, username, and password match your PostgreSQL configuration.

**Important:** Never commit `.env` or real database credentials to the repository. Only placeholder values should be included in `.env.example`.

### 6. Start the application

1. Open the XAMPP Control Panel.
2. Start **Apache**.
3. Make sure your PostgreSQL server is running.
4. Open the application in your browser:

```text
http://localhost/unisync/public/
```

If the repository is configured to use a different public entry point, use the appropriate URL for that setup.

## Application User Roles

Application roles describe what a person can do after logging in. They are separate from PostgreSQL database roles.

| Application role | Responsibilities |
|---|---|
| **Guest** (not logged in) | Browses public pages and available organizations and events. |
| **Student / Member** | Joins organizations, leaves organizations, and registers for events. |
| **Organization Manager** | Manages events and schedules, views attendees, removes attendees, and removes regular Members within their organization. |
| **Organization Admin** | Manages the organization, changes member roles, and has the additional administrative permissions of an Admin. |
| **System Administrator** | Database and record maintenance outside the normal application user workflow. |

Organization roles (Admin, Manager, Member) are stored per organization in the `MEMBERSHIP` table. This allows one account to have different roles in different organizations.

For example, an account can be an Admin in one organization and a regular Member in another.

These application roles are enforced by the PHP backend. They should not be confused with PostgreSQL users or group roles.

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

### Key Rules

- An account can hold only one membership per organization.
- An account can register for an event only once.
- A schedule's end time must be later than its start time.
- Records are soft-deleted using a `status` column (such as `deleted`, `inactive`, or `cancelled`) rather than physically removed from the table.
- Deleting an organization also marks its events as deleted.

For complete documentation covering columns, keys, constraints, and the ERD, refer to `docs/database.md` if included in the repository.

## Troubleshooting

| Problem | Likely cause and fix |
|---|---|
| `could not find driver` | The `pdo_pgsql` extension is disabled. Enable it in XAMPP's `php.ini` and restart Apache. |
| `password authentication failed for user` | The PostgreSQL username or password in the environment configuration does not match your PostgreSQL account. |
| `database "unisync" does not exist` | The database was not created, or the configured database name does not match. |
| `relation "..." does not exist` | The schema script was not executed, or it was executed against a different database. |
| Blank page or HTTP 500 | Check the Apache PHP error logs in XAMPP. This may be caused by a database connection or PHP configuration issue. |
| Event image upload fails | Check whether the application's upload directory exists and is writable by Apache. |
| Map is blank | Check your internet connection. Map tiles and Leaflet resources require external access. |
| Icons missing | Font Awesome is loaded externally. Check your internet connection. |
| Apache will not start | Another application may already be using Apache's configured port, commonly port 80 or 443. |
| Re-running the seed script fails with duplicate key errors | Existing records may already be present. Use a clean test database before running the seed script again. |

## Security Notes

- Do not commit `.env` or real database credentials.
- Passwords should be stored as hashes, not plaintext.
- All database access uses PDO prepared statements.
- Application permissions must be validated by the PHP backend, not only by hiding interface elements.

## AI Usage

AI tools were used during development. The full declaration, including the tools used, tasks performed, and how their output was validated, is provided in the project progress documentation.

## License

For academic use in COMP 018 – Database Administration.