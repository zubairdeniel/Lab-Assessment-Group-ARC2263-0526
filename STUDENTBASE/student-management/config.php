<?php
/**
 * StudentBase — config.php
 * Shared database configuration, included by both /admin and /client PHP files.
 * Update these values to match your environment.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'studentbase');
define('DB_USER', 'root');        // change in production
define('DB_PASS', '');            // change in production
define('DB_CHAR', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}
