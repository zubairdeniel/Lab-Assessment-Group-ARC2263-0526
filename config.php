<?php
/**
 * StudentBase — Configuration & Database Connection
 * Switched to Supabase PostgreSQL with Debug Mode
 */

// 1. Pull connection credentials from environment variables
$host     = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
$db_name  = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
$user     = $_ENV['DB_USER'] ?? getenv('DB_USER');
$password = $_ENV['DB_PASS'] ?? getenv('DB_PASS');
$port     = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '5432';

try {
    // 2. Build the PostgreSQL Data Source Name (DSN) string
    $dsn = "pgsql:host=$host;port=$port;dbname=$db_name";

    // 3. Establish connection with robust error configurations
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {
    // Force the network response to show the exact system error
    header('Content-Type: text/plain');
    echo "DATABASE DEBUG ERROR:\n";
    echo $e->getMessage();
    exit;
}