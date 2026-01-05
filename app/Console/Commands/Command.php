<?php

namespace App\Console\Commands;

/**
 * Base Command Class
 */
abstract class Command
{
    abstract public function execute(array $arguments): void;

    /**
     * Print success message
     */
    protected function success(string $message): void
    {
        echo "\033[32m✓ $message\033[0m\n";
    }

    /**
     * Print error message
     */
    protected function error(string $message): void
    {
        echo "\033[31m✗ $message\033[0m\n";
    }

    /**
     * Print info message
     */
    protected function info(string $message): void
    {
        echo "\033[34mℹ $message\033[0m\n";
    }

    /**
     * Print warning message
     */
    protected function warning(string $message): void
    {
        echo "\033[33m⚠ $message\033[0m\n";
    }

    /**
     * Get argument by index
     */
    protected function argument(array $arguments, int $index, $default = null)
    {
        return $arguments[$index] ?? $default;
    }

    /**
     * Create file with content
     */
    protected function createFile(string $path, string $content): bool
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($path)) {
            $this->error("File already exists: $path");
            return false;
        }

        file_put_contents($path, $content);
        $this->success("Created: $path");
        return true;
    }

    /**
     * Convert string to StudlyCase
     */
    protected function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }

    /**
     * Convert string to snake_case
     */
    protected function snake(string $value): string
    {
        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = preg_replace('/(.)(?=[A-Z])/u', '$1_', $value);
        return strtolower($value);
    }
}
