<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Pelanggan;

class PelangganController extends Controller {

    public function index() {
        $pelangganModel = new Pelanggan();
        $keyword = $_GET['keyword'] ?? '';
        $pelanggan = $keyword ? $pelangganModel->search($keyword) : $pelangganModel->all('nama ASC');

        $this->view('pelanggan/index', [
            'title' => 'Pelanggan - KasirKu',
            'pelanggan' => $pelanggan,
            'keyword' => $keyword,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/pelanggan');

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

        $pelangganModel = new Pelanggan();
        $pelangganModel->create([
            'nama' => $data['nama'],
            'telepon' => $data['telepon'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'email' => $data['email'] ?? null
        ]);

        Session::setFlash('success', 'Pelanggan berhasil ditambahkan');
        $this->back();
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/pelanggan');

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

        $pelangganModel = new Pelanggan();
        $pelangganModel->update($id, [
            'nama' => $data['nama'],
            'telepon' => $data['telepon'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'email' => $data['email'] ?? null
        ]);

        Session::setFlash('success', 'Pelanggan berhasil diperbarui');
        $this->back();
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token tidak valid'], 403);
        }

        $pelangganModel = new Pelanggan();
        $pelangganModel->delete($id);
        $this->json(['success' => true, 'message' => 'Pelanggan berhasil dihapus']);
    }
}