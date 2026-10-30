<?php

// Run this and check debug console, if it prints "object(PgSql\Connection)#1 (0)" connection is successful

$host     = 'localhost';
$port     = '5432';
$db       = 'grp10_unisyncdb';
$user     = 'postgres';
$password = '';

// Load environment variables from local .env if present
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    $host     = $env['DB_HOST'] ?? $host;
    $port     = $env['DB_PORT'] ?? $port;
    $db       = $env['DB_NAME'] ?? $db;
    $user     = $env['DB_USER'] ?? $user;
    $password = $env['DB_PASSWORD'] ?? $password;
}

// Build a clean connection string without line breaks
$conn_string = "host={$host} port={$port} dbname={$db} user={$user} password={$password}";

$conn = pg_connect($conn_string);

var_dump($conn);

?>