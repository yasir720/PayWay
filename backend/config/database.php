<?php
/**
 * Database configuration and connection setup.
 * Establishes a PDO connection to PostgreSQL using environment variables.
 */

require_once 'env.php';

// Build DSN string for PostgreSQL connection
$dsn = "pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}";

try {
    // Create PDO instance with database credentials
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

    // Enable exception mode for error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Terminate script on connection failure
    die('Database connection failed');
}
