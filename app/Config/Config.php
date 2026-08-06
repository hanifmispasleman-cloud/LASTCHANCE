<?php
/**
 * Konfigurasi Global Aplikasi KasirKu
 * 
 * Definisi constant dan konfigurasi sistem yang digunakan di seluruh aplikasi
 */

// ============================================
// KONFIGURASI DASAR
// ============================================

// URL Aplikasi
define('BASE_URL', 'http://localhost/kasirku/');
define('APP_NAME', 'KasirKu');
define('APP_VERSION', '1.0.0');

// ============================================
// PATH APLIKASI
// ============================================

define('APP_PATH', dirname(dirname(__DIR__)) . '/');
define('APP_DIR', APP_PATH . 'app/');
define('CONFIG_DIR', APP_DIR . 'Config/');
define('CONTROLLER_DIR', APP_DIR . 'Controllers/');
define('MODEL_DIR', APP_DIR . 'Models/');
define('MIDDLEWARE_DIR', APP_DIR . 'Middleware/');
define('HELPER_DIR', APP_DIR . 'Helpers/');
define('VIEW_DIR', APP_PATH . 'views/');
define('PUBLIC_DIR', APP_PATH . 'public/');
define('STORAGE_DIR', APP_PATH . 'storage/');
define('UPLOAD_DIR', PUBLIC_DIR . 'uploads/');

// ============================================
// KEAMANAN
// ============================================

// Session timeout (dalam detik) - 30 menit
define('SESSION_TIMEOUT', 1800);

// Session name
define('SESSION_NAME', 'KASIRKU_SESSION');

// CSRF Token name
define('CSRF_TOKEN_NAME', 'csrf_token');

// Password hash algorithm
define('PASSWORD_ALGO', PASSWORD_BCRYPT);

// ============================================
// DATABASE
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'kasirku');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// FILE UPLOAD
// ============================================

// Direktori upload
define('UPLOAD_PRODUCT_DIR', 'uploads/products/');
define('UPLOAD_PROFILE_DIR', 'uploads/profiles/');
define('UPLOAD_LOGO_DIR', 'uploads/logos/');

// Max file size (dalam bytes) - 5MB
define('MAX_FILE_SIZE', 5242880);

// Tipe file yang diizinkan
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'xlsx', 'xls', 'doc', 'docx']);

// ============================================
// MATA UANG & FORMAT
// ============================================

define('CURRENCY', 'IDR');
define('CURRENCY_SYMBOL', 'Rp');
define('DECIMAL_SEPARATOR', ',');
define('THOUSANDS_SEPARATOR', '.');
define('DECIMAL_PLACES', 2);

// ============================================
// ZONA WAKTU
// ============================================

define('TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set(TIMEZONE);

// ============================================
// ROLE & PERMISSION
// ============================================

define('ROLE_ADMIN', 'admin');
define('ROLE_KASIR', 'kasir');
define('ROLE_OWNER', 'owner');

// ============================================
// DEBUG MODE
// ============================================

define('DEBUG_MODE', true);
define('ERROR_LOG_FILE', STORAGE_DIR . 'logs/error.log');
define('ACCESS_LOG_FILE', STORAGE_DIR . 'logs/access.log');

// ============================================
// PAGINATION
// ============================================

define('ITEMS_PER_PAGE', 20);

// ============================================
// FORMAT DATA
// ============================================

define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');
define('TIME_FORMAT', 'H:i:s');
define('DB_DATE_FORMAT', 'Y-m-d');
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');

// ============================================
// RECEIPT & REPORT
// ============================================

define('RECEIPT_WIDTH', 80);
define('REPORT_PREFIX', 'Laporan_KasirKu_');

// ============================================
// SELESAI KONFIGURASI
// ============================================
?>
