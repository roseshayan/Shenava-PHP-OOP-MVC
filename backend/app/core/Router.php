<?php
/**
 * Shenava - Router Class
 * Handles routing and request dispatching
 */

class Router
{
    private array $routes = [];
    private array $params = [];

    /**
     * Add route
     */
    public function addRoute(string $method, string $route, array $params = []): void
    {
        $this->routes[strtoupper($method)][$route] = $params;
    }

    /**
     * Dispatch request
     * @throws Exception
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $this->getCurrentUrl();

        // Debug output
        if (isset($_GET['debug'])) {
            echo "Method: $method<br>";
            echo "URL: $url<br>";
            echo "Available routes:<br>";
            foreach ($this->routes[$method] ?? [] as $route => $params) {
                echo "- $route => " . json_encode($params) . "<br>";
            }
            exit;
        }

        // Find matching route
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $params) {
                if ($this->matchRoute($route, $url)) {
                    $this->params = $params;
                    $this->executeController();
                    return;
                }
            }
        }

        // No route found
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Route not found',
            'requested_url' => $url,
            'method' => $method
        ]);
    }

    /**
     * Match route pattern
     */
    private function matchRoute(string $route, string $url): bool
    {
        // Clean URL - remove base path if exists
        $basePath = '/shenava/backend/public';
        if (str_starts_with($url, $basePath)) {
            $url = substr($url, strlen($basePath));
        }

        // Ensure URL starts with /
        if (empty($url) || $url[0] !== '/') {
            $url = '/' . $url;
        }

        // Convert route pattern to regex
        $pattern = preg_replace('/\//', '\\/', $route);
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<\1>[a-zA-Z0-9\-_]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';

        if (preg_match($pattern, $url, $matches)) {
            foreach ($matches as $key => $match) {
                if (is_string($key)) {
                    $this->params[$key] = $match;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Execute controller method
     * @throws Exception
     */
    private function executeController(): void
    {
        $controller = $this->params['controller'] ?? 'Home';
        $action = $this->params['action'] ?? 'index';

        // Remove 'controller' from class name if present
        $controller = str_replace('Controller', '', $controller);
        $controllerClass = $controller . 'Controller';

        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();

            if (method_exists($controllerInstance, $action)) {
                call_user_func_array([$controllerInstance, $action], [$this->params]);
            } else {
                throw new Exception("Method $action not found in controller $controllerClass");
            }
        } else {
            throw new Exception("Controller class $controllerClass not found");
        }
    }

    /**
     * Get current URL
     */
    private function getCurrentUrl(): string
    {
        $url = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        if (($pos = strpos($url, '?')) !== false) {
            $url = substr($url, 0, $pos);
        }

        return $url;
    }
}