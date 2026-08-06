<?php
/**
 * FileHelper - Fungsi manipulasi file
 */

class FileHelper {
    
    /**
     * Upload file
     */
    public static function upload($file_input, $upload_dir, $allowed_types = []) {
        if (!isset($_FILES[$file_input])) {
            return ['success' => false, 'message' => 'File tidak ditemukan'];
        }
        
        $file = $_FILES[$file_input];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error uploading file'];
        }
        
        // Check file type
        if (!empty($allowed_types)) {
            $file_type = mime_content_type($file['tmp_name']);
            if (!in_array($file_type, $allowed_types)) {
                return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
            }
        }
        
        // Generate filename
        $filename = time() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            ];
        }
        
        return ['success' => false, 'message' => 'Gagal upload file'];
    }
    
    /**
     * Delete file
     */
    public static function delete($filepath) {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    /**
     * Get file extension
     */
    public static function getExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Get file size in human readable format
     */
    public static function getFileSize($filepath) {
        $size = filesize($filepath);
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = max($size, 0);
        $pow = floor(($size ? log($size) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $size /= (1 << (10 * $pow));
        
        return round($size, 2) . ' ' . $units[$pow];
    }
}

?>
