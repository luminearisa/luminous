<?php

namespace App\Database;

use App\Core\Database;

/**
 * Base Seeder Class
 * 
 * All seeders should extend this class and implement the run() method.
 */
abstract class Seeder
{
    /**
     * Database instance
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Run the database seeds.
     * 
     * @return void
     */
    abstract public function run(): void;

    /**
     * Call another seeder
     * 
     * @param string $seederClass
     * @return void
     */
    protected function call(string $seederClass): void
    {
        $seeder = new $seederClass();
        $seeder->run();
    }

    /**
     * Insert data into a table
     * 
     * @param string $table
     * @param array $data
     * @return bool
     */
    protected function insert(string $table, array $data): bool
    {
        return Database::insert($table, $data);
    }

    /**
     * Truncate a table (clear all data)
     * 
     * @param string $table
     * @return void
     */
    protected function truncate(string $table): void
    {
        $this->db->exec("TRUNCATE TABLE {$table}");
    }

    /**
     * Delete all data from a table
     * 
     * @param string $table
     * @return void
     */
    protected function clear(string $table): void
    {
        Database::delete($table, "1=1");
    }
}
