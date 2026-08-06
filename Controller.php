<?php
namespace App\Core;

abstract class Controller {
    protected $db;

    public function __construct() {
        $this->db = \App\Config\Database::getInstance()->getConnection();
        Session::start();
    }

    protected function view($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View {$view} tidak ditemukan");
        }
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? \App\Config\App::BASE_URL;
        header('Location: ' . $referer);
        exit;
    }

    protected function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function validate($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);
            $value = $data[$field] ?? '';
            foreach ($ruleList as $rule) {
                if ($rule === 'required' && empty(trim($value))) {
                    $errors[$field] = ucfirst($field) . ' wajib diisi';
                } elseif (strpos($rule, 'min:') === 0) {
                    $min = (int)substr($rule, 4);
                    if (strlen($value) < $min) $errors[$field] = ucfirst($field) . " minimal {$min} karakter";
                } elseif (strpos($rule, 'max:') === 0) {
                    $max = (int)substr($rule, 4);
                    if (strlen($value) > $max) $errors[$field] = ucfirst($field) . " maksimal {$max} karakter";
                } elseif ($rule === 'numeric' && !is_numeric($value) && !empty($value)) {
                    $errors[$field] = ucfirst($field) . ' harus berupa angka';
                } elseif ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL) && !empty($value)) {
                    $errors[$field] = 'Format email tidak valid';
                }
            }
        }
        return $errors;
    }

    protected function sanitize($data) {
        if (is_array($data)) return array_map([$this, 'sanitize'], $data);
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    protected function uploadFile($file, $directory = 'uploads') {
        $targetDir = __DIR__ . '/../../public/' . $directory . '/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
        $targetFile = $targetDir . $fileName;
        $extension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (!in_array($extension, \App\Config\App::ALLOWED_EXTENSIONS)) {
            return ['error' => 'Tipe file tidak diizinkan'];
        }
        if ($file['size'] > \App\Config\App::UPLOAD_MAX_SIZE) {
            return ['error' => 'Ukuran file terlalu besar (max 2MB)'];
        }
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return ['success' => true, 'filename' => $fileName, 'path' => $directory . '/' . $fileName];
        }
        return ['error' => 'Gagal mengupload file'];
    }
}