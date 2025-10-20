<?php

/**
 * Database Class
 *
 * This class follows the Singleton design pattern to provide a single shared
 * instance of the database connection using PDO and ensures proper error handling.
 */
class Database {
    /**
     * @var Database|null The single instance of the Database class
     */
    private static $instance = null;

    /**
     * @var PDO|null The PDO connection object
     */
    private $connection;

    /**
     * Private constructor to prevent direct instantiation.
     * Initializes the database connection using SQLite and sets error mode to exception.
     */
    private function __construct() {
        try {
            $this->connection = new PDO("sqlite:ecobuddy.sqlite");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Terminate execution if the database connection fails
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the single shared instance of the Database class.
     *
     * @return Database The shared Database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection object.
     *
     * @return PDO The active PDO connection
     */
    public function getConnection() {
        return $this->connection;
    }
}






