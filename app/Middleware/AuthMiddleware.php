<?php
/**
 * AuthMiddleware - Middleware untuk authentication
 */

class AuthMiddleware {
    
    /**
     * Check if user is authenticated
     */
    public static function checkAuth() {
        if (!isset($_SESSION['user'])) {
            redirect('auth/login');
        }
    }
    
    /**
     * Check session timeout
     */
    public static function checkSessionTimeout() {
        $timeout = SESSION_TIMEOUT * 60; // Convert to seconds
        
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            
            if ($elapsed > $timeout) {
                // Session expired
                session_destroy();
                redirect('auth/login?expired=1');
            }
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Prevent authenticated users from accessing login page
     */
    public static function checkGuest() {
        if (isset($_SESSION['user'])) {
            redirect('dashboard');
        }
    }
}

?>
