<?php
namespace App\Core;

class Router {
    private $routes = [];
    private $middlewares = [];
    private $basePath = '';

    public function __construct($basePath = '') { $this->basePath = $basePath; }
    public function get($path, $handler, $middleware = []) { $this->addRoute('GET', $path, $handler, $middleware); }
    public function post($path, $handler, $middleware = []) { $this->addRoute('POST', $path, $handler, $middleware); }
    public function put($path, $handler, $middleware = []) { $this->addRoute('PUT', $path, $handler, $middleware); }
    public function delete($path, $handler, $middleware = []) { $this->addRoute('DELETE', $path, $handler, $middleware); }

    private function addRoute($method, $path, $handler, $middleware) {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->basePath . $path,
            'handler' => $handler,
            'middleware' => (array)$middleware
        ];
    }

    public function addMiddleware($name, $callback) {
        $this->middlewares[$name] = $callback;
    }

    public function dispatch($requestUri, $requestMethod) {
        $requestUri = parse_url($requestUri, PHP_URL_PATH);
        $requestUri = rtrim($requestUri, '/');
        if (empty($requestUri)) $requestUri = '/';

        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            $pattern = $this->convertRouteToRegex($route['path']);
            if (preg_match($pattern, $requestUri, $matches) && $route['method'] === $requestMethod) {
                array_shift($matches);
                foreach ($route['middleware'] as $mw) {
                    if (isset($this->middlewares[$mw])) {
                        $result = call_user_func($this->middlewares[$mw]);
                        if ($result === false) return;
                    }
                }
                if (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $matches);
                } elseif (is_string($route['handler']) && strpos($route['handler'], '@') !== false) {
                    list($controller, $method) = explode('@', $route['handler']);
                    $controllerClass = "App\\Controllers\\{$controller}";
                    if (class_exists($controllerClass)) {
                        $instance = new $controllerClass();
                        call_user_func_array([$instance, $method], $matches);
                    }
                }
                return;
            }
        }
        http_response_code(404);
        echo '404 - Halaman tidak ditemukan';
    }

    private function convertRouteToRegex($path) {
        $path = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $path . '$#';
    }
}