<?php

// server/config/database.php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Class Database
 *
 * Manages the database connection for the Collaborative Task Management System.
 * This class provides a static method to get a PDO database connection,
 * utilizing environment variables for configuration to ensure flexibility and security.
 * It implements a singleton pattern for the connection to prevent multiple connections
 * within a single request lifecycle, optimizing resource usage.
 */
class Database
{
    /**
     * @var PDO|null The single PDO connection instance.
     */
    private static ?PDO $connection = null;

    /**
     * Prevents direct instantiation of the Database class.
     * This enforces the singleton pattern, ensuring that the connection
     * can only be obtained via the static `getConnection` method.
     */
    private function __construct()
    {
        // Private constructor to enforce singleton pattern
    }

    /**
     * Prevents cloning of the Database instance.
     * This ensures that the singleton instance remains unique.
     */
    private function __clone()
    {
        // Private clone method to enforce singleton pattern
    }

    /**
     * Prevents unserializing of the Database instance.
     * This prevents external code from creating new instances through deserialization,
     * further enforcing the singleton pattern.
     *
     * @throws \Exception If an attempt is made to unserialize the singleton.
     */
    public function __wakeup()
    {
        // Private wakeup method to enforce singleton pattern
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * Establishes and returns a PDO database connection.
     *
     * It retrieves database credentials from environment variables (e.g., loaded from .env file).
     * If a connection already exists, it returns the existing one, adhering to the singleton pattern.
     *
     * @return PDO The PDO database connection object.
     * @throws PDOException If the database connection fails due to incorrect credentials,
     *                      server issues, or other configuration problems.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Retrieve database configuration from environment variables.
            // These variables should be defined in the .env file (e.g., via .env.example)
            // and loaded by a library like 'vlucas/phpdotenv' at application bootstrap
            // (e.g., in server/public/index.php).
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '3306';
            // The database name is specific to this 'Collaborative Task Management System' service.
            // In a microservice architecture, each service typically has its own dedicated database.
            $dbName = getenv('DB_NAME') ?: 'task_management_db';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';
            $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

            // Construct the Data Source Name (DSN) for MySQL.
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

            // PDO connection options for robust, secure, and performant connections.
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,      // Throw exceptions on errors, crucial for debugging and error handling.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,            // Fetch results as associative arrays by default, common and convenient.
                PDO::ATTR_EMULATE_PREPARES   => false,                       // Disable emulation for better security (prevents SQL injection) and performance.
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}",     // Ensure character set is correctly set for data integrity.