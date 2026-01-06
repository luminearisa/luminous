<?php

namespace App\Console\Commands;

use App\Core\CorsConfig;

/**
 * CORS Add Command
 * Add origin to CORS whitelist
 */
class CorsAdd extends Command
{
    public function execute(array $arguments): void
    {
        $origin = $this->argument($arguments, 0);
        $endpoint = $this->argument($arguments, 1, '/api/*');

        if (!$origin) {
            $this->error('Origin is required');
            $this->info('Usage: php lumi cors:add <origin> [endpoint]');
            $this->info('Example: php lumi cors:add https://example.com /api/*');
            return;
        }

        // Validate origin format
        if (!$this->isValidOrigin($origin)) {
            $this->error('Invalid origin format');
            $this->info('Origin must be a valid URL (e.g., https://example.com)');
            return;
        }

        // Parse endpoints (support multiple comma-separated)
        $endpoints = array_map('trim', explode(',', $endpoint));

        try {
            $result = CorsConfig::addOrigin($origin, $endpoints);

            if ($result) {
                $this->success("Added origin: $origin");
                $this->info("Endpoints: " . implode(', ', $endpoints));
                $this->info("Methods: GET, POST, PUT, PATCH, DELETE (default)");
            } else {
                $this->warning("Origin already exists: $origin");
                $this->info("Use 'php lumi cors:remove $origin' to remove it first");
            }
        } catch (\Exception $e) {
            $this->error('Failed to add origin: ' . $e->getMessage());
        }
    }

    /**
     * Validate origin format
     */
    private function isValidOrigin(string $origin): bool
    {
        // Allow * for wildcard
        if ($origin === '*') {
            return true;
        }

        // Validate URL format
        $parsed = parse_url($origin);
        
        if (!isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }

        // Only allow http and https
        if (!in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }

        return true;
    }
}
