<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller {
    public function loginForm() {
        if (Session::has('user_id')) $this->redirect(\App\Config\App::BASE_URL . '/dashboard');
        $this->view('auth/login', ['title' => 'Login - KasirKu', 'csrf_token' => Session::csrfToken()]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/login');
        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token keamanan tidak valid.');
            $this->redirect(\App\Config\App::BASE_URL . '/login');
        }
        $username = $this->sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = $this->validate($_POST, ['username' => 'required', 'password' => 'required']);
        if (!empty($errors)) { Session::setFlash('error', implode('<br>', $errors)); $this->redirect('/login'); }
        $user = (new User())->authenticate($username, $password);
        if ($user) {
            Session::regenerate();
            Session::set('user_id', $user['id']);
            Session::set('user_nama', $user['nama']);
            Session::set('user_username', $user['username']);
            Session::set('user_role', $user['role']);
            Session::set('user_foto', $user['foto']);
            Session::setFlash('success', 'Selamat datang, ' . $user['nama'] . '!');
            $this->redirect(\App\Config\App::BASE_URL . '/dashboard');
        } else {
            Session::setFlash('error', 'Username atau password salah!');
            $this->redirect(\App\Config\App::BASE_URL . '/login');
        }
    }

    public function logout() {
        Session::destroy();
        $this->redirect(\App\Config\App::BASE_URL . '/login');
    }
}