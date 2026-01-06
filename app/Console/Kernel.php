<?php

namespace App\Console;

/**
 * Console Kernel
 * Handle CLI commands
 */
class Kernel
{
    private array $commands = [];

    public function __construct()
    {
        $this->registerCommands();
    }

    /**
     * Register available commands
     */
    private function registerCommands(): void
    {
        $this->commands = [
            'make:controller' => Commands\MakeController::class,
            'make:model' => Commands\MakeModel::class,
            'make:migration' => Commands\MakeMigration::class,
            'make:middleware' => Commands\MakeMiddleware::class,
            'migrate' => Commands\Migrate::class,
            'serve' => Commands\Serve::class,
            'run' => Commands\Serve::class,
            'list' => Commands\ListCommands::class,
            // CORS commands
            'cors:add' => Commands\CorsAdd::class,
            'cors:remove' => Commands\CorsRemove::class,
            'cors:list' => Commands\CorsList::class,
            'cors:allow-all' => Commands\CorsAllowAll::class,
            'cors:disallow-all' => Commands\CorsDisallowAll::class,
            'cors:whitelist' => Commands\CorsWhitelist::class,
            // Rate Limit commands
            'limit:set' => Commands\RateLimitSet::class,
        ];
    }

    /**
     * Handle CLI command
     */
    public function handle(array $argv): void
    {
        array_shift($argv); // Remove script name

        if (empty($argv)) {
            $this->showHelp();
            return;
        }

        $commandName = $argv[0];
        $arguments = array_slice($argv, 1);

        if (!isset($this->commands[$commandName])) {
            echo "Command '$commandName' not found.\n";
            echo "Run 'php lumi list' to see available commands.\n";
            exit(1);
        }

        $commandClass = $this->commands[$commandName];
        $command = new $commandClass();
        $command->execute($arguments);
    }

    /**
     * Show help message
     */
    private function showHelp(): void
    {
        echo "\n";
        echo "  _                    _                    \n";
        echo " | |   _   _ _ __ ___ (_)_ __   ___  _   _ ___\n";
        echo " | |  | | | | '_ ` _ \| | '_ \ / _ \| | | / __|\n";
        echo " | |__| |_| | | | | | | | | | | (_) | |_| \__ \\\n";
        echo " |_____\__,_|_| |_| |_|_|_| |_|\___/ \__,_|___/\n";
        echo "\n";
        echo " Luminous Framework CLI\n";
        echo " Version 1.0.0\n";
        echo "\n";
        echo "Usage: php lumi <command> [arguments]\n";
        echo "\n";
        echo "Available Commands:\n";
        echo "  make:controller    Create a new controller\n";
        echo "  make:model         Create a new model\n";
        echo "  make:migration     Create a new migration\n";
        echo "  make:middleware    Create a new middleware\n";
        echo "  migrate            Run database migrations\n";
        echo "  run                Start development server\n";
        echo "  list               List all available commands\n";
        echo "\n";        echo "CORS Commands:\n";
        echo "  cors:add           Add origin to CORS whitelist\n";
        echo "  cors:remove        Remove origin from whitelist\n";
        echo "  cors:list          List all CORS configuration\n";
        echo "  cors:allow-all     Allow all origins (development)\n";
        echo "  cors:disallow-all  Disable all CORS\n";
        echo "  cors:whitelist     Enable whitelist mode\n";
        echo "\n";
        echo "Rate Limiting:\n";
        echo "  limit:set          Set rate limit (e.g., 60 or unlimited)\n";
        echo "\n";
    }

    /**
     * Get all commands
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
