<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\JWT;

/**
 * Auth Middleware
 * Verify JWT token and authenticate requests
 */
class AuthMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        $token = $request->bearerToken();

        if (!$token) {
            $response->unauthorized('Token not provided');
            return false;
        }

        $payload = JWT::verify($token);

        if ($payload === null) {
            $response->unauthorized('Invalid or expired token');
            return false;
        }

        // Attach user data to request
        $request->user = $payload;

        return true;
    }
}
