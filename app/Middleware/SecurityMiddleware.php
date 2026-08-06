<?php
/**
 * SecurityMiddleware - Middleware untuk security
 */

class SecurityMiddleware {
    
    /**
     * Set security headers
     */
    public static function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
        
        // Strict Transport Security (HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['_token']) || $_POST['_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                die('CSRF Token Mismatch');
            }
        }
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Rate limiting
     */
    public static function checkRateLimit($action, $limit = 10, $window = 60) {
        $key = 'rate_limit_' . $action . '_' . $_SERVER['REMOTE_ADDR'];
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        
        $elapsed = time() - $_SESSION[$key]['time'];
        
        if ($elapsed > $window) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        
        $_SESSION[$key]['count']++;
        
        if ($_SESSION[$key]['count'] > $limit) {
            http_response_code(429);
            die('Too many requests. Please try again later.');
        }
    }
}

?>
