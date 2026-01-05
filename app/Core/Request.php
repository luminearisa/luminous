<?php

namespace App\Core;

/**
 * Request Class
 * Handle HTTP request data
 */
class Request
{
    private array $queryParams;
    private array $bodyParams;
    private array $headers;
    private string $method;
    private string $uri;
    private array $files;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->queryParams = $_GET;
        $this->bodyParams = $this->parseBody();
        $this->headers = $this->parseHeaders();
        $this->uri = $this->parseUri();
        $this->files = $_FILES;
    }

    /**
     * Parse request body
     */
    private function parseBody(): array
    {
        if ($this->method === 'GET') {
            return [];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }

        return $_POST;
    }

    /**
     * Parse request headers
     */
    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    /**
     * Parse request URI
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        return $uri;
    }

    /**
     * Get HTTP method
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get request URI
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get query parameter
     */
    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->queryParams;
        }
        return $this->queryParams[$key] ?? $default;
    }

    /**
     * Get body parameter
     */
    public function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->bodyParams;
        }
        return $this->bodyParams[$key] ?? $default;
    }

    /**
     * Get all request data
     */
    public function all(): array
    {
        return array_merge($this->queryParams, $this->bodyParams);
    }

    /**
     * Get specific inputs only
     */
    public function only(array $keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            if (isset($this->bodyParams[$key])) {
                $data[$key] = $this->bodyParams[$key];
            } elseif (isset($this->queryParams[$key])) {
                $data[$key] = $this->queryParams[$key];
            }
        }
        return $data;
    }

    /**
     * Get header
     */
    public function header(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get all headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get bearer token from Authorization header
     */
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');
        if ($header && strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        return null;
    }

    /**
     * Get uploaded file
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Check if request has file
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Check if key exists in request
     */
    public function has(string $key): bool
    {
        return isset($this->bodyParams[$key]) || isset($this->queryParams[$key]);
    }

    /**
     * Get client IP address
     */
    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return ($this->header('X-Requested-With') === 'XMLHttpRequest');
    }

    /**
     * Check if request is JSON
     */
    public function isJson(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }
}
