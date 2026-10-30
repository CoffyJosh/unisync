# UniSync
## Project Setup and Git Collaboration Guide

Welcome to the UniSync project!

This guide explains how to:
- Clone the project repository.
- Set up your local development environment.
- Create your own `.env` file.
- Connect the project to PostgreSQL.
- Run the application locally.
- Commit and push your changes to GitHub.
- Work with the rest of the team without overwriting each other's work.

Please follow these instructions before starting your assigned tasks.

---

# 1. Requirements

Make sure you have these installed:

| Software | Purpose |
|---|---|
| Git | Clone, commit, and push project changes. |
| PHP | Run the backend. |
| PostgreSQL | Store application data. |
| pgAdmin or psql | Manage and execute PostgreSQL scripts. |
| Visual Studio Code | Recommended code editor. |
| Web browser | Test the application. |

You also need access to the UniSync GitHub repository.

Make sure PHP and PostgreSQL are accessible from your terminal.

You can check your installations using:

```bash
git --version
php -v
psql --version
```

If a command is not recognized, check whether the software is installed and added to your system PATH.

---

# 2. Clone the Repository

You should work from the group's shared GitHub repository.

Do not create a separate project or download the repository as a ZIP unless specifically instructed.

Open your terminal or VS Code terminal.

Run:

```bash
git clone YOUR_REPOSITORY_URL
```

Replace `YOUR_REPOSITORY_URL` with the repository link provided by the group.

Example:

```bash
git clone https://github.com/USERNAME/unisync.git
```

Enter the project folder:

```bash
cd unisync
```

Open it in VS Code:

```bash
code .
```

You should now have your own local copy of the UniSync project.

**Important:** Your local copy is connected to the shared Git repository. This allows you to download other people's changes and upload your own.

---

# 3. Project Structure

The UniSync project structure is still being developed and may change as the system grows.

Rather than following a fixed folder structure, always check the latest version of the repository before creating or modifying files.

### General guidelines

- Keep related files organized together.
- Follow the existing naming conventions.
- Check where similar pages or files are located before creating new ones.
- Avoid creating duplicate folders or files.
- Coordinate with the group before moving or renaming shared files.
- Keep configuration files, database scripts, frontend pages, and backend processing organized.

### Important files

Some important project files include:

- `.env` - Your personal local environment configuration. Never upload this file.
- `.env.example` - A template showing the environment variables required by the project.
- `.gitignore` - Specifies files Git should ignore.
- `README.md` - Project setup and collaboration instructions.
- `config/db.php` - Handles the PostgreSQL database connection.
- `db/init.sql` - Contains the database initialization script.
- `db/seed.sql` - Contains sample data for testing.

These filenames describe the intended purpose of the files. Their locations may change as the project develops, so always check the latest repository.

**If you're unsure where to put a new file, ask the group before creating a new folder or changing the existing structure.**
---

# 4. Create Your Own .env File

Every developer must create their own local `.env` file.

The `.env` file contains your local database connection information.

**Never upload your personal `.env` file to GitHub.**

### Step 1: Find `.env.example`

After cloning the repository, look for:

```text
.env.example
```

This file contains the required environment variable names and example values.

### Step 2: Create your own `.env`

In the project's root folder, create a file named:

```text
.env
```

Copy the contents of `.env.example` into your new `.env` file.

You can do this manually or through your terminal.

Windows Command Prompt:

```cmd
copy .env.example .env
```

PowerShell:

```powershell
Copy-Item .env.example .env
```

Git Bash:

```bash
cp .env.example .env
```

### Step 3: Configure your local database credentials

Example `.env.example`:

```env
APP_ENV=development

DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=unisync_db
DB_USER=postgres
DB_PASSWORD=your_local_password
```

Your own `.env` should contain your actual local PostgreSQL credentials.

For example:

```env
APP_ENV=development

DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=unisync_db
DB_USER=postgres
DB_PASSWORD=my_own_postgres_password
```

The password above is only an example. Use the password you configured when installing PostgreSQL.

### Important .env rules

- Everyone creates their own `.env`.
- Everyone can have different database passwords.
- Do not commit `.env` to GitHub.
- Do not send your actual database password in the group chat.
- Do not change `.env.example` to include your personal credentials.

The `.env.example` file is shared. The `.env` file is private to your computer.

---

# 5. Set Up PostgreSQL

UniSync uses PostgreSQL.

Each developer needs a local database named:

```text
unisync_db
```

### Step 1: Open PostgreSQL

You can use pgAdmin or the PostgreSQL terminal (`psql`).

### Step 2: Create the database

Run:

```sql
CREATE DATABASE unisync_db;
```

### Step 3: Create the database tables

Open the project's:

```text
db/init.sql
```

Execute the SQL script against `unisync_db`.

This initializes the database structure.

**Warning:** The initialization script may drop existing tables before recreating them. Do not execute it on a database containing data you need to keep.

### Step 4: Insert sample data

Open:

```text
db/seed.sql
```

Execute it against `unisync_db`.

This inserts sample records so you can test the application without manually creating everything.

### Step 5: Check the database

Verify that the tables were created successfully.

The approved database design includes:

- STUDENT
- ORGANIZATION
- MEMBERSHIP
- EVENT
- VENUE
- EVENT_SCHEDULE
- REGISTRATION

The notification table is reserved for future functionality and is not part of the current notification implementation.

---

# 6. Run the Application Locally

Once PHP and PostgreSQL are installed and your `.env` is configured:

1. Open the UniSync project folder.
2. Check that your local database exists.
3. Make sure `config/db.php` can read your `.env` configuration.
4. Start the PHP development server using the project's configured document root.

If the website pages are located inside `public/`, you can use:

```bash
php -S localhost:8000 -t public
```

Then open:

```text
http://localhost:8000
```

If the project uses Apache/XAMPP instead, follow the group's configured Apache setup.

If you encounter a database connection error, check:
- Is PostgreSQL running?
- Does `unisync_db` exist?
- Are your `.env` credentials correct?
- Is the database connection code reading the correct environment variables?

---

# 7. Git Workflow

This is important because all of us will be working on the same project.

**Always pull the latest changes before starting work.**

### Step 1: Check your current branch

```bash
git branch
```

### Step 2: Get the latest changes

If you are working on the shared main branch:

```bash
git pull origin main
```

If you are working on your own branch, pull from that branch instead.

### Step 3: Create your own branch

Recommended:

```bash
git switch -c feature/your-task
```

Examples:

```bash
git switch -c feature/homepage
```

```bash
git switch -c feature/event-details
```

```bash
git switch -c feature/student-dashboard
```

This keeps your work separate from everyone else's until it is ready to be merged.

### Step 4: Work on your assigned files

Make your changes inside the cloned UniSync project.

For example:

```text
public/
    index.php
    login.php
    register.php
    events.php
```

If you are only creating the frontend, HTML, CSS, Tailwind CSS, and jQuery are acceptable.

You do not need to implement the PHP backend if you are not comfortable with it.

### Step 5: Check your changes

Before uploading, run:

```bash
git status
```

This shows which files you changed.

You can inspect your changes with:

```bash
git diff
```

### Step 6: Commit your changes

Add the files you want to upload:

```bash
git add .
```

Create a commit:

```bash
git commit -m "Create homepage design"
```

Use meaningful commit messages.

Examples:

```text
Create homepage design
Add event browsing page
Implement event search interface
Add student registration form
Fix event details layout
```

### Step 7: Push your branch

```bash
git push -u origin feature/your-task
```

Your changes will now be available in the shared GitHub repository.

### Step 8: Merge your changes

Once your work is ready, let the group know.

We can review your changes and merge your branch into `main` through a GitHub Pull Request.

---

# 8. Important Git Rules

Please follow these to avoid conflicts:

1. Don't work on a completely separate project.
2. Always clone the group's repository.
3. Pull the latest changes before starting.
4. Use your assigned branch when possible.
5. Don't directly overwrite another person's files.
6. Don't push unfinished or broken changes to `main`.
7. Commit regularly instead of uploading everything at once.
8. Tell the group when your task is ready for review.
9. Never commit `.env` or other files containing secrets.

### If Git says there is a conflict

Don't immediately delete files or force-push.

A conflict means Git cannot automatically combine two people's changes.

Ask the group for help if you are unsure how to resolve it.

---

# 9. Frontend and Backend Collaboration

We are dividing the work so everyone can contribute even if they have different levels of PHP experience.

### Frontend

The frontend includes:
- HTML
- CSS
- Tailwind CSS
- jQuery

You can focus on the layout, buttons, forms, cards, navigation, and page interactions.

### Backend

The backend includes:
- PHP
- PostgreSQL
- Database queries
- Form processing
- Authentication
- Event registration
- Application permissions

Backend processing should be organized in the `php/` folder.

### When creating frontend pages

Please coordinate with the group regarding:

- Form field names.
- Button actions.
- Expected backend handler URLs.
- Data that should be displayed.
- Expected responses from the backend.

For AJAX requests, we should use a consistent JSON response format:

```json
{
    "success": true,
    "message": "Operation completed successfully."
}
```

This helps us connect the frontend to the backend without having to rewrite everything.

If you are unsure how a feature should work, ask before implementing a different workflow.

---

# 10. Troubleshooting

### Git is not recognized

Git may not be installed or added to PATH.

Install Git and restart your terminal.

### PHP is not recognized

Make sure PHP is installed and its executable folder is added to PATH.

### PostgreSQL connection failed

Check:
- PostgreSQL service is running.
- Database name is correct.
- Username and password are correct.
- Host and port are correct.
- Your `.env` file exists.

### My page does not load

Check:
- Are you running the correct PHP server?
- Is the page inside the configured document root?
- Are the file paths correct?
- Are there PHP errors?

### Git push was rejected

Someone may have pushed changes before you.

Try pulling the latest changes and resolving any conflicts before pushing again. If you are working on a feature branch, make sure you are pushing the correct branch.

---

# 11. Before You Start Working

Please make sure you have completed these:

- [ ] Installed Git.
- [ ] Installed PHP.
- [ ] Installed PostgreSQL.
- [ ] Cloned the UniSync repository.
- [ ] Created your own `.env`.
- [ ] Created your local `unisync_db` database.
- [ ] Executed `db/init.sql`.
- [ ] Executed `db/seed.sql`.
- [ ] Confirmed that the application runs locally.
- [ ] Created your assigned Git branch.
- [ ] Confirmed your assigned pages with the group.

---

# Final Reminder

The goal is to make everyone's work part of one UniSync project.

You can use ChatGPT to help with your assigned pages, but make sure the generated code follows the existing project structure and agreed system requirements.

If you are working on frontend design only, that's completely fine. I'll handle converting the pages to PHP and connecting them to the backend.

**Clone the repository, create your own `.env`, work on your assigned branch, and push your changes when they're ready.**

Let's keep everything in one repository so we can integrate the system properly.