<?php

namespace App\Console\Commands;

use App\Core\CorsConfig;

/**
 * CORS List Command
 * List all allowed origins
 */
class CorsList extends Command
{
    public function execute(array $arguments): void
    {
        try {
            $config = CorsConfig::load();

            echo "\n";
            echo "  \033[1mCORS Configuration\033[0m\n";
            echo "  ==================\n\n";

            // Status
            if ($config['enabled']) {
                $this->success('Status: Enabled');
            } else {
                $this->error('Status: Disabled');
            }

            // Mode
            if ($config['allow_all']) {
                $this->warning('Mode: Allow All Origins (*)');
                echo "\n";
                $this->info('⚠ All origins are allowed. Not recommended for production!');
            } else {
                $this->info('Mode: Whitelist');
                
                $origins = $config['allowed_origins'] ?? [];
                
                if (empty($origins)) {
                    echo "\n";
                    $this->warning('No origins in whitelist');
                    $this->info('Add origins with: php lumi cors:add <origin>');
                } else {
                    echo "\n";
                    echo "  \033[1mWhitelisted Origins:\033[0m\n\n";
                    
                    foreach ($origins as $index => $origin) {
                        $num = $index + 1;
                        echo "  \033[36m[$num]\033[0m \033[32m{$origin['origin']}\033[0m\n";
                        echo "      Methods: " . implode(', ', $origin['methods']) . "\n";
                        echo "      Endpoints: " . implode(', ', $origin['endpoints']) . "\n";
                        echo "\n";
                    }
                }
            }

            // Additional settings
            echo "  \033[1mSettings:\033[0m\n";
            echo "  - Credentials: " . ($config['supports_credentials'] ? 'Enabled' : 'Disabled') . "\n";
            echo "  - Max Age: " . $config['max_age'] . " seconds\n";
            echo "  - Allowed Headers: " . implode(', ', $config['allowed_headers']) . "\n";
            
            if (!empty($config['exposed_headers'])) {
                echo "  - Exposed Headers: " . implode(', ', $config['exposed_headers']) . "\n";
            }
            
            echo "\n";

        } catch (\Exception $e) {
            $this->error('Failed to load CORS configuration: ' . $e->getMessage());
        }
    }
}
