<?php
/**
 * Helper Functions - Fungsi Umum KasirKu
 * 
 * File ini berisi fungsi-fungsi helper yang digunakan di seluruh aplikasi
 */

/**
 * Format currency ke format Indonesia
 * 
 * @param float $amount
 * @return string
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, DECIMAL_PLACES, DECIMAL_SEPARATOR, THOUSANDS_SEPARATOR);
}

/**
 * Parse currency string ke float
 * 
 * @param string $value
 * @return float
 */
function parseCurrency($value) {
    $value = str_replace(CURRENCY_SYMBOL, '', $value);
    $value = str_replace(THOUSANDS_SEPARATOR, '', $value);
    $value = str_replace(DECIMAL_SEPARATOR, '.', $value);
    return (float)$value;
}

/**
 * Format tanggal ke format Indonesia
 * 
 * @param string $date
 * @return string
 */
function formatDate($date) {
    if (empty($date)) return '-';
    return date(DATE_FORMAT, strtotime($date));
}

/**
 * Format datetime ke format Indonesia
 * 
 * @param string $datetime
 * @return string
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    return date(DATETIME_FORMAT, strtotime($datetime));
}

/**
 * Format waktu
 * 
 * @param string $time
 * @return string
 */
function formatTime($time) {
    if (empty($time)) return '-';
    return date(TIME_FORMAT, strtotime($time));
}

/**
 * Redirect ke halaman lain
 * 
 * @param string $location
 */
function redirect($location) {
    header('Location: ' . BASE_URL . $location);
    exit();
}

/**
 * Generate CSRF Token
 * 
 * @return string
 */
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF Token
 * 
 * @param string $token
 * @return bool
 */
function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize input string
 * 
 * @param string $input
 * @return string
 */
function sanitize($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Validasi email
 * 
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 * 
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ALGO);
}

/**
 * Verify password
 * 
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate nomor unik untuk transaksi
 * 
 * @param string $prefix
 * @return string
 */
function generateUniqueNumber($prefix = '') {
    return $prefix . date('YmdHis') . rand(1000, 9999);
}

/**
 * Generate kode produk unik
 * 
 * @return string
 */
function generateProductCode() {
    return 'PRD' . date('YmdHis') . rand(100, 999);
}

/**
 * Convert angka ke teks (untuk struk)
 * 
 * @param int $num
 * @return string
 */
function numberToWords($num) {
    $num = abs($num);
    $words = '';
    $list1 = array(
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
        'sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
        'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'
    );
    $list2 = array('', 'dua puluh', 'tiga puluh', 'empat puluh', 'lima puluh', 'enam puluh',
        'tujuh puluh', 'delapan puluh', 'sembilan puluh'
    );
    $list3 = array('', 'seratus', 'dua ratus', 'tiga ratus', 'empat ratus', 'lima ratus',
        'enam ratus', 'tujuh ratus', 'delapan ratus', 'sembilan ratus'
    );
    $list4 = array('', 'seribu', 'sejuta', 'setriliun', 'setriliun', 'semiliar');

    $num = number_format($num, 2, '.', ',');
    $num_arr = explode('.', $num);
    $wholenum = $num_arr[0];
    $decnum = $num_arr[1];
    $whole_arr = array_reverse(explode(',', number_format($wholenum, 0, '.', ',')));
    krsort($whole_arr, 1);
    $tn = count($whole_arr) - 1;
    $words = '';
    foreach ($whole_arr as $w) {
        $w = intval($w);
        $string = '';
        if ($w >= 100) {
            $hundreds = intval($w / 100);
            $remainder = intval($w % 100);
            $string .= $list3[$hundreds] . ' ';
            $w = $remainder;
        }
        if ($w >= 20) {
            $tens = intval($w / 10);
            $units = intval($w % 10);
            $string .= $list2[$tens];
            if ($units > 0)
                $string .= ' ' . $list1[$units];
        } else if ($w > 0)
            $string .= $list1[$w];
        if ($w == 0 and $tn == 1)
            break;
        if ($string != '') {
            $words .= $string . $list4[$tn] . ' ';
        }
        $tn--;
    }

    if ($decnum > 0) {
        $words .= ' koma ';
        $decnum_arr = str_split($decnum);
        foreach ($decnum_arr as $d)
            $words .= $list1[$d] . ' ';
    }
    return trim($words);
}

/**
 * Get file size dalam format yang readable
 * 
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes) {
    $size = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024 && $i < count($size) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, 2) . ' ' . $size[$i];
}

/**
 * Check apakah user sudah login
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Get data user yang sedang login
 * 
 * @return array|null
 */
function getLoggedInUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'nama_lengkap' => $_SESSION['nama_lengkap'],
        'role' => $_SESSION['role'],
        'email' => $_SESSION['email'] ?? null
    ];
}

/**
 * Check user role
 * 
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

/**
 * Check user memiliki salah satu role
 * 
 * @param array $roles
 * @return bool
 */
function hasAnyRole($roles) {
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['role'], $roles);
}

/**
 * Log activity
 * 
 * @param PDO $db
 * @param string $action
 * @param string $table_name
 * @param int $record_id
 * @param mixed $old_value
 * @param mixed $new_value
 */
function logActivity($db, $action, $table_name = null, $record_id = null, $old_value = null, $new_value = null) {
    if (!isLoggedIn()) return false;
    
    try {
        $user_id = $_SESSION['user_id'];
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $sql = "INSERT INTO activity_log (user_id, action, table_name, record_id, old_value, new_value, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $user_id,
            $action,
            $table_name,
            $record_id,
            is_array($old_value) ? json_encode($old_value) : $old_value,
            is_array($new_value) ? json_encode($new_value) : $new_value,
            $ip_address
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log('Log Activity Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get current page number
 * 
 * @return int
 */
function getCurrentPage() {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    return $page < 1 ? 1 : $page;
}

/**
 * Get offset untuk pagination
 * 
 * @param int $page
 * @param int $per_page
 * @return int
 */
function getPageOffset($page = 1, $per_page = ITEMS_PER_PAGE) {
    return ($page - 1) * $per_page;
}

/**
 * Generate pagination HTML
 * 
 * @param int $total
 * @param int $per_page
 * @param int $current_page
 * @param string $base_url
 * @return string
 */
function generatePagination($total, $per_page = ITEMS_PER_PAGE, $current_page = 1, $base_url = '') {
    $total_pages = ceil($total / $per_page);
    if ($total_pages <= 1) return '';
    
    $html = '<nav aria-label="Pagination Navigation"><ul class="pagination">';
    
    // Previous
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page - 1) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Pages
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page + 1) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Get HTTP response message
 * 
 * @param int $code
 * @return string
 */
function getHttpResponseMessage($code) {
    $messages = [
        200 => 'OK',
        201 => 'Created',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable'
    ];
    
    return $messages[$code] ?? 'Unknown';
}

/**
 * Output JSON response
 * 
 * @param bool $success
 * @param string $message
 * @param mixed $data
 * @param int $code
 */
function jsonResponse($success, $message, $data = null, $code = 200) {
    header('Content-Type: application/json');
    http_response_code($code);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Encode data to JSON
 * 
 * @param mixed $data
 * @return string
 */
function toJson($data) {
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * Decode JSON string
 * 
 * @param string $json
 * @return mixed
 */
function fromJson($json) {
    return json_decode($json, true);
}

?>
