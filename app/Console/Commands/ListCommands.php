<?php

namespace App\Console\Commands;

use App\Console\Kernel;

/**
 * List Commands
 */
class ListCommands extends Command
{
    public function execute(array $arguments): void
    {
        echo "\n";
        echo "  Luminous Framework - Available Commands\n";
        echo "  ========================================\n\n";

        $commands = [
            'make:controller' => 'Create a new controller class',
            'make:model' => 'Create a new model class',
            'make:migration' => 'Create a new database migration',
            'make:middleware' => 'Create a new middleware class',
            'migrate' => 'Run all pending database migrations',
            'run' => 'Start development server (default: localhost:8000)',
            'list' => 'Display list of available commands',
        ];

        $corsCommands = [
            'cors:add' => 'Add origin to CORS whitelist',
            'cors:remove' => 'Remove origin from CORS whitelist',
            'cors:list' => 'List all CORS configuration',
            'cors:allow-all' => 'Allow all origins (development mode)',
            'cors:disallow-all' => 'Disable all CORS',
            'cors:whitelist' => 'Enable whitelist mode',
        ];

        $rateLimitCommands = [
            'limit:set' => 'Set rate limit (60, 600, unlimited)',
        ];

        foreach ($commands as $command => $description) {
            echo "  \033[32m$command\033[0m\n";
            echo "    $description\n\n";
        }

        echo "  \033[1mCORS Management:\033[0m\n\n";

        foreach ($corsCommands as $command => $description) {
            echo "  \033[36m$command\033[0m\n";
            echo "    $description\n\n";
        }

        echo "  \033[1mRate Limiting:\033[0m\n\n";

        foreach ($rateLimitCommands as $command => $description) {
            echo "  \033[33m$command\033[0m\n";
            echo "    $description\n\n";
        }

        echo "  Usage: php lumi <command> [arguments]\n";
        echo "\n";
    }
}
