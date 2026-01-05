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
        $requestedPort = $this->argument($arguments, 1, '8000');

        // Find available port
        $port = $this->findAvailablePort($host, (int)$requestedPort);

        if ($port !== (int)$requestedPort) {
            $this->warning("Port $requestedPort is already in use");
            $this->info("Using port $port instead");
            $this->info("");
        }

        $this->info("Luminous Framework Development Server");
        $this->info("====================================");
        $this->info("");
        $this->success("Server running at: http://$host:$port");
        $this->info("Document root: " . getcwd());
        $this->info("Press Ctrl+C to stop");
        $this->info("");

        // Start PHP built-in server
        $command = "php -S $host:$port -t " . getcwd();
        passthru($command);
    }

    /**
     * Find available port starting from given port
     */
    private function findAvailablePort(string $host, int $startPort, int $maxAttempts = 10): int
    {
        $port = $startPort;

        for ($i = 0; $i < $maxAttempts; $i++) {
            if ($this->isPortAvailable($host, $port)) {
                return $port;
            }
            $port++;
        }

        // If no port found, return the last tried port and let it fail
        $this->warning("Could not find available port between $startPort and $port");
        return $startPort;
    }

    /**
     * Check if port is available
     */
    private function isPortAvailable(string $host, int $port): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        
        if (is_resource($connection)) {
            fclose($connection);
            return false; // Port is in use
        }
        
        return true; // Port is available
    }
}
