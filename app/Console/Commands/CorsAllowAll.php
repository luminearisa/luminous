<?php

namespace App\Console\Commands;

use App\Core\CorsConfig;

/**
 * CORS Allow All Command
 * Enable CORS for all origins
 */
class CorsAllowAll extends Command
{
    public function execute(array $arguments): void
    {
        try {
            $result = CorsConfig::allowAll();

            if ($result) {
                $this->success('CORS: Allow all origins enabled');
                $this->warning('⚠ Warning: This allows requests from ANY origin');
                $this->info('This mode is recommended for development only');
                $this->info('');
                $this->info('To enable whitelist mode, use: php lumi cors:whitelist');
            } else {
                $this->error('Failed to update CORS configuration');
            }
        } catch (\Exception $e) {
            $this->error('Failed to enable allow all: ' . $e->getMessage());
        }
    }
}
