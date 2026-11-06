# UniSync Troubleshooting Guide

Common setup problems and how to fix them. Each entry lists the symptom, the likely cause, and the fix.

## 1. Database

### `psql` is not recognized
- **Cause:** The PostgreSQL `bin` folder is not in your PATH.
- **Fix:** Add it to PATH (e.g. `C:\Program Files\PostgreSQL\18\bin` on Windows), then reopen the terminal. Or call `psql` by its full path. You can also run the scripts from pgAdmin's Query Tool instead.

### `password authentication failed for user`
- **Cause:** `DB_USER` or `DB_PASSWORD` in `.env` does not match a PostgreSQL user.
- **Fix:** Correct the values in `.env`. If you have not created the `unisync_app` runtime user, use your local `postgres` account for testing.

### `connection refused` or `could not connect to server`
- **Cause:** PostgreSQL is not running, or `DB_HOST` / `DB_PORT` is wrong.
- **Fix:** Start the PostgreSQL service (on Windows, use the Services app). Confirm `DB_PORT` (default `5432`).

### `database "grp10_unisyncdb" does not exist`
- **Cause:** The database was not created, or `DB_NAME` does not match it.
- **Fix:** Run `psql -U postgres -c "CREATE DATABASE grp10_unisyncdb;"` (or create it in pgAdmin) and make sure `.env` uses the same name.

### `relation "..." does not exist`
- **Cause:** `db/init.sql` was not run, or it ran against a different database.
- **Fix:** Run `psql -U postgres -d grp10_unisyncdb -f db/init.sql`, then load the seed data.

### Seed script fails with duplicate key errors
- **Cause:** `seed.sql` was run more than once on the same data.
- **Fix:** Re-run `db/init.sql` (it drops and recreates all tables), then run `db/seed.sql` once. Warning: this erases all existing data.

### `permission denied for table ...`
- **Cause:** The user in `.env` has no privileges on the tables, e.g. the tables were created by `postgres` and `unisync_app` was never granted access.
- **Fix:** Use the `postgres` account for local testing, or grant privileges as described in [`database-roles.md`](database-roles.md).

## 2. PHP

### `could not find driver`
- **Cause:** The `pdo_pgsql` extension is disabled.
- **Fix:** Enable it in `php.ini` (`extension=pdo_pgsql`), then restart `php -S`.

### Errors about `fileinfo` or `mbstring`
- **Cause:** The extension is disabled.
- **Fix:** Enable `extension=fileinfo` and `extension=mbstring` in `php.ini`, then restart the server.

### Extension changes have no effect
- **Cause:** You edited a different `php.ini` than the one PHP uses.
- **Fix:** Run `php --ini` to see the active file, edit that one, and check with `php -m`.

### Blank page or HTTP 500
- **Cause:** Usually a database connection error or a missing extension.
- **Fix:** Read the error output in the terminal running `php -S`. You can also test the connection with `php config/db_connection_test.php`.

### Port 8000 already in use
- **Cause:** Another program is using the port.
- **Fix:** Start on another port, e.g. `php -S localhost:8080`, and open that address.

## 3. Application

### Page loads without styling or scripts
- **Cause:** The server was started from the wrong folder, so asset paths do not resolve.
- **Fix:** Run `php -S localhost:8000` from the project root (the folder with `index.php`), not from inside `public/`.

### Event image upload fails
- **Cause:** `public/uploads/events/` is missing or not writable.
- **Fix:** Create the folder and make it writable. On Windows, check the folder's security permissions.

### Map is blank
- **Cause:** The browser has no internet access, so map tiles from OpenStreetMap cannot load.
- **Fix:** Connect to the internet and reload.

### Icons are missing
- **Cause:** Icons load from an online source and the browser has no internet access.
- **Fix:** Connect to the internet and reload.

### Cannot log in
- **Cause:** Wrong credentials, or the seed data was not loaded.
- **Fix:** Confirm `db/seed.sql` ran without errors, then log in with a sample account, e.g. `testaccount1@gmail.com` / `Password123!`. The full list is in the **Sample Accounts** section of the README. Other seeded accounts use placeholder password hashes and cannot log in.

### Registering a new account fails with a duplicate key error
- **Cause:** The ID sequence was not reset after the seed data inserted explicit IDs.
- **Fix:** Run this once, then try again:
```sql
SELECT setval(pg_get_serial_sequence('account','account_id'), (SELECT MAX(account_id) FROM account));
```
  Re-running `db/init.sql` and `db/seed.sql` also fixes it if the seed script already resets the sequence.

## 4. Still stuck?

1. Re-run the steps in the README from the top on a fresh database.
2. Check `.env` against `.env.example` for typos or missing variables.
3. Report the exact error message and the step where it happened to the group (see the README team list).