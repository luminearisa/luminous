<?php

/**
 * API Routes
 * Define all API routes here
 */

use App\Middlewares\CorsMiddleware;
use App\Middlewares\AuthMiddleware;

// Apply CORS middleware globally
$router->group(['middleware' => CorsMiddleware::class], function ($router) {
    
    // Public routes
    $router->get('/', function ($request, $response) {
        $response->success([
            'framework' => 'Luminous',
            'version' => '1.0.0',
            'message' => 'Welcome to Luminous Framework'
        ]);
    });

    $router->get('/health', function ($request, $response) {
        $response->success([
            'status' => 'healthy',
            'timestamp' => time()
        ]);
    });

    // Auth routes
    $router->post('/auth/register', 'AuthController@register');
    $router->post('/auth/login', 'AuthController@login');

    // Protected routes
    $router->group(['prefix' => '/api', 'middleware' => AuthMiddleware::class], function ($router) {
        
        // User routes
        $router->get('/user', 'UserController@profile');
        $router->put('/user', 'UserController@update');
        
        // Resource routes example
        $router->get('/posts', 'PostController@index');
        $router->post('/posts', 'PostController@store');
        $router->get('/posts/{id}', 'PostController@show');
        $router->put('/posts/{id}', 'PostController@update');
        $router->delete('/posts/{id}', 'PostController@destroy');
    });
});
