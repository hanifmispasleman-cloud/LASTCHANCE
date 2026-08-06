<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Pengaturan;
use App\Models\User;

class PengaturanController extends Controller {

    public function index() {
        $pengaturanModel = new Pengaturan();
        $pengaturan = $pengaturanModel->getAllAsKeyValue();
        $users = (new User())->all('nama ASC');

        $this->view('pengaturan/index', [
            'title' => 'Pengaturan - KasirKu',
            'pengaturan' => $pengaturan,
            'users' => $users,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/pengaturan');

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token keamanan tidak valid');
            $this->back();
        }

        $pengaturanModel = new Pengaturan();
        $data = $this->sanitize($_POST);

        $keys = ['nama_toko', 'alamat', 'telepon', 'pajak', 'mata_uang', 'footer_struk'];
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $pengaturanModel->updateByKunci($key, $data[$key]);
            }
        }

        // Upload logo
        if (!empty($_FILES['logo']['name'])) {
            $uploadResult = $this->uploadFile($_FILES['logo'], 'uploads');
            if (isset($uploadResult['success'])) {
                $pengaturanModel->updateByKunci('logo', $uploadResult['filename']);
            }
        }

        Session::setFlash('success', 'Pengaturan berhasil disimpan');
        $this->back();
    }

    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/pengaturan');

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token keamanan tidak valid');
            $this->back();
        }

        $data = $this->sanitize($_POST);
        $errors = $this->validate($data, [
            'nama' => 'required',
            'username' => 'required|min:3',
            'password' => 'required|min:6'
        ]);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            $this->back();
        }

        $userModel = new User();
        $existing = $userModel->findOneBy('username', $data['username']);
        if ($existing) {
            Session::setFlash('error', 'Username sudah digunakan');
            $this->back();
        }

        $userModel->create([
            'nama' => $data['nama'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'kasir'
        ]);

        Session::setFlash('success', 'User berhasil ditambahkan');
        $this->back();
    }

    public function deleteUser($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token tidak valid'], 403);
        }

        // Tidak bisa hapus diri sendiri
        if ($id == Session::get('user_id')) {
            $this->json(['error' => 'Tidak dapat menghapus akun sendiri'], 400);
        }

        $userModel = new User();
        $userModel->delete($id);
        $this->json(['success' => true, 'message' => 'User berhasil dihapus']);
    }
}