<?php
/**
 * Security Middleware - Keamanan umum aplikasi
 * 
 * Middleware ini mengatur security headers dan proteksi umum
 */

class SecurityMiddleware {
    
    /**
     * Set security headers
     * 
     * @return void
     */
    public static function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection di browser lama
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy (dasar)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' cdn.jsdelivr.net;");
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy (previously Feature Policy)
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
    
    /**
     * Prevent cache untuk halaman sensitive
     * 
     * @return void
     */
    public static function preventCache() {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    }
    
    /**
     * Sanitize output untuk prevent XSS
     * 
     * @param string $string
     * @return string
     */
    public static function escapeOutput($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate & sanitize URL
     * 
     * @param string $url
     * @return string|false
     */
    public static function sanitizeUrl($url) {
        // Only allow http, https, and relative URLs
        if (preg_match('~^(?:f|ht)tps?://~i', $url) || preg_match('~^/~', $url)) {
            return filter_var($url, FILTER_SANITIZE_URL);
        }
        return false;
    }
    
    /**
     * Validate & sanitize email
     * 
     * @param string $email
     * @return string|false
     */
    public static function sanitizeEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Remove HTML tags dari string
     * 
     * @param string $string
     * @param string $allowed_tags (optional)
     * @return string
     */
    public static function stripHtmlTags($string, $allowed_tags = '') {
        return strip_tags($string, $allowed_tags);
    }
    
    /**
     * Validate file upload security
     * 
     * @param array $file
     * @param array $allowed_types
     * @param int $max_size
     * @return array
     */
    public static function validateFileUpload($file, $allowed_types = [], $max_size = MAX_FILE_SIZE) {
        $response = [
            'valid' => false,
            'message' => ''
        ];
        
        // Check file error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'File upload error';
            return $response;
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            $response['message'] = 'File terlalu besar';
            return $response;
        }
        
        // Check file type
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowed_types) && !in_array($file_ext, $allowed_types)) {
            $response['message'] = 'Tipe file tidak diizinkan';
            return $response;
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel'
        ];
        
        if (isset($allowed_mimes[$file_ext]) && $mime_type !== $allowed_mimes[$file_ext]) {
            $response['message'] = 'File MIME type tidak sesuai';
            return $response;
        }
        
        $response['valid'] = true;
        return $response;
    }
    
    /**
     * Check SQL Injection patterns (deteksi dasar)
     * 
     * @param string $string
     * @return bool
     */
    public static function detectSqlInjection($string) {
        $patterns = [
            '/union\s+select/i',
            '/\bor\b.*=.*\b/i',
            '/\band\b.*=.*\b/i',
            '/;\s*drop/i',
            '/;\s*delete/i',
            '/;\s*insert/i',
            '/;\s*update/i',
            '/\bexec\b/i',
            '/\bscript\b/i',
            '/alert\(/i',
            '/onclick/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log security event
     * 
     * @param string $event
     * @param mixed $data
     * @return void
     */
    public static function logSecurityEvent($event, $data = null) {
        $log_file = STORAGE_DIR . 'logs/security.log';
        
        if (!is_dir(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $user_id = $_SESSION['user_id'] ?? 'GUEST';
        $message = "[$timestamp] [$ip] [$user_id] $event";
        
        if ($data) {
            $message .= ' | ' . json_encode($data);
        }
        
        error_log($message . PHP_EOL, 3, $log_file);
    }
}

?>
