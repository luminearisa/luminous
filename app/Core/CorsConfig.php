<?php

namespace App\Core;

/**
 * CorsConfig Class
 * Manage CORS configuration
 */
class CorsConfig
{
    private static ?array $config = null;
    private static string $configPath = __DIR__ . '/../../config/cors.lumi';

    /**
     * Load CORS configuration
     */
    public static function load(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        if (!file_exists(self::$configPath)) {
            // Return default config if file doesn't exist
            self::$config = self::getDefaultConfig();
            return self::$config;
        }

        $content = file_get_contents(self::$configPath);
        $config = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid cors.lumi format: ' . json_last_error_msg());
        }

        self::$config = $config;
        return self::$config;
    }

    /**
     * Save CORS configuration
     */
    public static function save(array $config): bool
    {
        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to encode CORS config: ' . json_last_error_msg());
        }

        $result = file_put_contents(self::$configPath, $json);
        
        if ($result !== false) {
            self::$config = $config;
            return true;
        }

        return false;
    }

    /**
     * Add origin to whitelist
     */
    public static function addOrigin(string $origin, array $endpoints = ['/api/*'], array $methods = null): bool
    {
        $config = self::load();

        // Check if origin already exists
        foreach ($config['allowed_origins'] as $existingOrigin) {
            if ($existingOrigin['origin'] === $origin) {
                return false; // Origin already exists
            }
        }

        // Default methods
        if ($methods === null) {
            $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        }

        // Add new origin
        $config['allowed_origins'][] = [
            'origin' => $origin,
            'methods' => $methods,
            'endpoints' => $endpoints
        ];

        return self::save($config);
    }

    /**
     * Remove origin from whitelist
     */
    public static function removeOrigin(string $origin): bool
    {
        $config = self::load();
        $found = false;

        $config['allowed_origins'] = array_filter($config['allowed_origins'], function($item) use ($origin, &$found) {
            if ($item['origin'] === $origin) {
                $found = true;
                return false;
            }
            return true;
        });

        // Re-index array
        $config['allowed_origins'] = array_values($config['allowed_origins']);

        if ($found) {
            return self::save($config);
        }

        return false;
    }

    /**
     * Enable allow all origins
     */
    public static function allowAll(): bool
    {
        $config = self::load();
        $config['enabled'] = true;
        $config['allow_all'] = true;
        return self::save($config);
    }

    /**
     * Disable all CORS
     */
    public static function disallowAll(): bool
    {
        $config = self::load();
        $config['enabled'] = false;
        $config['allow_all'] = false;
        return self::save($config);
    }

    /**
     * Enable CORS with whitelist
     */
    public static function enableWhitelist(): bool
    {
        $config = self::load();
        $config['enabled'] = true;
        $config['allow_all'] = false;
        return self::save($config);
    }

    /**
     * Get all origins
     */
    public static function getOrigins(): array
    {
        $config = self::load();
        return $config['allowed_origins'] ?? [];
    }

    /**
     * Check if origin is allowed
     */
    public static function isOriginAllowed(string $origin, string $endpoint, string $method): bool
    {
        $config = self::load();

        // If CORS is disabled
        if (!$config['enabled']) {
            return false;
        }

        // If allow all is enabled
        if ($config['allow_all']) {
            return true;
        }

        // Check whitelist
        foreach ($config['allowed_origins'] as $allowedOrigin) {
            if ($allowedOrigin['origin'] === $origin || $allowedOrigin['origin'] === '*') {
                // Check endpoint pattern
                if (self::matchEndpoint($endpoint, $allowedOrigin['endpoints'])) {
                    // Check method
                    if (in_array($method, $allowedOrigin['methods']) || in_array('*', $allowedOrigin['methods'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Match endpoint with patterns
     */
    private static function matchEndpoint(string $endpoint, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            // Convert wildcard pattern to regex
            $regex = str_replace(['*', '/'], ['.*', '\/'], $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $endpoint)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get default configuration
     */
    private static function getDefaultConfig(): array
    {
        return [
            'enabled' => true,
            'allow_all' => false,
            'allowed_origins' => [],
            'allowed_headers' => [
                'Content-Type',
                'Authorization',
                'X-Requested-With',
                'Accept',
                'Origin'
            ],
            'exposed_headers' => [],
            'supports_credentials' => true,
            'max_age' => 86400
        ];
    }

    /**
     * Get config value
     */
    public static function get(string $key, $default = null)
    {
        $config = self::load();
        return $config[$key] ?? $default;
    }
}
