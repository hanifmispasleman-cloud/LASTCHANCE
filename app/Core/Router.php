<?php
/**
 * Router - Sistem routing sederhana untuk KasirKu
 * 
 * Mengatur mapping URL ke Controller dan Action
 */

class Router {
    
    private $routes = [];
    private $controller;
    private $action;
    private $params = [];
    
    /**
     * Register GET route
     */
    public function get($path, $callback) {
        $this->registerRoute('GET', $path, $callback);
    }
    
    /**
     * Register POST route
     */
    public function post($path, $callback) {
        $this->registerRoute('POST', $path, $callback);
    }
    
    /**
     * Register PUT route
     */
    public function put($path, $callback) {
        $this->registerRoute('PUT', $path, $callback);
    }
    
    /**
     * Register DELETE route
     */
    public function delete($path, $callback) {
        $this->registerRoute('DELETE', $path, $callback);
    }
    
    /**
     * Register route untuk semua method
     */
    public function any($path, $callback) {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            $this->registerRoute($method, $path, $callback);
        }
    }
    
    /**
     * Register route
     */
    private function registerRoute($method, $path, $callback) {
        $key = $method . ':' . $path;
        $this->routes[$key] = $callback;
    }
    
    /**
     * Parse URL dan match dengan route
     */
    public function dispatch() {
        $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $request_method = $_SERVER['REQUEST_METHOD'];
        
        // Remove base URL
        $base_path = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        $path = substr($request_uri, strlen($base_path));
        $path = trim($path, '/');
        
        // If empty, set to home
        if (empty($path)) {
            $path = 'dashboard';
        }
        
        // Try to match route
        $matched = false;
        foreach ($this->routes as $route_key => $callback) {
            list($method, $route_path) = explode(':', $route_key, 2);
            
            if ($method !== $request_method && $method !== 'ANY') {
                continue;
            }
            
            $route_pattern = $this->convertRouteToRegex($route_path);
            
            if (preg_match($route_pattern, $path, $matches)) {
                // Extract parameters
                array_shift($matches); // Remove full match
                $this->params = $matches;
                
                // Call callback
                $this->executeCallback($callback);
                $matched = true;
                break;
            }
        }
        
        if (!$matched) {
            $this->notFound();
        }
    }
    
    /**
     * Convert route path to regex
     * e.g., produk/{id} -> /^produk\/(\d+)$/
     */
    private function convertRouteToRegex($route) {
        // Escape special regex chars
        $route = preg_quote($route, '#');
        
        // Replace parameter placeholders
        $route = preg_replace('#\\{(\w+)\\}#', '([^/]+)', $route);
        $route = preg_replace('#\\{(\w+):number\\}#', '(\\d+)', $route);
        
        return '#^' . $route . '$#';
    }
    
    /**
     * Execute route callback
     */
    private function executeCallback($callback) {
        if (is_string($callback)) {
            // Format: ControllerName@method
            list($controller_name, $action) = explode('@', $callback);
            
            $controller_class = $controller_name . 'Controller';
            $controller_file = CONTROLLER_DIR . $controller_name . 'Controller.php';
            
            if (!file_exists($controller_file)) {
                $this->notFound();
                return;
            }
            
            require_once $controller_file;
            
            if (!class_exists($controller_class)) {
                $this->notFound();
                return;
            }
            
            $db_config = new Database();
            $database = $db_config->connect();
            
            $controller = new $controller_class($database);
            
            if (!method_exists($controller, $action)) {
                $this->notFound();
                return;
            }
            
            // Call method dengan params
            call_user_func_array([$controller, $action], $this->params);
        } elseif (is_callable($callback)) {
            // Direct callback function
            call_user_func_array($callback, $this->params);
        }
    }
    
    /**
     * Handle 404 Not Found
     */
    private function notFound() {
        http_response_code(404);
        echo '<h1>404 - Halaman Tidak Ditemukan</h1>';
        echo '<p>Halaman yang Anda cari tidak tersedia.</p>';
        exit();
    }
}

?>
