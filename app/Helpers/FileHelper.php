<?php
/**
 * File Helper - Fungsi Penanganan File
 * 
 * File ini berisi fungsi-fungsi untuk upload, delete, dan manipulasi file
 */

class FileHelper {
    
    /**
     * Upload file
     * 
     * @param array $file (dari $_FILES)
     * @param string $destination (direktori tujuan)
     * @param array $allowed_types
     * @param int $max_size
     * @return array
     */
    public static function upload($file, $destination, $allowed_types = [], $max_size = MAX_FILE_SIZE) {
        $response = [
            'success' => false,
            'message' => '',
            'filename' => '',
            'path' => ''
        ];
        
        // Validasi file ada
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $response['message'] = 'Tidak ada file yang dipilih';
            return $response;
        }
        
        // Validasi file error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = self::getUploadErrorMessage($file['error']);
            return $response;
        }
        
        // Validasi ukuran file
        if ($file['size'] > $max_size) {
            $response['message'] = 'Ukuran file terlalu besar. Maksimal ' . formatFileSize($max_size);
            return $response;
        }
        
        // Validasi tipe file
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowed_types) && !in_array($file_ext, $allowed_types)) {
            $response['message'] = 'Tipe file tidak didukung';
            return $response;
        }
        
        // Buat direktori jika belum ada
        $upload_path = PUBLIC_DIR . $destination;
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid('file_') . '.' . $file_ext;
        $file_path = $upload_path . $filename;
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $response['success'] = true;
            $response['message'] = 'File berhasil diupload';
            $response['filename'] = $filename;
            $response['path'] = $destination . $filename;
            return $response;
        } else {
            $response['message'] = 'Gagal mengupload file';
            return $response;
        }
    }
    
    /**
     * Upload file dengan custom filename
     * 
     * @param array $file
     * @param string $destination
     * @param string $custom_filename
     * @param array $allowed_types
     * @return array
     */
    public static function uploadWithCustomName($file, $destination, $custom_filename, $allowed_types = []) {
        $response = [
            'success' => false,
            'message' => '',
            'filename' => '',
            'path' => ''
        ];
        
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $response['message'] = 'Tidak ada file yang dipilih';
            return $response;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = self::getUploadErrorMessage($file['error']);
            return $response;
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowed_types) && !in_array($file_ext, $allowed_types)) {
            $response['message'] = 'Tipe file tidak didukung';
            return $response;
        }
        
        $upload_path = PUBLIC_DIR . $destination;
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $filename = $custom_filename . '.' . $file_ext;
        $file_path = $upload_path . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $response['success'] = true;
            $response['message'] = 'File berhasil diupload';
            $response['filename'] = $filename;
            $response['path'] = $destination . $filename;
            return $response;
        } else {
            $response['message'] = 'Gagal mengupload file';
            return $response;
        }
    }
    
    /**
     * Delete file
     * 
     * @param string $file_path
     * @return bool
     */
    public static function delete($file_path) {
        $full_path = PUBLIC_DIR . $file_path;
        
        if (file_exists($full_path) && is_file($full_path)) {
            return unlink($full_path);
        }
        
        return false;
    }
    
    /**
     * Get upload error message
     * 
     * @param int $error
     * @return string
     */
    private static function getUploadErrorMessage($error) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melampaui upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melampaui MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih',
            UPLOAD_ERR_NO_TMP_DIR => 'Direktori sementara tidak tersedia',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dibatalkan oleh ekstensi PHP'
        ];
        
        return $messages[$error] ?? 'Error tidak diketahui';
    }
    
    /**
     * Generate thumbnail dari image
     * 
     * @param string $source_path
     * @param string $dest_path
     * @param int $width
     * @param int $height
     * @return bool
     */
    public static function generateThumbnail($source_path, $dest_path, $width = 200, $height = 200) {
        if (!extension_loaded('gd')) {
            error_log('GD Library tidak tersedia');
            return false;
        }
        
        $source_full = PUBLIC_DIR . $source_path;
        $dest_full = PUBLIC_DIR . $dest_path;
        
        if (!file_exists($source_full)) {
            return false;
        }
        
        $image_info = getimagesize($source_full);
        if (!$image_info) {
            return false;
        }
        
        $image_type = $image_info[2];
        
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $src_image = imagecreatefromjpeg($source_full);
                break;
            case IMAGETYPE_PNG:
                $src_image = imagecreatefrompng($source_full);
                break;
            case IMAGETYPE_GIF:
                $src_image = imagecreatefromgif($source_full);
                break;
            default:
                return false;
        }
        
        if (!$src_image) {
            return false;
        }
        
        $src_width = imagesx($src_image);
        $src_height = imagesy($src_image);
        
        $aspect_ratio = $src_width / $src_height;
        
        if ($width / $height > $aspect_ratio) {
            $new_height = $height;
            $new_width = intval($height * $aspect_ratio);
        } else {
            $new_width = $width;
            $new_height = intval($width / $aspect_ratio);
        }
        
        $dst_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $src_width, $src_height);
        
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($dst_image, $dest_full, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($dst_image, $dest_full, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($dst_image, $dest_full);
                break;
        }
        
        imagedestroy($src_image);
        imagedestroy($dst_image);
        
        return true;
    }
    
    /**
     * Check file exists
     * 
     * @param string $file_path
     * @return bool
     */
    public static function exists($file_path) {
        return file_exists(PUBLIC_DIR . $file_path);
    }
}

?>
