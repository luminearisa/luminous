<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\CorsConfig;

/**
 * CORS Middleware
 * Handle Cross-Origin Resource Sharing with configurable whitelist
 */
class CorsMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        // Load CORS configuration
        $config = CorsConfig::load();

        // If CORS is disabled, allow request without CORS headers
        if (!$config['enabled']) {
            return true;
        }

        // Get request origin
        $origin = $request->header('Origin', '');
        $method = $request->method();
        $endpoint = $request->uri();

        // Handle preflight OPTIONS request
        if ($method === 'OPTIONS') {
            return $this->handlePreflight($config, $origin, $endpoint);
        }

        // Handle actual request
        return $this->handleRequest($config, $origin, $endpoint, $method);
    }

    /**
     * Handle preflight OPTIONS request
     */
    private function handlePreflight(array $config, string $origin, string $endpoint): bool
    {
        if ($config['allow_all']) {
            $this->setAllowAllHeaders($config);
        } else {
            // Check if origin is in whitelist
            $allowed = $this->checkOriginInWhitelist($config, $origin, $endpoint);
            
            if (!$allowed) {
                http_response_code(403);
                exit;
            }

            $this->setWhitelistHeaders($config, $origin, $endpoint);
        }

        // Send 204 No Content for preflight
        http_response_code(204);
        exit;
    }

    /**
     * Handle actual request
     */
    private function handleRequest(array $config, string $origin, string $endpoint, string $method): bool
    {
        if ($config['allow_all']) {
            $this->setAllowAllHeaders($config, false);
            return true;
        }

        // Check if origin is allowed
        $allowed = CorsConfig::isOriginAllowed($origin, $endpoint, $method);

        if (!$allowed && $origin !== '') {
            // Origin not allowed, but don't block the request
            // Just don't send CORS headers
            return true;
        }

        if ($allowed) {
            $this->setWhitelistHeaders($config, $origin, $endpoint, false);
        }

        return true;
    }

    /**
     * Set headers for allow all mode
     */
    private function setAllowAllHeaders(array $config, bool $isPreflight = true): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        
        $headers = implode(', ', $config['allowed_headers']);
        header("Access-Control-Allow-Headers: $headers");

        if (!empty($config['exposed_headers'])) {
            $exposedHeaders = implode(', ', $config['exposed_headers']);
            header("Access-Control-Expose-Headers: $exposedHeaders");
        }

        if ($isPreflight) {
            header('Access-Control-Max-Age: ' . $config['max_age']);
        }
    }

    /**
     * Set headers for whitelist mode
     */
    private function setWhitelistHeaders(array $config, string $origin, string $endpoint, bool $isPreflight = true): void
    {
        // Find allowed methods for this origin and endpoint
        $allowedMethods = $this->getAllowedMethods($config, $origin, $endpoint);

        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
        
        $headers = implode(', ', $config['allowed_headers']);
        header("Access-Control-Allow-Headers: $headers");

        if ($config['supports_credentials']) {
            header('Access-Control-Allow-Credentials: true');
        }

        if (!empty($config['exposed_headers'])) {
            $exposedHeaders = implode(', ', $config['exposed_headers']);
            header("Access-Control-Expose-Headers: $exposedHeaders");
        }

        if ($isPreflight) {
            header('Access-Control-Max-Age: ' . $config['max_age']);
        }
    }

    /**
     * Check if origin is in whitelist
     */
    private function checkOriginInWhitelist(array $config, string $origin, string $endpoint): bool
    {
        foreach ($config['allowed_origins'] as $allowedOrigin) {
            if ($allowedOrigin['origin'] === $origin || $allowedOrigin['origin'] === '*') {
                // Check if endpoint matches
                if ($this->matchEndpoint($endpoint, $allowedOrigin['endpoints'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get allowed methods for origin and endpoint
     */
    private function getAllowedMethods(array $config, string $origin, string $endpoint): array
    {
        $methods = [];

        foreach ($config['allowed_origins'] as $allowedOrigin) {
            if ($allowedOrigin['origin'] === $origin || $allowedOrigin['origin'] === '*') {
                if ($this->matchEndpoint($endpoint, $allowedOrigin['endpoints'])) {
                    $methods = array_merge($methods, $allowedOrigin['methods']);
                }
            }
        }

        // Add OPTIONS for preflight
        if (!in_array('OPTIONS', $methods)) {
            $methods[] = 'OPTIONS';
        }

        return array_unique($methods);
    }

    /**
     * Match endpoint with patterns
     */
    private function matchEndpoint(string $endpoint, array $patterns): bool
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
}

