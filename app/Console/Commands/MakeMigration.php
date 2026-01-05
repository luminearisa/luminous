<?php

namespace App\Console\Commands;

/**
 * Make Migration Command
 */
class MakeMigration extends Command
{
    public function execute(array $arguments): void
    {
        $name = $this->argument($arguments, 0);

        if (!$name) {
            $this->error('Migration name is required');
            $this->info('Usage: php lumi make:migration create_users_table');
            return;
        }

        $timestamp = date('YmdHis');
        $fileName = $timestamp . '_' . $name . '.php';
        $path = __DIR__ . "/../../../database/migrations/$fileName";
        $className = $this->studly($name);

        $content = $this->getStub($className);

        $this->createFile($path, $content);
    }

    private function getStub(string $className): string
    {
        return <<<PHP
<?php

use App\Core\Database;

/**
 * Migration: $className
 */
return new class {
    /**
     * Run the migration
     */
    public function up(): void
    {
        \$sql = "
            CREATE TABLE IF NOT EXISTS sample_table (
                id INTEGER PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";

        Database::query(\$sql);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        \$sql = "DROP TABLE IF EXISTS sample_table";
        Database::query(\$sql);
    }
};

PHP;
    }
}
