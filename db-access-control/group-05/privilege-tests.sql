
-- Group 05: SHIORI Café
-- Activity 4: Privilege Verification
-- Results are based on checks actually performed.
-- No database permissions were modified.

-- 1. Database privileges
SELECT
    rolname,
    has_database_privilege(
        rolname, current_database(), 'CONNECT'
    ) AS can_connect,
    has_database_privilege(
        rolname, current_database(), 'CREATE'
    ) AS can_create,
    has_database_privilege(
        rolname, current_database(), 'TEMP'
    ) AS can_temp
FROM pg_roles
WHERE rolname IN (
    'app_runtime',
    'app_runtime_grp',
    'app_admin_grp',
    'app_readonly_grp'
)
ORDER BY rolname;

-- 2. Schema privileges
SELECT
    nspname,
    has_schema_privilege(
        'app_runtime', oid, 'USAGE'
    ) AS runtime_usage,
    has_schema_privilege(
        'app_runtime', oid, 'CREATE'
    ) AS runtime_create,
    has_schema_privilege(
        'app_readonly_grp', oid, 'USAGE'
    ) AS readonly_usage,
    has_schema_privilege(
        'app_readonly_grp', oid, 'CREATE'
    ) AS readonly_create
FROM pg_namespace
WHERE nspname = 'shioricafe_schema';

-- 3. Table privileges
SELECT
    table_name,
    has_table_privilege(
        'app_runtime',
        quote_ident(table_schema) || '.' || quote_ident(table_name),
        'SELECT'
    ) AS can_select,
    has_table_privilege(
        'app_runtime',
        quote_ident(table_schema) || '.' || quote_ident(table_name),
        'INSERT'
    ) AS can_insert,
    has_table_privilege(
        'app_runtime',
        quote_ident(table_schema) || '.' || quote_ident(table_name),
        'UPDATE'
    ) AS can_update,
    has_table_privilege(
        'app_runtime',
        quote_ident(table_schema) || '.' || quote_ident(table_name),
        'DELETE'
    ) AS can_delete
FROM information_schema.tables
WHERE table_schema = 'shioricafe_schema'
  AND table_type = 'BASE TABLE'
ORDER BY table_name;

-- 4. Actual runtime access test
-- Expected result: permission denied for schema.
SET ROLE app_runtime;
SELECT * FROM shioricafe_schema.customer LIMIT 1;
