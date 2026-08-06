<?php
/**
 * Validation Helper - Fungsi Validasi Data
 * 
 * File ini berisi fungsi-fungsi untuk validasi input data
 */

class ValidationHelper {
    
    private static $errors = [];
    
    /**
     * Validate string tidak kosong
     * 
     * @param string $value
     * @param string $field_name
     * @return bool
     */
    public static function required($value, $field_name = 'Field') {
        $value = trim($value);
        if (empty($value)) {
            self::$errors[] = "$field_name harus diisi";
            return false;
        }
        return true;
    }
    
    /**
     * Validate minimum length
     * 
     * @param string $value
     * @param int $min
     * @param string $field_name
     * @return bool
     */
    public static function minLength($value, $min, $field_name = 'Field') {
        if (strlen($value) < $min) {
            self::$errors[] = "$field_name minimal harus $min karakter";
            return false;
        }
        return true;
    }
    
    /**
     * Validate maximum length
     * 
     * @param string $value
     * @param int $max
     * @param string $field_name
     * @return bool
     */
    public static function maxLength($value, $max, $field_name = 'Field') {
        if (strlen($value) > $max) {
            self::$errors[] = "$field_name maksimal $max karakter";
            return false;
        }
        return true;
    }
    
    /**
     * Validate email format
     * 
     * @param string $value
     * @param string $field_name
     * @return bool
     */
    public static function email($value, $field_name = 'Email') {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::$errors[] = "$field_name format tidak valid";
            return false;
        }
        return true;
    }
    
    /**
     * Validate numeric
     * 
     * @param mixed $value
     * @param string $field_name
     * @return bool
     */
    public static function numeric($value, $field_name = 'Field') {
        if (!is_numeric($value)) {
            self::$errors[] = "$field_name harus berupa angka";
            return false;
        }
        return true;
    }
    
    /**
     * Validate integer
     * 
     * @param mixed $value
     * @param string $field_name
     * @return bool
     */
    public static function integer($value, $field_name = 'Field') {
        if (!is_numeric($value) || strpos($value, '.') !== false) {
            self::$errors[] = "$field_name harus berupa angka bulat";
            return false;
        }
        return true;
    }
    
    /**
     * Validate minimum value
     * 
     * @param numeric $value
     * @param numeric $min
     * @param string $field_name
     * @return bool
     */
    public static function minValue($value, $min, $field_name = 'Field') {
        if ($value < $min) {
            self::$errors[] = "$field_name minimal $min";
            return false;
        }
        return true;
    }
    
    /**
     * Validate maximum value
     * 
     * @param numeric $value
     * @param numeric $max
     * @param string $field_name
     * @return bool
     */
    public static function maxValue($value, $max, $field_name = 'Field') {
        if ($value > $max) {
            self::$errors[] = "$field_name maksimal $max";
            return false;
        }
        return true;
    }
    
    /**
     * Validate phone number
     * 
     * @param string $value
     * @param string $field_name
     * @return bool
     */
    public static function phone($value, $field_name = 'Nomor Telepon') {
        $pattern = '/^(\+62|0)[0-9]{8,12}$/';
        if (!preg_match($pattern, str_replace('-', '', $value))) {
            self::$errors[] = "$field_name tidak valid";
            return false;
        }
        return true;
    }
    
    /**
     * Validate date format
     * 
     * @param string $value
     * @param string $format
     * @param string $field_name
     * @return bool
     */
    public static function date($value, $format = 'Y-m-d', $field_name = 'Tanggal') {
        $date = DateTime::createFromFormat($format, $value);
        if (!$date || $date->format($format) !== $value) {
            self::$errors[] = "$field_name format tidak valid";
            return false;
        }
        return true;
    }
    
    /**
     * Validate unique value in database
     * 
     * @param PDO $db
     * @param string $value
     * @param string $table
     * @param string $column
     * @param string $field_name
     * @param int $exclude_id (opsional, untuk update)
     * @return bool
     */
    public static function unique($db, $value, $table, $column, $field_name = 'Field', $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
        $params = [$value];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                self::$errors[] = "$field_name '$value' sudah digunakan";
                return false;
            }
            return true;
        } catch (PDOException $e) {
            error_log('Validation Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate array dalam in list
     * 
     * @param mixed $value
     * @param array $allowed
     * @param string $field_name
     * @return bool
     */
    public static function inArray($value, $allowed, $field_name = 'Field') {
        if (!in_array($value, $allowed, true)) {
            self::$errors[] = "$field_name nilai tidak valid";
            return false;
        }
        return true;
    }
    
    /**
     * Validate regex pattern
     * 
     * @param string $value
     * @param string $pattern
     * @param string $field_name
     * @return bool
     */
    public static function regex($value, $pattern, $field_name = 'Field') {
        if (!preg_match($pattern, $value)) {
            self::$errors[] = "$field_name format tidak valid";
            return false;
        }
        return true;
    }
    
    /**
     * Validate uploaded file size
     * 
     * @param array $file
     * @param int $max_size (dalam bytes)
     * @param string $field_name
     * @return bool
     */
    public static function fileSize($file, $max_size = MAX_FILE_SIZE, $field_name = 'File') {
        if ($file['size'] > $max_size) {
            self::$errors[] = "$field_name terlalu besar. Maksimal " . formatFileSize($max_size);
            return false;
        }
        return true;
    }
    
    /**
     * Validate uploaded file type
     * 
     * @param array $file
     * @param array $allowed_types
     * @param string $field_name
     * @return bool
     */
    public static function fileType($file, $allowed_types, $field_name = 'File') {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            self::$errors[] = "$field_name tipe tidak didukung. Tipe yang diizinkan: " . implode(', ', $allowed_types);
            return false;
        }
        return true;
    }
    
    /**
     * Get all validation errors
     * 
     * @return array
     */
    public static function getErrors() {
        return self::$errors;
    }
    
    /**
     * Get first error
     * 
     * @return string|null
     */
    public static function getFirstError() {
        return self::$errors[0] ?? null;
    }
    
    /**
     * Check apakah ada error
     * 
     * @return bool
     */
    public static function hasErrors() {
        return count(self::$errors) > 0;
    }
    
    /**
     * Clear all errors
     */
    public static function clearErrors() {
        self::$errors = [];
    }
    
    /**
     * Add custom error message
     * 
     * @param string $message
     */
    public static function addError($message) {
        self::$errors[] = $message;
    }
}

?>
