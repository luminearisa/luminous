<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

/**
 * CORS Middleware
 * Handle Cross-Origin Resource Sharing
 */
class CorsMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        // Handle preflight request
        if ($request->method() === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        return true;
    }
}
