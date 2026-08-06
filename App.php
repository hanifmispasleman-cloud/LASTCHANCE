<?php
namespace App\Config;

class App {
    const BASE_URL = 'http://localhost/KasirKu'; // sesuaikan
    const APP_NAME = 'KasirKu POS';
    const APP_VERSION = '1.0.0';
    const TIMEZONE = 'Asia/Jakarta';
    const SESSION_LIFETIME = 7200;
    const UPLOAD_MAX_SIZE = 2097152;
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    const ITEMS_PER_PAGE = 20;
    const DEFAULT_PAJAK = 11;
}