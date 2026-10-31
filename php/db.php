<?php

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

$dsn = "pgsql:host=$host;port=$port;dbname=$db;";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
