<?php
namespace App\Core;

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Lax');
            session_start();
        }
    }

    public static function set($key, $value) { $_SESSION[$key] = $value; }
    public static function get($key, $default = null) { return $_SESSION[$key] ?? $default; }
    public static function has($key) { return isset($_SESSION[$key]); }
    public static function remove($key) { unset($_SESSION[$key]); }
    public static function destroy() { session_destroy(); $_SESSION = []; }
    public static function regenerate() { session_regenerate_id(true); }

    public static function csrfToken() {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    public static function validateCsrf($token) {
        if (!self::has('csrf_token') || !hash_equals(self::get('csrf_token'), $token)) {
            return false;
        }
        // Jangan hapus token agar bisa reuse di halaman yang sama (misal kasir)
        return true;
    }

    public static function setFlash($key, $message) { self::set("flash_{$key}", $message); }
    public static function getFlash($key) {
        $message = self::get("flash_{$key}");
        self::remove("flash_{$key}");
        return $message;
    }
    public static function hasFlash($key) { return self::has("flash_{$key}"); }
}