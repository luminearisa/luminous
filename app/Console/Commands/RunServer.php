<?php

namespace App\Console\Commands;

/**
 * Run Server Command
 */
class RunServer extends Command
{
    public function execute(array $arguments): void
    {
        $host = $this->argument($arguments, 0, 'localhost');
        $port = $this->argument($arguments, 1, '8000');

        $this->info("Starting Luminous development server...");
        $this->info("Server running at: http://$host:$port");
        $this->info("Press Ctrl+C to stop");
        echo "\n";

        // Start PHP built-in server
        $command = "php -S $host:$port -t " . __DIR__ . "/../../../";
        passthru($command);
    }
}
