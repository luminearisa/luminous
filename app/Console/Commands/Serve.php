<?php

namespace App\Console\Commands;

/**
 * Serve Command
 * Run PHP development server
 */
class Serve extends Command
{
    public function execute(array $arguments): void
    {
        $host = $this->argument($arguments, 0, 'localhost');
        $port = $this->argument($arguments, 1, '8000');

        $this->info("Luminous Framework Development Server");
        $this->info("====================================");
        $this->info("");
        $this->info("Server running at: http://$host:$port");
        $this->info("Press Ctrl+C to stop");
        $this->info("");

        // Start PHP built-in server
        $command = "php -S $host:$port -t " . getcwd();
        passthru($command);
    }
}
