<?php
/**
 * Helper - Fungsi-fungsi helper umum untuk KasirKu
 */

/**
 * Get base URL aplikasi
 */
if (!function_exists('base_url')) {
    function base_url($path = '') {
        return BASE_URL . $path;
    }
}

/**
 * Format currency ke Rupiah
 */
if (!function_exists('format_currency')) {
    function format_currency($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

/**
 * Format tanggal
 */
if (!function_exists('format_date')) {
    function format_date($date, $format = 'd/m/Y') {
        return date($format, strtotime($date));
    }
}

/**
 * Format waktu
 */
if (!function_exists('format_time')) {
    function format_time($date, $format = 'H:i') {
        return date($format, strtotime($date));
    }
}

/**
 * Redirect ke halaman lain
 */
if (!function_exists('redirect')) {
    function redirect($path) {
        header('Location: ' . BASE_URL . $path);
        exit();
    }
}

/**
 * Set flash message
 */
if (!function_exists('set_flash')) {
    function set_flash($message, $type = 'info') {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }
}

/**
 * Get flash message
 */
if (!function_exists('get_flash')) {
    function get_flash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

/**
 * Check if user is authenticated
 */
if (!function_exists('is_authenticated')) {
    function is_authenticated() {
        return isset($_SESSION['user']);
    }
}

/**
 * Get current user
 */
if (!function_exists('current_user')) {
    function current_user() {
        return $_SESSION['user'] ?? null;
    }
}

/**
 * Check user role
 */
if (!function_exists('has_role')) {
    function has_role($role) {
        $user = current_user();
        if (!$user) return false;
        return $user['role'] === $role;
    }
}

/**
 * Sanitize input
 */
if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Validate email
 */
if (!function_exists('is_valid_email')) {
    function is_valid_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Validate phone number
 */
if (!function_exists('is_valid_phone')) {
    function is_valid_phone($phone) {
        return preg_match('/^(\+62|62|0)[0-9]{9,12}$/', preg_replace('/[^0-9+]/', '', $phone));
    }
}

/**
 * Generate random string
 */
if (!function_exists('generate_random')) {
    function generate_random($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $result;
    }
}

/**
 * Generate SKU
 */
if (!function_exists('generate_sku')) {
    function generate_sku($prefix = 'PRD') {
        return strtoupper($prefix . '-' . date('YmdHis') . '-' . generate_random(4));
    }
}

/**
 * Log activity
 */
if (!function_exists('log_activity')) {
    function log_activity($action, $details = '') {
        $log_file = LOG_DIR . 'activity-' . date('Y-m-d') . '.log';
        $user = current_user();
        $message = date('H:i:s') . ' | ' . ($user['id'] ?? 'System') . ' | ' . $action . ' | ' . $details . PHP_EOL;
        file_put_contents($log_file, $message, FILE_APPEND);
    }
}

?>
