<?php
/**
 * Router Class
 * Handles URL routing and dispatches requests to appropriate controllers
 */

class Router {
    private static $instance = null;
    private $routes = [];
    private $middlewares = [];
    private $basePath = '';

    /**
     * Private constructor for singleton
     */
    private function __construct() {
        $this->basePath = $this->getBasePath();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get base path for routing
     */
    private function getBasePath() {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestUri = $_SERVER['REQUEST_URI'];

        if (strpos($requestUri, $scriptName) === 0) {
            return $scriptName;
        } elseif (strpos($requestUri, dirname($scriptName)) === 0) {
            return dirname($scriptName);
        }

        return '';
    }

    /**
     * Add a route
     */
    public function add($method, $route, $handler, $middleware = []) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $this->normalizeRoute($route),
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Add GET route
     */
    public function get($route, $handler, $middleware = []) {
        $this->add('GET', $route, $handler, $middleware);
    }

    /**
     * Add POST route
     */
    public function post($route, $handler, $middleware = []) {
        $this->add('POST', $route, $handler, $middleware);
    }

    /**
     * Add PUT route
     */
    public function put($route, $handler, $middleware = []) {
        $this->add('PUT', $route, $handler, $middleware);
    }

    /**
     * Add DELETE route
     */
    public function delete($route, $handler, $middleware = []) {
        $this->add('DELETE', $route, $handler, $middleware);
    }

    /**
     * Add resource routes (RESTful)
     */
    public function resource($resource, $controller, $middleware = []) {
        $this->get("/{$resource}", "{$controller}@index", $middleware);
        $this->get("/{$resource}/create", "{$controller}@create", $middleware);
        $this->post("/{$resource}", "{$controller}@store", $middleware);
        $this->get("/{$resource}/{id}", "{$controller}@show", $middleware);
        $this->get("/{$resource}/{id}/edit", "{$controller}@edit", $middleware);
        $this->put("/{$resource}/{id}", "{$controller}@update", $middleware);
        $this->delete("/{$resource}/{id}", "{$controller}@destroy", $middleware);
    }

    /**
     * Add middleware globally
     */
    public function middleware($middleware) {
        $this->middlewares = array_merge($this->middlewares, (array)$middleware);
    }

    /**
     * Normalize route pattern
     */
    private function normalizeRoute($route) {
        return '/' . trim($route, '/');
    }

    /**
     * Get current request URI
     */
    private function getRequestUri() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if present
        if (strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        return $this->normalizeRoute($uri);
    }

    /**
     * Get current request method
     */
    private function getRequestMethod() {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Match route against current request
     */
    private function matchRoute($routePattern, $requestUri) {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePattern);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';

        if (preg_match($pattern, $requestUri, $matches)) {
            array_shift($matches); // Remove full match
            return $matches;
        }

        return false;
    }

    /**
     * Dispatch the request
     */
    public function dispatch() {
        $method = $this->getRequestMethod();
        $uri = $this->getRequestUri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method || $route['method'] === 'ANY') {
                $params = $this->matchRoute($route['route'], $uri);

                if ($params !== false) {
                    // Execute middleware
                    $this->executeMiddleware(array_merge($this->middlewares, $route['middleware']));

                    // Execute handler
                    return $this->executeHandler($route['handler'], $params);
                }
            }
        }

        // No route matched
        $this->handle404();
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware($middlewares) {
        foreach ($middlewares as $middleware) {
            if (is_callable($middleware)) {
                $middleware();
            } elseif (is_string($middleware)) {
                $this->executeMiddlewareClass($middleware);
            } elseif (is_object($middleware) && method_exists($middleware, 'handle')) {
                $middleware->handle();
            }
        }
    }

    /**
     * Execute middleware class
     */
    private function executeMiddlewareClass($middlewareClass) {
        if (class_exists($middlewareClass)) {
            $middleware = new $middlewareClass();
            if (method_exists($middleware, 'handle')) {
                $middleware->handle();
            }
        }
    }

    /**
     * Execute route handler
     */
    private function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            return $this->executeControllerAction($handler, $params);
        }
    }

    /**
     * Execute controller action
     */
    private function executeControllerAction($handler, $params = []) {
        list($controller, $action) = explode('@', $handler);

        $controllerClass = $controller . 'Controller';

        if (!class_exists($controllerClass)) {
            // Try with namespace
            $controllerClass = "Controllers\\{$controllerClass}";
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller {$controllerClass} not found");
            }
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $action)) {
            throw new Exception("Action {$action} not found in controller {$controllerClass}");
        }

        return call_user_func_array([$controllerInstance, $action], $params);
    }

    /**
     * Handle 404 errors
     */
    private function handle404() {
        header("HTTP/1.0 404 Not Found");
        echo "404 - Page not found";
        exit;
    }

    /**
     * Redirect to a URL
     */
    public function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    /**
     * Get current route parameters
     */
    public function getParams() {
        // This would need to be implemented to store matched params
        return [];
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {}
}