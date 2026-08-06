<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Supplier;

class SupplierController extends Controller {

    public function index() {
        $supplierModel = new Supplier();
        $keyword = $_GET['keyword'] ?? '';
        $supplier = $keyword ? $supplierModel->search($keyword) : $supplierModel->all('nama ASC');

        $this->view('supplier/index', [
            'title' => 'Supplier - KasirKu',
            'supplier' => $supplier,
            'keyword' => $keyword,
            'csrf_token' => Session::csrfToken()
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/supplier');

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

        $supplierModel = new Supplier();
        $supplierModel->create([
            'nama' => $data['nama'],
            'alamat' => $data['alamat'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'email' => $data['email'] ?? null
        ]);

        Session::setFlash('success', 'Supplier berhasil ditambahkan');
        $this->back();
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/supplier');

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

        $supplierModel = new Supplier();
        $supplierModel->update($id, [
            'nama' => $data['nama'],
            'alamat' => $data['alamat'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'email' => $data['email'] ?? null
        ]);

        Session::setFlash('success', 'Supplier berhasil diperbarui');
        $this->back();
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token tidak valid'], 403);
        }

        $supplierModel = new Supplier();
        $supplierModel->delete($id);
        $this->json(['success' => true, 'message' => 'Supplier berhasil dihapus']);
    }
}