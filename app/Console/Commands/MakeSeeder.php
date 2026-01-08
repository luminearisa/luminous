<?php

namespace App\Console\Commands;

/**
 * Make Seeder Command
 * 
 * Generate a new seeder class file.
 * Usage: php lumi make:seeder UserSeeder
 */
class MakeSeeder
{
    /**
     * Execute the command
     * 
     * @param array $args
     * @return void
     */
    public function execute(array $args): void
    {
        if (!isset($args[1])) {
            echo "\033[31mError: Seeder name is required.\033[0m\n";
            echo "Usage: php lumi make:seeder <SeederName>\n";
            echo "Example: php lumi make:seeder UserSeeder\n";
            return;
        }

        $seederName = $args[1];
        
        // Ensure seeder name ends with 'Seeder'
        if (!str_ends_with($seederName, 'Seeder')) {
            $seederName .= 'Seeder';
        }

        $seederPath = __DIR__ . '/../../Database/Seeders/' . $seederName . '.php';

        // Check if seeder already exists
        if (file_exists($seederPath)) {
            echo "\033[31mError: Seeder '{$seederName}' already exists.\033[0m\n";
            return;
        }

        // Create seeder file
        $content = $this->getSeederTemplate($seederName);
        
        file_put_contents($seederPath, $content);

        echo "\033[32m✓ Seeder created successfully!\033[0m\n";
        echo "File: app/Database/Seeders/{$seederName}.php\n";
        echo "\nNext steps:\n";
        echo "1. Edit the seeder: app/Database/Seeders/{$seederName}.php\n";
        echo "2. Run seeder: php lumi db:seed --class={$seederName}\n";
    }

    /**
     * Get seeder template
     * 
     * @param string $seederName
     * @return string
     */
    private function getSeederTemplate(string $seederName): string
    {
        $tableName = $this->getTableName($seederName);
        
        return <<<PHP
<?php

namespace App\Database\Seeders;

use App\Database\Seeder;

/**
 * {$seederName}
 * 
 * Seed data for {$tableName} table
 */
class {$seederName} extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @return void
     */
    public function run(): void
    {
        echo "Seeding {$tableName} table...\\n";

        // Clear existing data (optional)
        // \$this->clear('{$tableName}');

        // Example: Insert sample data
        // \$this->insert('{$tableName}', [
        //     'name' => 'Sample Data',
        //     'created_at' => date('Y-m-d H:i:s')
        // ]);

        echo "✓ {$tableName} seeded successfully!\\n";
    }
}

PHP;
    }

    /**
     * Get table name from seeder name
     * 
     * @param string $seederName
     * @return string
     */
    private function getTableName(string $seederName): string
    {
        // Remove 'Seeder' suffix
        $name = str_replace('Seeder', '', $seederName);
        
        // Convert to snake_case and pluralize
        $name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
        
        // Simple pluralization
        if (!str_ends_with($name, 's')) {
            $name .= 's';
        }
        
        return $name;
    }
}
