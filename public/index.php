<?php
/**
 * KasirKu - Point of Sale System
 * 
 * File utama aplikasi - Entry point
 */

// ============================================
// SETUP PATH & AUTOLOADER
// ============================================

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/app/Config/Config.php';

// ============================================
// AUTOLOADER UNTUK CLASS
// ============================================

spl_autoload_register(function($class) {
    $dirs = [
        CONFIG_DIR,
        MIDDLEWARE_DIR,
        MODEL_DIR,
        CONTROLLER_DIR,
        HELPER_DIR,
        APP_DIR . 'Core/'
    ];
    
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});

// ============================================
// LOAD HELPERS
// ============================================

require_once HELPER_DIR . 'Helper.php';
require_once HELPER_DIR . 'ValidationHelper.php';
require_once HELPER_DIR . 'FileHelper.php';

// ============================================
// RUN APLIKASI
// ============================================

try {
    $app = new Application();
    $app->run();
} catch (Exception $e) {
    error_log('Application Error: ' . $e->getMessage());
    http_response_code(500);
    
    if (DEBUG_MODE) {
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>Terjadi Kesalahan</h1>';
        echo '<p>Silakan hubungi administrator.</p>';
    }
}

?>
