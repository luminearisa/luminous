<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\JWT;
use App\Models\User;
use App\Helpers\Hash;

/**
 * AuthController
 * Handle user authentication
 */
class AuthController extends Controller
{
    /**
     * Register new user
     */
    public function register(Request $request, Response $response): void
    {
        $data = $request->all();

        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|string|min:3',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed'
        ]);

        if (!empty($errors)) {
            $response->validationError($errors);
            return;
        }

        // Check if email already exists
        $existingUser = User::firstWhere('email', $data['email']);
        if ($existingUser) {
            $response->error('Email already registered', null, 409);
            return;
        }

        // Create user
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        if (User::create($userData)) {
            $userId = User::lastId();
            
            // Generate token
            $token = JWT::generate([
                'user_id' => $userId,
                'email' => $data['email']
            ]);

            $response->created([
                'token' => $token,
                'user' => [
                    'id' => $userId,
                    'name' => $data['name'],
                    'email' => $data['email']
                ]
            ], 'Registration successful');
        } else {
            $response->serverError('Failed to create user');
        }
    }

    /**
     * Login user
     */
    public function login(Request $request, Response $response): void
    {
        $data = $request->all();

        // Validate input
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            $response->validationError($errors);
            return;
        }

        // Find user
        $user = User::firstWhere('email', $data['email']);

        if (!$user || !Hash::verify($data['password'], $user['password'])) {
            $response->unauthorized('Invalid credentials');
            return;
        }

        // Generate token
        $token = JWT::generate([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        $response->success([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ], 'Login successful');
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request, Response $response): void
    {
        $token = $request->bearerToken();

        if (!$token) {
            $response->unauthorized('Token not provided');
            return;
        }

        $newToken = JWT::refresh($token);

        if (!$newToken) {
            $response->unauthorized('Invalid or expired token');
            return;
        }

        $response->success([
            'token' => $newToken
        ], 'Token refreshed');
    }
}
