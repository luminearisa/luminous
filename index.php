<?php

/**
 * Luminous Framework
 * Entry Point for RESTful API
 */

// Load Composer autoloader
require __DIR__ . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\Env;

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');

set_exception_handler(function ($e) {
    $response = new Response();
    
    $debug = Env::get('APP_DEBUG', 'false') === 'true';
    
    if ($debug) {
        $response->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    } else {
        $response->serverError('An error occurred');
    }
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Initialize environment
try {
    Env::load(__DIR__ . '/.env');
    Env::loadConfig(__DIR__ . '/config/config.lumi');
} catch (Exception $e) {
    $response = new Response();
    $response->serverError('Configuration error: ' . $e->getMessage());
    exit;
}

// Create request and response instances
$request = new Request();
$response = new Response();

// Create router instance
$router = new Router();

// Load routes
require __DIR__ . '/routes/api.php';

// Dispatch request
$router->dispatch($request, $response);
