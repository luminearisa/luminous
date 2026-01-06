<?php

namespace App\Console\Commands;

use App\Core\CorsConfig;

/**
 * CORS Disallow All Command
 * Disable all CORS requests
 */
class CorsDisallowAll extends Command
{
    public function execute(array $arguments): void
    {
        try {
            $result = CorsConfig::disallowAll();

            if ($result) {
                $this->success('CORS: Disabled');
                $this->info('All cross-origin requests will be blocked');
                $this->info('');
                $this->info('To enable CORS, use:');
                $this->info('  - php lumi cors:allow-all (allow all origins)');
                $this->info('  - php lumi cors:whitelist (enable whitelist mode)');
            } else {
                $this->error('Failed to update CORS configuration');
            }
        } catch (\Exception $e) {
            $this->error('Failed to disable CORS: ' . $e->getMessage());
        }
    }
}
