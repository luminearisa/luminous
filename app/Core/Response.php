<?php

namespace App\Core;

/**
 * Response Class
 * Handle HTTP responses with JSON format
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private array $data = [];

    /**
     * Set HTTP status code
     */
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set response header
     */
    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set response data
     */
    public function data(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Send JSON response
     */
    public function json(array $data = null, int $statusCode = null): void
    {
        if ($statusCode !== null) {
            $this->statusCode = $statusCode;
        }

        if ($data !== null) {
            $this->data = $data;
        }

        // Set status code
        http_response_code($this->statusCode);

        // Set default JSON header
        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = 'application/json';
        }

        // Send headers
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        // Send JSON response
        echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Success response
     */
    public function success($data = null, string $message = 'Success', int $statusCode = 200): void
    {
        $this->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Error response
     */
    public function error(string $message = 'Error', $errors = null, int $statusCode = 400): void
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $this->json($response, $statusCode);
    }

    /**
     * Created response (201)
     */
    public function created($data = null, string $message = 'Resource created'): void
    {
        $this->success($data, $message, 201);
    }

    /**
     * No content response (204)
     */
    public function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    /**
     * Not found response (404)
     */
    public function notFound(string $message = 'Resource not found'): void
    {
        $this->error($message, null, 404);
    }

    /**
     * Unauthorized response (401)
     */
    public function unauthorized(string $message = 'Unauthorized'): void
    {
        $this->error($message, null, 401);
    }

    /**
     * Forbidden response (403)
     */
    public function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, null, 403);
    }

    /**
     * Validation error response (422)
     */
    public function validationError(array $errors, string $message = 'Validation failed'): void
    {
        $this->error($message, $errors, 422);
    }

    /**
     * Server error response (500)
     */
    public function serverError(string $message = 'Internal server error'): void
    {
        $this->error($message, null, 500);
    }

    /**
     * Redirect response
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }
}
