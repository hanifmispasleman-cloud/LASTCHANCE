<?php
/**
 * Rate Limit Middleware - Proteksi dari brute force attack
 * 
 * Middleware ini membatasi jumlah request dalam periode waktu tertentu
 */

class RateLimitMiddleware {
    
    private static $storage_file = STORAGE_DIR . 'rate_limit.json';
    
    /**
     * Check rate limit
     * 
     * @param string $identifier (IP address atau user ID)
     * @param int $max_requests (jumlah request yang diizinkan)
     * @param int $time_window (periode waktu dalam detik)
     * @return bool (true = within limit, false = exceeded)
     */
    public static function check($identifier, $max_requests = 10, $time_window = 300) {
        $storage_file = self::$storage_file;
        
        // Buat direktori storage jika belum ada
        if (!is_dir(STORAGE_DIR)) {
            mkdir(STORAGE_DIR, 0755, true);
        }
        
        // Baca data rate limit
        $rate_limits = [];
        if (file_exists($storage_file)) {
            $rate_limits = json_decode(file_get_contents($storage_file), true) ?? [];
        }
        
        $current_time = time();
        
        // Bersihkan expired entries
        foreach ($rate_limits as $key => $data) {
            if ($current_time - $data['last_request'] > $time_window) {
                unset($rate_limits[$key]);
            }
        }
        
        // Check identifier
        if (isset($rate_limits[$identifier])) {
            $data = $rate_limits[$identifier];
            
            // Reset counter jika time window sudah lewat
            if ($current_time - $data['last_request'] > $time_window) {
                $rate_limits[$identifier] = [
                    'count' => 1,
                    'first_request' => $current_time,
                    'last_request' => $current_time
                ];
                self::saveRateLimits($rate_limits);
                return true;
            }
            
            // Check apakah sudah exceed limit
            if ($data['count'] >= $max_requests) {
                return false;
            }
            
            // Increment counter
            $rate_limits[$identifier]['count']++;
            $rate_limits[$identifier]['last_request'] = $current_time;
        } else {
            // Create new entry
            $rate_limits[$identifier] = [
                'count' => 1,
                'first_request' => $current_time,
                'last_request' => $current_time
            ];
        }
        
        self::saveRateLimits($rate_limits);
        return true;
    }
    
    /**
     * Get remaining requests
     * 
     * @param string $identifier
     * @param int $max_requests
     * @param int $time_window
     * @return int
     */
    public static function getRemaining($identifier, $max_requests = 10, $time_window = 300) {
        $storage_file = self::$storage_file;
        
        if (!file_exists($storage_file)) {
            return $max_requests;
        }
        
        $rate_limits = json_decode(file_get_contents($storage_file), true) ?? [];
        $current_time = time();
        
        if (!isset($rate_limits[$identifier])) {
            return $max_requests;
        }
        
        $data = $rate_limits[$identifier];
        
        // Check apakah time window sudah lewat
        if ($current_time - $data['last_request'] > $time_window) {
            return $max_requests;
        }
        
        return max(0, $max_requests - $data['count']);
    }
    
    /**
     * Reset rate limit untuk identifier
     * 
     * @param string $identifier
     */
    public static function reset($identifier) {
        $storage_file = self::$storage_file;
        
        if (!file_exists($storage_file)) {
            return;
        }
        
        $rate_limits = json_decode(file_get_contents($storage_file), true) ?? [];
        
        if (isset($rate_limits[$identifier])) {
            unset($rate_limits[$identifier]);
            self::saveRateLimits($rate_limits);
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple IPs (take the first one)
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validate IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Save rate limits to storage
     * 
     * @param array $rate_limits
     */
    private static function saveRateLimits($rate_limits) {
        if (!is_dir(STORAGE_DIR)) {
            mkdir(STORAGE_DIR, 0755, true);
        }
        
        file_put_contents(self::$storage_file, json_encode($rate_limits), LOCK_EX);
    }
    
    /**
     * Check rate limit by IP dengan aksi jika exceeded
     * 
     * @param int $max_requests
     * @param int $time_window
     * @return void
     */
    public static function checkByIP($max_requests = 100, $time_window = 3600) {
        $ip = self::getClientIP();
        
        if (!self::check($ip, $max_requests, $time_window)) {
            http_response_code(429);
            die('Terlalu banyak request. Silakan coba lagi dalam beberapa menit.');
        }
    }
}

?>
