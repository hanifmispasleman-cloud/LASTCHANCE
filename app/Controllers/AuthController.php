<?php
/**
 * Auth Controller - Controller untuk Authentication
 */

require_once APP_DIR . 'Models/User.php';

class AuthController {
    
    private $db;
    private $userModel;
    
    public function __construct($database) {
        $this->db = $database;
        $this->userModel = new User($database);
    }
    
    /**
     * Show login form
     */
    public function loginForm() {
        // Jika sudah login redirect ke dashboard
        if (AuthMiddleware::isAuthenticated()) {
            redirect('dashboard');
        }
        
        $title = 'Login - KasirKu';
        $expired = $_GET['expired'] ?? false;
        
        require VIEW_DIR . 'auth/login.php';
    }
    
    /**
     * Process login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/login');
        }
        
        // Validate CSRF token
        if (!CsrfMiddleware::verifyToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            SecurityMiddleware::logSecurityEvent('CSRF_FAILURE', ['action' => 'login']);
            jsonResponse(false, 'CSRF Token tidak valid', null, 403);
        }
        
        // Rate limiting
        $ip = RateLimitMiddleware::getClientIP();
        if (!RateLimitMiddleware::check($ip . '_login', 5, 300)) {
            SecurityMiddleware::logSecurityEvent('LOGIN_RATE_LIMIT', ['ip' => $ip]);
            jsonResponse(false, 'Terlalu banyak percobaan login. Silakan coba lagi dalam beberapa menit', null, 429);
        }
        
        // Get input
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validasi input
        if (empty($username) || empty($password)) {
            jsonResponse(false, 'Username dan password harus diisi');
        }
        
        // Attempt login
        $user = $this->userModel->login($username, $password);
        
        if (!$user) {
            SecurityMiddleware::logSecurityEvent('LOGIN_FAILED', ['username' => $username]);
            jsonResponse(false, 'Username atau password salah');
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['last_activity'] = time();
        
        // Log activity
        logActivity($this->db, 'LOGIN', 'users', $user['id']);
        
        // Reset rate limit
        RateLimitMiddleware::reset($ip . '_login');
        
        SecurityMiddleware::logSecurityEvent('LOGIN_SUCCESS', ['user_id' => $user['id']]);
        
        // Redirect
        $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard';
        unset($_SESSION['redirect_after_login']);
        
        jsonResponse(true, 'Login berhasil', ['redirect' => BASE_URL . $redirect]);
    }
    
    /**
     * Logout
     */
    public function logout() {
        AuthMiddleware::requireLogin();
        
        $user_id = $_SESSION['user_id'];
        
        // Log activity
        logActivity($this->db, 'LOGOUT', 'users', $user_id);
        
        // Destroy session
        session_destroy();
        $_SESSION = [];
        
        SecurityMiddleware::logSecurityEvent('LOGOUT_SUCCESS', ['user_id' => $user_id]);
        
        redirect('auth/login');
    }
    
    /**
     * Show profile
     */
    public function profile() {
        AuthMiddleware::requireLogin();
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        $title = 'Profile - KasirKu';
        
        require VIEW_DIR . 'auth/profile.php';
    }
    
    /**
     * Update profile
     */
    public function updateProfile() {
        AuthMiddleware::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/profile');
        }
        
        if (!CsrfMiddleware::verifyToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            jsonResponse(false, 'CSRF Token tidak valid', null, 403);
        }
        
        $user_id = $_SESSION['user_id'];
        
        $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        
        // Validasi
        ValidationHelper::clearErrors();
        ValidationHelper::required($nama_lengkap, 'Nama lengkap');
        ValidationHelper::required($email, 'Email');
        ValidationHelper::email($email, 'Email');
        
        if (!ValidationHelper::isEmailAvailable($this->db, $email, 'users', 'email', 'Email', $user_id)) {
            ValidationHelper::addError('Email sudah digunakan');
        }
        
        if (ValidationHelper::hasErrors()) {
            jsonResponse(false, ValidationHelper::getFirstError());
        }
        
        // Update
        $data = [
            'nama_lengkap' => $nama_lengkap,
            'email' => $email
        ];
        
        if ($this->userModel->update($user_id, $data)) {
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['email'] = $email;
            
            logActivity($this->db, 'UPDATE_PROFILE', 'users', $user_id);
            
            jsonResponse(true, 'Profile berhasil diupdate');
        } else {
            jsonResponse(false, 'Gagal update profile');
        }
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        AuthMiddleware::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request');
        }
        
        if (!CsrfMiddleware::verifyToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            jsonResponse(false, 'CSRF Token tidak valid', null, 403);
        }
        
        $user_id = $_SESSION['user_id'];
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validasi
        ValidationHelper::clearErrors();
        ValidationHelper::required($old_password, 'Password lama');
        ValidationHelper::required($new_password, 'Password baru');
        ValidationHelper::required($confirm_password, 'Konfirmasi password');
        ValidationHelper::minLength($new_password, 6, 'Password baru');
        
        if (ValidationHelper::hasErrors()) {
            jsonResponse(false, ValidationHelper::getFirstError());
        }
        
        // Verify old password
        if (!$this->userModel->verifyPassword($user_id, $old_password)) {
            jsonResponse(false, 'Password lama tidak sesuai');
        }
        
        // Confirm password
        if ($new_password !== $confirm_password) {
            jsonResponse(false, 'Konfirmasi password tidak sesuai');
        }
        
        // Update password
        if ($this->userModel->updatePassword($user_id, $new_password)) {
            logActivity($this->db, 'CHANGE_PASSWORD', 'users', $user_id);
            jsonResponse(true, 'Password berhasil diubah');
        } else {
            jsonResponse(false, 'Gagal mengubah password');
        }
    }
}

?>
