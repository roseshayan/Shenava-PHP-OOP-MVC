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
     * @param string $method
     * @param string $route
     * @param array $params
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
        echo json_encode(['status' => 'error', 'message' => 'Route not found']);
    }

    /**
     * Match route pattern
     * @param string $route
     * @param string $url
     * @return bool
     */
    private function matchRoute(string $route, string $url): bool
    {
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = '/^' . $route . '$/i';

        if (preg_match($route, $url, $matches)) {
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

        $controllerClass = 'controllers\\' . $controller . 'Controller';

        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();

            if (method_exists($controllerInstance, $action)) {
                call_user_func_array([$controllerInstance, $action], [$this->params]);
            } else {
                throw new Exception("Method $action not found in controller $controller");
            }
        } else {
            throw new Exception("Controller class $controllerClass not found");
        }
    }

    /**
     * Get current URL
     * @return string
     */
    private function getCurrentUrl(): string
    {
        $url = $_SERVER['REQUEST_URI'];
        $url = str_replace('/backend/public', '', $url);
        $url = parse_url($url, PHP_URL_PATH);
        return $url ?: '/';
    }
}