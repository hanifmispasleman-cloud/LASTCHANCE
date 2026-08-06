<?php
/**
 * AuthController - Controller untuk authentication
 */

class AuthController {
    
    private $userModel;
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
        $this->userModel = new User($database);
    }
    
    /**
     * Show login page
     */
    public function login() {
        AuthMiddleware::checkGuest();
        SecurityMiddleware::setSecurityHeaders();
        
        $page_title = 'Login - KasirKu';
        include APP_PATH . 'Views/Auth/login.php';
    }
    
    /**
     * Process login
     */
    public function doLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/login');
        }
        
        SecurityMiddleware::validateCSRFToken();
        SecurityMiddleware::checkRateLimit('login', 5, 300);
        
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validate input
        if (empty($email) || empty($password)) {
            $error = 'Email dan password harus diisi';
            include APP_PATH . 'Views/Auth/login.php';
            return;
        }
        
        // Get user
        $user = $this->userModel->getByEmail($email);
        
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            $error = 'Email atau password salah';
            include APP_PATH . 'Views/Auth/login.php';
            return;
        }
        
        if ($user['status'] !== 'active') {
            $error = 'Akun Anda tidak aktif';
            include APP_PATH . 'Views/Auth/login.php';
            return;
        }
        
        // Set session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        
        $_SESSION['last_activity'] = time();
        
        // Remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/');
            // Save token to database for validation
        }
        
        log_activity('LOGIN', 'User ' . $user['email'] . ' logged in');
        
        redirect('dashboard');
    }
    
    /**
     * Logout
     */
    public function logout() {
        if (isset($_SESSION['user'])) {
            $email = $_SESSION['user']['email'];
            log_activity('LOGOUT', 'User ' . $email . ' logged out');
        }
        
        session_destroy();
        setcookie('remember_me', '', time() - 3600, '/');
        
        redirect('auth/login');
    }
    
    /**
     * Show profile page
     */
    public function profile() {
        AuthMiddleware::checkAuth();
        AuthMiddleware::checkSessionTimeout();
        SecurityMiddleware::setSecurityHeaders();
        
        $user = $this->userModel->getById($_SESSION['user']['id']);
        $page_title = 'Profil - KasirKu';
        
        include APP_PATH . 'Views/Auth/profile.php';
    }
    
    /**
     * Update profile
     */
    public function updateProfile() {
        AuthMiddleware::checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/profile');
        }
        
        SecurityMiddleware::validateCSRFToken();
        
        $id = $_SESSION['user']['id'];
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        
        // Validate
        $errors = [];
        if (empty($name)) $errors[] = 'Nama tidak boleh kosong';
        if (empty($email)) $errors[] = 'Email tidak boleh kosong';
        if (!is_valid_email($email)) $errors[] = 'Email tidak valid';
        
        if (count($errors) > 0) {
            $_SESSION['errors'] = $errors;
            redirect('auth/profile');
        }
        
        // Update
        $update_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ];
        
        if ($this->userModel->update($id, $update_data)) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            set_flash('Profil berhasil diperbarui', 'success');
            log_activity('UPDATE_PROFILE', 'User ' . $email . ' updated their profile');
        } else {
            set_flash('Gagal memperbarui profil', 'danger');
        }
        
        redirect('auth/profile');
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        AuthMiddleware::checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/profile');
        }
        
        SecurityMiddleware::validateCSRFToken();
        
        $id = $_SESSION['user']['id'];
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Get current user
        $user = $this->userModel->getById($id);
        
        // Validate
        $errors = [];
        if (empty($old_password)) $errors[] = 'Password lama harus diisi';
        if (empty($new_password)) $errors[] = 'Password baru harus diisi';
        if (empty($confirm_password)) $errors[] = 'Konfirmasi password harus diisi';
        
        if (!$this->userModel->verifyPassword($old_password, $user['password'])) {
            $errors[] = 'Password lama tidak sesuai';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'Password baru tidak cocok';
        }
        
        if (count($errors) > 0) {
            $_SESSION['errors'] = $errors;
            redirect('auth/profile');
        }
        
        // Update password
        $hashed_password = $this->userModel->hashPassword($new_password);
        
        if ($this->userModel->update($id, ['password' => $hashed_password])) {
            set_flash('Password berhasil diubah', 'success');
            log_activity('CHANGE_PASSWORD', 'User ' . $user['email'] . ' changed their password');
        } else {
            set_flash('Gagal mengubah password', 'danger');
        }
        
        redirect('auth/profile');
    }
}

?>
