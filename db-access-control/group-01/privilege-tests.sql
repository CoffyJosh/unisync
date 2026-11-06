-- Check access to another database
SELECT
    datname,
    has_database_privilege(
        'dropboxed_app',
        datname,
        'CONNECT'
    ) AS can_connect
FROM pg_database
WHERE datname <> 'dropboxed_db'
  AND datname <> 'postgres'
  AND datallowconn = true;