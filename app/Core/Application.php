<?php
/**
 * Application - Class utama aplikasi KasirKu
 */

class Application {
    
    private $router;
    private $db;
    
    public function __construct() {
        $this->setupSession();
        $this->setupSecurity();
        $this->router = new Router();
        $this->registerRoutes();
    }
    
    /**
     * Setup session
     */
    private function setupSession() {
        session_name(SESSION_NAME);
        session_start();
    }
    
    /**
     * Setup security headers
     */
    private function setupSecurity() {
        SecurityMiddleware::setSecurityHeaders();
    }
    
    /**
     * Register semua routes aplikasi
     */
    private function registerRoutes() {
        // ===== AUTH ROUTES =====
        $this->router->get('auth/login', 'Auth@loginForm');
        $this->router->post('auth/login', 'Auth@login');
        $this->router->get('auth/logout', 'Auth@logout');
        $this->router->get('auth/profile', 'Auth@profile');
        $this->router->post('auth/update-profile', 'Auth@updateProfile');
        $this->router->post('auth/change-password', 'Auth@changePassword');
        
        // ===== DASHBOARD ROUTES =====
        $this->router->get('dashboard', 'Dashboard@index');
        $this->router->get('dashboard/data', 'Dashboard@getData');
        
        // ===== PRODUK ROUTES =====
        $this->router->get('produk', 'Produk@index');
        $this->router->get('produk/create', 'Produk@create');
        $this->router->post('produk/store', 'Produk@store');
        $this->router->get('produk/{id:number}/edit', 'Produk@edit');
        $this->router->post('produk/{id:number}/update', 'Produk@update');
        $this->router->post('produk/{id:number}/delete', 'Produk@delete');
        $this->router->get('produk/search', 'Produk@search');
        
        // ===== HOME/INDEX =====
        $this->router->get('', 'Dashboard@index');
        $this->router->get('/', 'Dashboard@index');
        $this->router->get('index', 'Dashboard@index');
    }
    
    /**
     * Run aplikasi
     */
    public function run() {
        // Check session timeout
        AuthMiddleware::checkSessionTimeout();
        
        // Dispatch route
        $this->router->dispatch();
    }
}

?>
