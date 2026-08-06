<?php
/**
 * Authentication Middleware - Validasi Login User
 * 
 * Middleware ini mengecek apakah user sudah login dan role apa yang dimiliki
 */

class AuthMiddleware {
    
    /**
     * Check apakah user sudah login
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role']);
    }
    
    /**
     * Require login, redirect ke login jika belum
     * 
     * @return void
     */
    public static function requireLogin() {
        if (!self::isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            redirect('auth/login');
        }
    }
    
    /**
     * Require specific role
     * 
     * @param string $role
     * @return void
     */
    public static function requireRole($role) {
        self::requireLogin();
        
        if ($_SESSION['role'] !== $role) {
            http_response_code(403);
            die('Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }
    
    /**
     * Require salah satu dari beberapa role
     * 
     * @param array $roles
     * @return void
     */
    public static function requireAnyRole($roles) {
        self::requireLogin();
        
        if (!in_array($_SESSION['role'], $roles)) {
            http_response_code(403);
            die('Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }
    
    /**
     * Check session timeout
     * 
     * @return void
     */
    public static function checkSessionTimeout() {
        if (!self::isAuthenticated()) {
            return;
        }
        
        $timeout = SESSION_TIMEOUT;
        $session_timeout = $_SESSION['last_activity'] ?? time();
        
        if (time() - $session_timeout > $timeout) {
            // Session expired
            session_destroy();
            $_SESSION = [];
            redirect('auth/login?expired=1');
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Prevent authenticated users dari mengakses login/register
     * 
     * @return void
     */
    public static function requireGuest() {
        if (self::isAuthenticated()) {
            redirect('dashboard');
        }
    }
    
    /**
     * Get current user info
     * 
     * @return array|null
     */
    public static function getCurrentUser() {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'nama_lengkap' => $_SESSION['nama_lengkap'],
            'role' => $_SESSION['role'],
            'email' => $_SESSION['email'] ?? null
        ];
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     * 
     * @return string|null
     */
    public static function getCurrentUserRole() {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Check user has role
     * 
     * @param string $role
     * @return bool
     */
    public static function hasRole($role) {
        return self::isAuthenticated() && $_SESSION['role'] === $role;
    }
    
    /**
     * Check user has any role
     * 
     * @param array $roles
     * @return bool
     */
    public static function hasAnyRole($roles) {
        return self::isAuthenticated() && in_array($_SESSION['role'], $roles);
    }
    
    /**
     * Check user is admin
     * 
     * @return bool
     */
    public static function isAdmin() {
        return self::hasRole(ROLE_ADMIN);
    }
    
    /**
     * Check user is owner
     * 
     * @return bool
     */
    public static function isOwner() {
        return self::hasRole(ROLE_OWNER);
    }
    
    /**
     * Check user is kasir
     * 
     * @return bool
     */
    public static function isKasir() {
        return self::hasRole(ROLE_KASIR);
    }
}

?>
