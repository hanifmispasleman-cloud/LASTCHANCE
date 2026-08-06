<?php
/**
 * CSRF Protection Middleware - Proteksi terhadap Cross-Site Request Forgery
 * 
 * Middleware ini menghasilkan dan memvalidasi CSRF token
 */

class CsrfMiddleware {
    
    /**
     * Generate CSRF Token
     * 
     * @return string
     */
    public static function generateToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Get CSRF Token
     * 
     * @return string
     */
    public static function getToken() {
        return $_SESSION[CSRF_TOKEN_NAME] ?? '';
    }
    
    /**
     * Verify CSRF Token dari request
     * 
     * @param string $token (optional, jika tidak ada akan diambil dari POST)
     * @return bool
     */
    public static function verifyToken($token = null) {
        // Ambil token dari parameter jika tidak ada
        if ($token === null) {
            $token = $_POST[CSRF_TOKEN_NAME] ?? $_GET[CSRF_TOKEN_NAME] ?? '';
        }
        
        $session_token = $_SESSION[CSRF_TOKEN_NAME] ?? '';
        
        // Gunakan hash_equals untuk mencegah timing attack
        return !empty($token) && !empty($session_token) && hash_equals($session_token, $token);
    }
    
    /**
     * Validate request method dan CSRF token untuk unsafe methods
     * 
     * @param string $token (optional)
     * @return bool
     */
    public static function validateRequest($token = null) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Unsafe methods yang memerlukan CSRF validation
        $unsafe_methods = ['POST', 'PUT', 'DELETE', 'PATCH'];
        
        if (!in_array($method, $unsafe_methods)) {
            return true; // Safe method, tidak perlu validasi
        }
        
        return self::verifyToken($token);
    }
    
    /**
     * Get CSRF token HTML input
     * 
     * @return string
     */
    public static function getTokenInput() {
        $token = self::generateToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Require CSRF token valid atau redirect
     * 
     * @param string $token (optional)
     * @return void
     */
    public static function requireValidToken($token = null) {
        if (!self::validateRequest($token)) {
            http_response_code(403);
            die('CSRF Token tidak valid. Silakan coba lagi.');
        }
    }
}

?>
