<?php

namespace App\Console\Commands;

use App\Core\Database;
use App\Core\Env;

/**
 * Migrate Command
 */
class Migrate extends Command
{
    public function execute(array $arguments): void
    {
        $this->info('Running migrations...');

        // Initialize environment and database
        try {
            Env::load();
            Env::loadConfig();
            Database::connection();
        } catch (\Exception $e) {
            $this->error('Failed to initialize: ' . $e->getMessage());
            return;
        }

        // Create migrations table if not exists
        $this->createMigrationsTable();

        // Get migration files
        $migrationPath = __DIR__ . '/../../../database/migrations';
        
        if (!is_dir($migrationPath)) {
            $this->warning('No migrations directory found');
            return;
        }

        $files = glob($migrationPath . '/*.php');
        
        if (empty($files)) {
            $this->warning('No migration files found');
            return;
        }

        sort($files);

        $executed = 0;

        foreach ($files as $file) {
            $fileName = basename($file);

            // Check if already migrated
            if ($this->isMigrated($fileName)) {
                continue;
            }

            $this->info("Migrating: $fileName");

            try {
                $migration = require $file;
                $migration->up();
                
                $this->recordMigration($fileName);
                $this->success("Migrated: $fileName");
                $executed++;
            } catch (\Exception $e) {
                $this->error("Failed to migrate $fileName: " . $e->getMessage());
                break;
            }
        }

        if ($executed === 0) {
            $this->info('Nothing to migrate');
        } else {
            $this->success("Migrated $executed file(s)");
        }
    }

    private function createMigrationsTable(): void
    {
        $driver = Database::driver();

        if ($driver === 'sqlite') {
            $sql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration VARCHAR(255) NOT NULL,
                    migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
        } else {
            $sql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL,
                    migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
        }

        Database::query($sql);
    }

    private function isMigrated(string $fileName): bool
    {
        $sql = "SELECT COUNT(*) as count FROM migrations WHERE migration = ?";
        $result = Database::fetch($sql, [$fileName]);
        return ($result['count'] ?? 0) > 0;
    }

    private function recordMigration(string $fileName): void
    {
        Database::insert('migrations', ['migration' => $fileName]);
    }
}
