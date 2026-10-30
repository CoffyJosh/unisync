# UniSync

**A Web-Based University Event and Organization Scheduling System**

UniSync is a web-based platform designed to help students discover university events, register for activities, and keep track of their schedules. It also allows student organizations to manage their events, members, and registrations in one place.

The project is being developed using **PHP, PostgreSQL, HTML, Tailwind CSS, and jQuery**.

---

## 1. Features

### Student Features
- Create an account and log in.
- Browse and search university events.
- Search for student organizations through events.
- Sort and filter events.
- View event details, including schedules and venues.
- Register for public events.
- View and track registered events through a student dashboard.
- Update basic account information.

### Organization Features
- Create and manage student organizations.
- Manage organization memberships.
- Create, edit, and manage organization events.
- View students registered for an event.
- View organization members and their registration status for an event.

### Event Management
Events have the following statuses:
- Pending
- Confirmed
- Cancelled
- Completed

The system also checks for scheduling conflicts involving the same venue and date.

---

## 2. Technology Stack

| Technology | Purpose |
|---|---|
| PHP | Backend development |
| PostgreSQL | Database management |
| HTML | Page structure |
| Tailwind CSS | Styling and layout |
| jQuery | Frontend interactions and AJAX |

---

## 3. Project Structure

The project structure is still being developed and may change as UniSync grows.

**Do not assume that files will always stay in their current folders.** Before creating or modifying files, check the latest version of the repository and follow its existing organization.

Some important files may include:

| File | Purpose |
|---|---|
| `.env` | Contains your local database credentials and environment settings. |
| `.env.example` | Shared example of the required environment variables. |
| `.gitignore` | Specifies files Git should not upload. |
| `README.md` | Project documentation and setup guide. |
| Database initialization SQL | Contains the database tables and constraints. |
| Database seed SQL | Contains sample data for development and testing. |
| Database connection file | Handles the application's PostgreSQL connection. |

File names and locations may change as development continues. Check the repository before relying on any specific path.

---

## 4. Requirements

Before running the project, make sure you have:

- PHP installed.
- PostgreSQL installed and running.
- A database management tool such as pgAdmin (optional, but recommended).
- Git installed.
- Visual Studio Code or another code editor.
- A web browser.

Make sure your PHP installation has the PostgreSQL PDO extension enabled (`pdo_pgsql`).

---

## 5. Getting Started

### Step 1: Clone the Repository

Open a terminal and clone the shared repository:

```bash
git clone <repository-url>
```

Enter the project directory:

```bash
cd <project-folder>
```

Open the project in Visual Studio Code:

```bash
code .
```

Replace the placeholders with the actual repository URL and folder name.

### Step 2: Configure Environment Variables

Find the `.env.example` file in the repository.

Create your own `.env` file using it as a reference. If the project uses a different configuration method, follow the current implementation.

Example environment configuration:

```env
APP_ENV=development

DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=unisync_db
DB_USER=postgres
DB_PASSWORD=your_local_password
```

Change the values according to your own PostgreSQL installation.

**Important:**
- Each member should use their own local database credentials.
- Never upload your `.env` file to GitHub.
- Keep `.env.example` updated whenever the required environment variables change.
- PHP does not automatically load `.env` files. The application must have a dotenv package or another environment-loading method configured.

### Step 3: Create the Database

Open PostgreSQL through pgAdmin or your preferred database tool.

Create a database named:

```sql
unisync_db
```

Locate the database initialization SQL file in the repository and execute it against the newly created database.

If sample data is available, locate and execute the seed SQL file afterward.

**Important:** Check the SQL scripts before running them. Initialization scripts may contain commands that drop and recreate existing tables, which can delete your current data.

### Step 4: Run the Application

Follow the current project configuration and identify its PHP entry point.

If the application is configured to use a `public` directory as its document root, you may run:

```bash
php -S localhost:8000 -t public
```

Otherwise, use the appropriate command for the current project structure.

Open the local address in your browser:

```text
http://localhost:8000
```

If the application uses a different port or server configuration, follow the latest instructions from the team.

---

## 6. Git Workflow

Since everyone is working on the same project, make sure your changes are pushed to the shared repository.

### Before Starting Work

Get the latest changes from the main branch:

```bash
git switch main
git pull origin main
```

### Create a Branch

Create a separate branch for your task:

```bash
git switch -c feature/your-task
```

Examples:

```bash
git switch -c feature/homepage
git switch -c feature/event-details
git switch -c feature/login
```

Use a branch name that describes what you're working on.

### Save Your Changes

Check which files you modified:

```bash
git status
```

Stage your changes:

```bash
git add .
```

Commit your work:

```bash
git commit -m "Add homepage layout"
```

Push your branch:

```bash
git push -u origin feature/your-task
```

### Submit Your Changes

After pushing your branch:

1. Open the repository on GitHub.
2. Create a Pull Request.
3. Let the team review your changes.
4. Merge the changes into the main branch once approved.

### Important Git Reminders

- Avoid working directly on the `main` branch.
- Pull the latest changes before starting a new task.
- Commit regularly instead of waiting until everything is finished.
- Use meaningful commit messages.
- Do not upload `.env`, passwords, or other private credentials.
- Avoid force-pushing or overwriting another member's work.
- If you encounter a merge conflict, coordinate with the person responsible for the affected files.

---

## 7. Application Roles

UniSync uses a single student account system. A student who creates an organization automatically becomes its Admin.

Organization roles determine what a member can do.

| Role | Permissions |
|---|---|
| Admin | Manage the organization, manage members and their roles, and manage organization events. |
| Manager | Create and manage events and view attendees and members. Cannot manage membership roles. |
| Member | View organization information and events, and register for events. |

Students do not need to belong to an organization to register for its public events.

---

## 8. Event Registration and Management

A few important rules guide how event registration works:

- A student can register for an event only once.
- Being an organization member does not automatically register a student for its events.
- Public events can be joined by students outside the hosting organization.
- Only authorized organization members can manage their organization's events.
- An event's end time must be later than its start time.
- The system prevents overlapping events from being scheduled at the same venue and date.

### Attendees and Members

Event management separates these into two views:

**Attendees**
- Displays all students registered for the selected event.
- Includes students who are not members of the hosting organization.
- Shows their names, email addresses, and registration dates.

**Members**
- Displays all members of the hosting organization.
- Shows their names, email addresses, organization roles, and whether they registered for the selected event.
- A member marked as "Not registered" simply has not registered for that event. It does not mean they were absent.

---

## 9. Development Guidelines

To keep the project organized:

- Check existing files before creating new ones.
- Follow the naming conventions already used in the repository.
- Keep related files organized.
- Reuse existing components and functions when appropriate.
- Avoid creating duplicate pages or database connections.
- Coordinate with the team before moving, renaming, or deleting shared files.
- Test your changes before pushing them.
- Make sure your changes do not break existing features.

If you're working on a frontend page, focus on making the layout and interactions work with the existing design. Coordinate with whoever is handling the backend before changing how data is retrieved or submitted.

---

## 10. Troubleshooting

### Database Connection Failed

Check the following:
- Is PostgreSQL running?
- Does the `unisync_db` database exist?
- Are your `.env` credentials correct?
- Is the PostgreSQL PDO extension enabled in PHP?
- Does the application's database connection configuration load the environment variables correctly?

### Page Not Found

- Check the current PHP entry point.
- Confirm that you're running the application from the correct location.
- Check whether the local server uses a specific document root.
- Review the current project routing configuration.

### Changes Are Missing

Make sure you:
- Saved your files.
- Committed your changes.
- Pushed your branch.
- Are viewing the correct branch on GitHub.

### Git Merge Conflict

- Check which files have conflicts.
- Coordinate with the teammate who modified the same files.
- Resolve the conflicts carefully.
- Test the affected functionality before committing the resolution.

---

## 11. Project Scope

The current development focuses on the core UniSync features: student accounts, event browsing, registration, organization management, event scheduling, and dashboards.

Some features are intentionally outside the current implementation scope, including:

- QR-based attendance tracking.
- Actual attendance monitoring.
- Mobile application.
- Automated notification functionality.
- A separate organization directory page.

These may be considered for future improvements, but they should not be treated as required features of the current version.

---

## 12. Project Goal

UniSync aims to make university event discovery and organization scheduling more organized and accessible. By bringing event browsing, registration, and organization management together, the platform provides students and organizations with a shared space to manage campus activities.