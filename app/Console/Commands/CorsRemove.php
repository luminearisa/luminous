<?php

namespace App\Console\Commands;

use App\Core\CorsConfig;

/**
 * CORS Remove Command
 * Remove origin from CORS whitelist
 */
class CorsRemove extends Command
{
    public function execute(array $arguments): void
    {
        $origin = $this->argument($arguments, 0);

        if (!$origin) {
            $this->error('Origin is required');
            $this->info('Usage: php lumi cors:remove <origin>');
            $this->info('Example: php lumi cors:remove https://example.com');
            return;
        }

        try {
            $result = CorsConfig::removeOrigin($origin);

            if ($result) {
                $this->success("Removed origin: $origin");
            } else {
                $this->warning("Origin not found: $origin");
                $this->info("Use 'php lumi cors:list' to see all origins");
            }
        } catch (\Exception $e) {
            $this->error('Failed to remove origin: ' . $e->getMessage());
        }
    }
}
