<?php
/**
 * Bella Vista Oasis Spa - Database Connection
 * 
 * Secure PDO connection using prepared statements and UTF-8 collation.
 */

// Database credentials
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_PORT') || define('DB_PORT', '3306');
defined('DB_NAME') || define('DB_NAME', 'bella_vista_spa');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');

/**
 * Returns a singleton or new PDO instance connected to the Bella Vista Spa database.
 *
 * @return PDO
 * @throws PDOException
 */
function get_db_connection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error internally in production
            error_log("Database Connection Error: " . $e->getMessage());
            throw new PDOException("Could not connect to the database. Please verify your database configuration.");
        }
    }

    return $pdo;
}

// Global $pdo instance for backward compatibility and direct inclusion
try {
    $pdo = get_db_connection();
} catch (PDOException $e) {
    $pdo = null;
}

return $pdo;
