<?php

use JetBrains\PhpStorm\NoReturn;

/**
 * Shenava - Home Controller
 * Handles root endpoint and API info
 */

class HomeController extends Controller
{
    /**
     * API information
     */
    #[NoReturn]
    public function index($params = []): void
    {
        $data = [
            'app' => 'Shenava Audio Books',
            'version' => '1.0.0',
            'description' => 'REST API for Shenava Audio Books Application',
            'endpoints' => [
                'auth' => [
                    'POST /api/v1/auth/register' => 'Register new user',
                    'POST /api/v1/auth/login' => 'Login user'
                ],
                'books' => [
                    'GET /api/v1/books' => 'Get all books',
                    'GET /api/v1/books/featured' => 'Get featured books',
                    'GET /api/v1/books/{uuid}' => 'Get book by UUID',
                    'GET /api/v1/books/category/{slug}' => 'Get books by category'
                ],
                'categories' => [
                    'GET /api/v1/categories' => 'Get all categories'
                ]
            ],
            'timestamp' => time()
        ];

        $this->success($data, 'Shenava API is running');
    }

    /**
     * Health check
     */
    #[NoReturn]
    public function health($params = []): void
    {
        $this->success([
            'status' => 'healthy',
            'timestamp' => time(),
            'database' => 'connected'
        ], 'API is healthy');
    }

    /**
     * Debug route
     * @throws ReflectionException
     */
    #[NoReturn]
    public function debug($params = []): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_SERVER['REQUEST_URI'] ?? '/';

        $routes = [];
        foreach ($this->getRoutes() as $routeMethod => $routeList) {
            foreach ($routeList as $route => $params) {
                $routes[] = [
                    'method' => $routeMethod,
                    'route' => $route,
                    'controller' => $params['controller'] ?? 'N/A',
                    'action' => $params['action'] ?? 'N/A'
                ];
            }
        }

        $data = [
            'debug_info' => [
                'method' => $method,
                'url' => $url,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'php_version' => PHP_VERSION,
                'app_path' => APP_PATH,
                'root_path' => ROOT_PATH
            ],
            'loaded_classes' => get_declared_classes(),
            'registered_routes' => $routes
        ];

        $this->success($data, 'Debug information');
    }

    /**
     * Get all registered routes (for debug)
     * @throws ReflectionException
     */
    private function getRoutes(): array
    {
        global $router;

        // Use reflection to access private routes property
        $reflection = new ReflectionClass($router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);

        return $routesProperty->getValue($router);
    }
}