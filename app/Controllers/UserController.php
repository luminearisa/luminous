<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

/**
 * UserController
 * Handle user operations
 */
class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(Request $request, Response $response): void
    {
        $user = $this->user($request);

        if (!$user) {
            $response->unauthorized();
            return;
        }

        // Fetch full user data from database
        $userData = User::find($user['user_id']);

        if (!$userData) {
            $response->notFound('User not found');
            return;
        }

        // Remove password from response
        unset($userData['password']);

        $response->success($userData);
    }

    /**
     * Update user profile
     */
    public function update(Request $request, Response $response): void
    {
        $user = $this->user($request);

        if (!$user) {
            $response->unauthorized();
            return;
        }

        $data = $request->all();

        // Validate input
        $errors = $this->validate($data, [
            'name' => 'string|min:3',
            'email' => 'email'
        ]);

        if (!empty($errors)) {
            $response->validationError($errors);
            return;
        }

        // Check if email is already taken by another user
        if (isset($data['email'])) {
            $existingUser = User::firstWhere('email', $data['email']);
            if ($existingUser && $existingUser['id'] != $user['user_id']) {
                $response->error('Email already taken', null, 409);
                return;
            }
        }

        // Update user
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        if (empty($updateData)) {
            $response->error('No data to update');
            return;
        }

        if (User::update($user['user_id'], $updateData)) {
            $userData = User::find($user['user_id']);
            unset($userData['password']);

            $response->success($userData, 'Profile updated successfully');
        } else {
            $response->serverError('Failed to update profile');
        }
    }

    /**
     * Get all users (admin only example)
     */
    public function index(Request $request, Response $response): void
    {
        $users = User::all();

        // Remove passwords from response
        $users = array_map(function ($user) {
            unset($user['password']);
            return $user;
        }, $users);

        $response->success([
            'users' => $users,
            'total' => count($users)
        ]);
    }

    /**
     * Get user by ID
     */
    public function show(Request $request, Response $response, array $params): void
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            $response->error('User ID is required');
            return;
        }

        $user = User::find($id);

        if (!$user) {
            $response->notFound('User not found');
            return;
        }

        unset($user['password']);

        $response->success($user);
    }
}
