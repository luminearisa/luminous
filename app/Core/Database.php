<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Class
 * Handle database connections with support for MySQL and SQLite
 */
class Database
{
    private static ?PDO $connection = null;
    private static string $driver;

    /**
     * Get database connection
     */
    public static function connection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }

        return self::$connection;
    }

    /**
     * Connect to database
     */
    private static function connect(): void
    {
        self::$driver = Env::get('DB_CONNECTION', 'mysql');

        try {
            if (self::$driver === 'sqlite') {
                self::connectSqlite();
            } else {
                self::connectMysql();
            }

            // Set PDO attributes
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Connect to MySQL database
     */
    private static function connectMysql(): void
    {
        $host = Env::get('DB_HOST', 'localhost');
        $port = Env::get('DB_PORT', '3306');
        $dbname = Env::get('DB_NAME');
        $username = Env::get('DB_USER');
        $password = Env::get('DB_PASS', '');
        $charset = Env::get('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

        self::$connection = new PDO($dsn, $username, $password);
    }

    /**
     * Connect to SQLite database
     */
    private static function connectSqlite(): void
    {
        $dbPath = Env::get('DB_PATH', __DIR__ . '/../../database/database.sqlite');

        // Create directory if not exists
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Create file if not exists
        if (!file_exists($dbPath)) {
            touch($dbPath);
        }

        $dsn = "sqlite:$dbPath";
        self::$connection = new PDO($dsn);
    }

    /**
     * Execute query
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $connection = self::connection();
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    /**
     * Fetch all results
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $statement = self::query($sql, $params);
        return $statement->fetchAll();
    }

    /**
     * Fetch single result
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $statement = self::query($sql, $params);
        $result = $statement->fetch();
        return $result ?: null;
    }

    /**
     * Insert record
     */
    public static function insert(string $table, array $data): bool
    {
        $fields = array_keys($data);
        $values = array_values($data);
        
        $fieldList = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO $table ($fieldList) VALUES ($placeholders)";
        
        $statement = self::query($sql, $values);
        return $statement->rowCount() > 0;
    }

    /**
     * Update record
     */
    public static function update(string $table, array $data, string $where, array $whereParams = []): bool
    {
        $setClause = [];
        $values = [];

        foreach ($data as $field => $value) {
            $setClause[] = "$field = ?";
            $values[] = $value;
        }

        $setClauseString = implode(', ', $setClause);
        $sql = "UPDATE $table SET $setClauseString WHERE $where";

        $params = array_merge($values, $whereParams);
        $statement = self::query($sql, $params);
        
        return $statement->rowCount() > 0;
    }

    /**
     * Delete record
     */
    public static function delete(string $table, string $where, array $params = []): bool
    {
        $sql = "DELETE FROM $table WHERE $where";
        $statement = self::query($sql, $params);
        return $statement->rowCount() > 0;
    }

    /**
     * Get last insert ID
     */
    public static function lastInsertId(): string
    {
        return self::connection()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool
    {
        return self::connection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): bool
    {
        return self::connection()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback(): bool
    {
        return self::connection()->rollBack();
    }

    /**
     * Check if in transaction
     */
    public static function inTransaction(): bool
    {
        return self::connection()->inTransaction();
    }

    /**
     * Get current driver
     */
    public static function driver(): string
    {
        return self::$driver ?? Env::get('DB_CONNECTION', 'mysql');
    }

    /**
     * Close connection
     */
    public static function close(): void
    {
        self::$connection = null;
    }
}
