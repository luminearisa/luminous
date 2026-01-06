<?php

namespace App\Console\Commands;

use App\Core\CorsConfig;

/**
 * CORS Whitelist Command
 * Enable CORS with whitelist mode
 */
class CorsWhitelist extends Command
{
    public function execute(array $arguments): void
    {
        try {
            $result = CorsConfig::enableWhitelist();

            if ($result) {
                $this->success('CORS: Whitelist mode enabled');
                $this->info('Only whitelisted origins will be allowed');
                $this->info('');
                $this->info('Manage whitelist:');
                $this->info('  - php lumi cors:add <origin> [endpoint]');
                $this->info('  - php lumi cors:remove <origin>');
                $this->info('  - php lumi cors:list');
            } else {
                $this->error('Failed to update CORS configuration');
            }
        } catch (\Exception $e) {
            $this->error('Failed to enable whitelist: ' . $e->getMessage());
        }
    }
}
