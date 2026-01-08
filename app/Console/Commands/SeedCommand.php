<?php

namespace App\Console\Commands;

/**
 * Seed Command
 * 
 * Run database seeders to populate the database with sample data.
 * Usage: 
 *   php lumi db:seed                     # Run all seeders
 *   php lumi db:seed --class=UserSeeder  # Run specific seeder
 */
class SeedCommand
{
    /**
     * Execute the command
     * 
     * @param array $args
     * @return void
     */
    public function execute(array $args): void
    {
        echo "\033[33m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
        echo "\033[33m  Database Seeding - Luminous Framework\033[0m\n";
        echo "\033[33m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n\n";

        // Check if specific seeder is requested
        $specificClass = $this->getClassOption($args);

        if ($specificClass) {
            $this->runSeeder($specificClass);
        } else {
            $this->runAllSeeders();
        }

        echo "\n\033[32m✓ Database seeding completed!\033[0m\n";
    }

    /**
     * Get --class option from arguments
     * 
     * @param array $args
     * @return string|null
     */
    private function getClassOption(array $args): ?string
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--class=')) {
                return str_replace('--class=', '', $arg);
            }
        }
        return null;
    }

    /**
     * Run all seeders in the Seeders directory
     * 
     * @return void
     */
    private function runAllSeeders(): void
    {
        $seedersPath = __DIR__ . '/../../Database/Seeders';

        if (!is_dir($seedersPath)) {
            echo "\033[31mError: Seeders directory not found.\033[0m\n";
            return;
        }

        $seederFiles = glob($seedersPath . '/*Seeder.php');

        if (empty($seederFiles)) {
            echo "\033[33mNo seeders found in app/Database/Seeders/\033[0m\n";
            echo "Create a seeder: php lumi make:seeder UserSeeder\n";
            return;
        }

        foreach ($seederFiles as $file) {
            $className = basename($file, '.php');
            $this->runSeeder($className);
        }
    }

    /**
     * Run a specific seeder
     * 
     * @param string $className
     * @return void
     */
    private function runSeeder(string $className): void
    {
        // Ensure class name ends with 'Seeder'
        if (!str_ends_with($className, 'Seeder')) {
            $className .= 'Seeder';
        }

        $fullClassName = "App\\Database\\Seeders\\{$className}";

        // Check if class exists
        if (!class_exists($fullClassName)) {
            echo "\033[31mError: Seeder '{$className}' not found.\033[0m\n";
            return;
        }

        try {
            $seeder = new $fullClassName();
            
            echo "\033[36m→ Running {$className}...\033[0m\n";
            $seeder->run();
            
        } catch (\Exception $e) {
            echo "\033[31m✗ Error in {$className}: {$e->getMessage()}\033[0m\n";
        }
    }
}
