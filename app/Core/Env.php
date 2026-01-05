<?php

namespace App\Core;

/**
 * Env Class
 * Load and manage environment variables and configuration
 */
class Env
{
    private static array $env = [];
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Load environment variables from .env file
     */
    public static function load(string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = $path ?? __DIR__ . '/../../.env';

        if (!file_exists($envFile)) {
            throw new \RuntimeException('.env file not found');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes
                $value = trim($value, '"\'');

                self::$env[$key] = $value;
                
                // Set as environment variable
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                }
                if (!array_key_exists($key, $_SERVER)) {
                    $_SERVER[$key] = $value;
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable
     */
    public static function get(string $key, $default = null)
    {
        return self::$env[$key] ?? $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    /**
     * Set environment variable
     */
    public static function set(string $key, $value): void
    {
        self::$env[$key] = $value;
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    /**
     * Check if environment variable exists
     */
    public static function has(string $key): bool
    {
        return isset(self::$env[$key]) || isset($_ENV[$key]) || isset($_SERVER[$key]);
    }

    /**
     * Load configuration from config.lumi file
     */
    public static function loadConfig(string $path = null): void
    {
        $configFile = $path ?? __DIR__ . '/../../config/config.lumi';

        if (!file_exists($configFile)) {
            throw new \RuntimeException('config.lumi file not found');
        }

        $content = file_get_contents($configFile);
        $config = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid config.lumi format: ' . json_last_error_msg());
        }

        self::$config = $config;
    }

    /**
     * Get configuration value
     * Supports dot notation: config('app.name')
     * Supports env reference: env:KEY_NAME
     */
    public static function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        // Check if value is env reference
        if (is_string($value) && strpos($value, 'env:') === 0) {
            $envKey = substr($value, 4);
            return self::get($envKey, $default);
        }

        return $value;
    }

    /**
     * Get all configuration
     */
    public static function allConfig(): array
    {
        return self::$config;
    }

    /**
     * Get all environment variables
     */
    public static function all(): array
    {
        return self::$env;
    }
}
