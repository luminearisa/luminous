<?php

use App\Core\Database;

/**
 * Migration: CreateUsersTable
 */
return new class {
    /**
     * Run the migration
     */
    public function up(): void
    {
        $driver = Database::driver();

        if ($driver === 'sqlite') {
            $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
        } else {
            $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
        }

        Database::query($sql);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS users";
        Database::query($sql);
    }
};
