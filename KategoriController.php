<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Kategori;

class KategoriController extends Controller {

    public function index() {
        $kategoriModel = new Kategori();
        $kategori = $kategoriModel->getWithProductCount();

        $this->view('kategori/index', [
            'title' => 'Kategori - KasirKu',
            'kategori' => $kategori,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/kategori');

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token keamanan tidak valid');
            $this->back();
        }

        $data = $this->sanitize($_POST);
        $errors = $this->validate($data, ['nama' => 'required']);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            $this->back();
        }

        $kategoriModel = new Kategori();
        $kategoriModel->create([
            'nama' => $data['nama'],
            'deskripsi' => $data['deskripsi'] ?? null
        ]);

        Session::setFlash('success', 'Kategori berhasil ditambahkan');
        $this->back();
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/kategori');

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token keamanan tidak valid');
            $this->back();
        }

        $data = $this->sanitize($_POST);
        $errors = $this->validate($data, ['nama' => 'required']);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            $this->back();
        }

        $kategoriModel = new Kategori();
        $kategoriModel->update($id, [
            'nama' => $data['nama'],
            'deskripsi' => $data['deskripsi'] ?? null
        ]);

        Session::setFlash('success', 'Kategori berhasil diperbarui');
        $this->back();
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token tidak valid'], 403);
        }

        $kategoriModel = new Kategori();
        $kategoriModel->delete($id);
        $this->json(['success' => true, 'message' => 'Kategori berhasil dihapus']);
    }
}