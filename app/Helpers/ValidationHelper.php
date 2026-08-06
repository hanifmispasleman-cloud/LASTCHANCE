<?php
/**
 * ValidationHelper - Fungsi validasi form
 */

class ValidationHelper {
    
    private static $errors = [];
    
    /**
     * Validate required field
     */
    public static function required($value, $field_name) {
        if (empty($value)) {
            self::$errors[] = $field_name . ' tidak boleh kosong';
            return false;
        }
        return true;
    }
    
    /**
     * Validate min length
     */
    public static function minLength($value, $field_name, $length) {
        if (strlen($value) < $length) {
            self::$errors[] = $field_name . ' minimal ' . $length . ' karakter';
            return false;
        }
        return true;
    }
    
    /**
     * Validate max length
     */
    public static function maxLength($value, $field_name, $length) {
        if (strlen($value) > $length) {
            self::$errors[] = $field_name . ' maksimal ' . $length . ' karakter';
            return false;
        }
        return true;
    }
    
    /**
     * Validate email format
     */
    public static function email($value, $field_name) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::$errors[] = $field_name . ' format tidak valid';
            return false;
        }
        return true;
    }
    
    /**
     * Validate numeric
     */
    public static function numeric($value, $field_name) {
        if (!is_numeric($value)) {
            self::$errors[] = $field_name . ' harus angka';
            return false;
        }
        return true;
    }
    
    /**
     * Validate min value
     */
    public static function min($value, $field_name, $min) {
        if ((int)$value < $min) {
            self::$errors[] = $field_name . ' minimal ' . $min;
            return false;
        }
        return true;
    }
    
    /**
     * Validate max value
     */
    public static function max($value, $field_name, $max) {
        if ((int)$value > $max) {
            self::$errors[] = $field_name . ' maksimal ' . $max;
            return false;
        }
        return true;
    }
    
    /**
     * Validate match two fields
     */
    public static function match($value1, $value2, $field_name) {
        if ($value1 !== $value2) {
            self::$errors[] = $field_name . ' tidak cocok';
            return false;
        }
        return true;
    }
    
    /**
     * Validate unique in database
     */
    public static function unique($value, $field_name, $table, $column, $db) {
        $query = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $value);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            self::$errors[] = $field_name . ' sudah terdaftar';
            return false;
        }
        return true;
    }
    
    /**
     * Get all errors
     */
    public static function getErrors() {
        return self::$errors;
    }
    
    /**
     * Has errors
     */
    public static function hasErrors() {
        return count(self::$errors) > 0;
    }
    
    /**
     * Clear errors
     */
    public static function clearErrors() {
        self::$errors = [];
    }
}

?>
