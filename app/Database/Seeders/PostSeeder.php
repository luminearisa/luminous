<?php

namespace App\Database\Seeders;

use App\Database\Seeder;
use App\Core\Database;

/**
 * PostSeeder
 * 
 * Seed sample posts for testing
 */
class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @return void
     */
    public function run(): void
    {
        echo "Seeding posts table...\n";

        // Get user IDs for foreign key references
        $users = Database::query("SELECT id FROM users LIMIT 5");
        
        if (empty($users)) {
            echo "\033[33m⚠ Warning: No users found. Run UserSeeder first.\033[0m\n";
            echo "  php lumi db:seed --class=UserSeeder\n";
            return;
        }

        // Clear existing posts (optional)
        // $this->clear('posts');

        // Sample posts
        $posts = [
            [
                'user_id' => $users[0]['id'] ?? 1,
                'title' => 'Welcome to Luminous Framework',
                'content' => 'This is a sample post created by the seeder. Luminous is a lightweight PHP framework for building RESTful APIs.',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'user_id' => $users[1]['id'] ?? 1,
                'title' => 'Getting Started with REST APIs',
                'content' => 'Learn how to build modern REST APIs using Luminous Framework. It\'s simple, fast, and production-ready.',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],
            [
                'user_id' => $users[1]['id'] ?? 1,
                'title' => 'Database Migrations Made Easy',
                'content' => 'Luminous provides a simple yet powerful migration system to manage your database schema changes over time.',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))
            ],
            [
                'user_id' => $users[2]['id'] ?? 1,
                'title' => 'JWT Authentication in Luminous',
                'content' => 'Secure your API with JWT authentication. Luminous makes it easy to implement token-based auth in your application.',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                'user_id' => $users[2]['id'] ?? 1,
                'title' => 'CORS Configuration Guide',
                'content' => 'Configure Cross-Origin Resource Sharing (CORS) for your API to allow frontend applications to consume your endpoints.',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'user_id' => $users[3]['id'] ?? 1,
                'title' => 'Rate Limiting Best Practices',
                'content' => 'Protect your API from abuse with Luminous\' built-in rate limiting feature. Learn how to configure it properly.',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'user_id' => $users[3]['id'] ?? 1,
                'title' => 'Draft Post Example',
                'content' => 'This is a draft post that hasn\'t been published yet. It\'s useful for testing unpublished content.',
                'published' => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-12 hours'))
            ],
            [
                'user_id' => $users[4]['id'] ?? 1,
                'title' => 'Deploying to Shared Hosting',
                'content' => 'Luminous is designed to work perfectly on shared hosting environments like Hostinger, cPanel, and others.',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
            ],
            [
                'user_id' => $users[4]['id'] ?? 1,
                'title' => 'Custom Middleware Creation',
                'content' => 'Extend Luminous functionality by creating custom middleware for your specific application needs.',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'user_id' => $users[0]['id'] ?? 1,
                'title' => 'Database Seeding Tutorial',
                'content' => 'Populate your database with sample data using Luminous seeding feature. Perfect for testing and development!',
                'published' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ]
        ];

        $count = 0;
        foreach ($posts as $post) {
            $this->insert('posts', $post);
            $count++;
        }

        echo "✓ Posts seeded successfully! ({$count} posts created)\n";
    }
}
