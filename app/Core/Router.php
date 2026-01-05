<?php

namespace App\Core;

/**
 * Router Class
 * Handle routing for the application
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private ?string $prefix = null;

    /**
     * Add route
     */
    private function addRoute(string $method, string $path, $handler, array $middlewares = []): void
    {
        if ($this->prefix) {
            $path = $this->prefix . $path;
        }

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middlewares' => array_merge($this->middlewares, $middlewares),
            'pattern' => $this->convertToRegex($path)
        ];
    }

    /**
     * Convert route path to regex pattern
     */
    private function convertToRegex(string $path): string
    {
        // Replace {param} with named capture group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * GET route
     */
    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * POST route
     */
    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * PUT route
     */
    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    /**
     * PATCH route
     */
    public function patch(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middlewares);
    }

    /**
     * DELETE route
     */
    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    /**
     * Group routes with prefix and middlewares
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->middlewares;

        if (isset($attributes['prefix'])) {
            $this->prefix = ($this->prefix ?? '') . $attributes['prefix'];
        }

        if (isset($attributes['middleware'])) {
            $middlewares = is_array($attributes['middleware']) 
                ? $attributes['middleware'] 
                : [$attributes['middleware']];
            $this->middlewares = array_merge($this->middlewares, $middlewares);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->middlewares = $previousMiddlewares;
    }

    /**
     * Dispatch request to matching route
     */
    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract route parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Execute middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = is_string($middleware) ? new $middleware() : $middleware;
                    $result = $middlewareInstance->handle($request, $response);
                    
                    // If middleware returns false, stop execution
                    if ($result === false) {
                        return;
                    }
                }

                // Execute handler
                $handler = $route['handler'];

                if (is_callable($handler)) {
                    // Closure handler
                    call_user_func_array($handler, [$request, $response, $params]);
                } elseif (is_string($handler)) {
                    // Controller@method format
                    $this->callControllerAction($handler, $request, $response, $params);
                } elseif (is_array($handler) && count($handler) === 2) {
                    // [Controller::class, 'method'] format
                    $controller = new $handler[0]();
                    $method = $handler[1];
                    $controller->$method($request, $response, $params);
                }

                return;
            }
        }

        // No route matched
        $response->notFound('Route not found');
    }

    /**
     * Call controller action
     */
    private function callControllerAction(string $handler, Request $request, Response $response, array $params): void
    {
        [$controller, $method] = explode('@', $handler);
        
        // Add namespace if not present
        if (strpos($controller, '\\') === false) {
            $controller = "App\\Controllers\\$controller";
        }

        if (!class_exists($controller)) {
            $response->serverError("Controller $controller not found");
            return;
        }

        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $method)) {
            $response->serverError("Method $method not found in controller");
            return;
        }

        $controllerInstance->$method($request, $response, $params);
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
