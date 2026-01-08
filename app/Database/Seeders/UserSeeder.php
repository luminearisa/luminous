<?php

namespace App\Database\Seeders;

use App\Database\Seeder;
use App\Helpers\Hash;

/**
 * UserSeeder
 * 
 * Seed sample users for testing
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @return void
     */
    public function run(): void
    {
        echo "Seeding users table...\n";

        // Clear existing users (optional - comment out if you want to keep existing data)
        // $this->clear('users');

        // Sample users
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@luminous.test',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@luminous.test',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@luminous.test',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@luminous.test',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@luminous.test',
                'password' => Hash::make('password123'),
                'role' => 'moderator',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($users as $user) {
            $this->insert('users', $user);
        }

        echo "✓ Users seeded successfully! (5 users created)\n";
        echo "  - admin@luminous.test (password: password123)\n";
        echo "  - john@luminous.test (password: password123)\n";
        echo "  - jane@luminous.test (password: password123)\n";
        echo "  - bob@luminous.test (password: password123)\n";
        echo "  - alice@luminous.test (password: password123)\n";
    }
}
