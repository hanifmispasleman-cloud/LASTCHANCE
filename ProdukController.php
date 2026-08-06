<?php
// File: app/Controllers/ProdukController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Supplier;

class ProdukController extends Controller {

    public function index() {
        $produkModel = new Produk();
        $kategoriModel = new Kategori();

        $filters = [
            'keyword'   => $_GET['keyword'] ?? '',
            'kategori'  => $_GET['kategori'] ?? '',
            'aktif'     => $_GET['aktif'] ?? '',
            'stok_habis'=> $_GET['stok_habis'] ?? ''
        ];

        $produk   = $produkModel->getAllWithKategori($filters);
        $kategori = $kategoriModel->all('nama ASC');

        $this->view('produk/index', [
            'title'     => 'Manajemen Produk - KasirKu',
            'produk'    => $produk,
            'kategori'  => $kategori,
            'filters'   => $filters,
            'csrf_token'=> Session::csrfToken()
        ]);
    }

    public function create() {
        $kategoriModel  = new Kategori();
        $supplierModel  = new Supplier();

        $this->view('produk/form', [
            'title'     => 'Tambah Produk - KasirKu',
            'produk'    => null,
            'kategori'  => $kategoriModel->all('nama ASC'),
            'supplier'  => $supplierModel->all('nama ASC'),
            'csrf_token'=> Session::csrfToken(),
            'is_edit'   => false
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/produk');
        }

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token keamanan tidak valid');
            $this->back();
        }

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, [
            'nama'        => 'required',
            'harga_beli'  => 'required|numeric',
            'harga_jual'  => 'required|numeric',
            'stok_minimum'=> 'numeric'
        ]);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            $this->back();
        }

        $produkModel = new Produk();

        // Generate kode produk jika kosong
        if (empty($data['kode'])) {
            $data['kode'] = \App\Helpers\Helper::generateKodeProduk();
        }

        // Upload gambar
        $gambar = null;
        if (!empty($_FILES['gambar']['name'])) {
            $uploadResult = $this->uploadFile($_FILES['gambar'], 'uploads/produk');
            if (isset($uploadResult['error'])) {
                Session::setFlash('error', $uploadResult['error']);
                $this->back();
            }
            $gambar = $uploadResult['filename'];
        }

        $insertData = [
            'kode'        => $data['kode'],
            'barcode'     => $data['barcode'] ?? null,
            'nama'        => $data['nama'],
            'id_kategori' => !empty($data['id_kategori']) ? $data['id_kategori'] : null,
            'id_supplier' => !empty($data['id_supplier']) ? $data['id_supplier'] : null,
            'harga_beli'  => $data['harga_beli'],
            'harga_jual'  => $data['harga_jual'],
            'stok'        => $data['stok'] ?? 0,
            'stok_minimum'=> $data['stok_minimum'] ?? 5,
            'gambar'      => $gambar,
            'aktif'       => $data['aktif'] ?? 1
        ];

        $produkModel->create($insertData);
        Session::setFlash('success', 'Produk berhasil ditambahkan');
        $this->redirect(\App\Config\App::BASE_URL . '/produk');
    }

    public function edit($id) {
        $produkModel    = new Produk();
        $kategoriModel  = new Kategori();
        $supplierModel  = new Supplier();

        $produk = $produkModel->find($id);
        if (!$produk) {
            Session::setFlash('error', 'Produk tidak ditemukan');
            $this->redirect(\App\Config\App::BASE_URL . '/produk');
        }

        $this->view('produk/form', [
            'title'     => 'Edit Produk - KasirKu',
            'produk'    => $produk,
            'kategori'  => $kategoriModel->all('nama ASC'),
            'supplier'  => $supplierModel->all('nama ASC'),
            'csrf_token'=> Session::csrfToken(),
            'is_edit'   => true
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/produk');
        }

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Token keamanan tidak valid');
            $this->back();
        }

        $produkModel = new Produk();
        $produk = $produkModel->find($id);
        if (!$produk) {
            Session::setFlash('error', 'Produk tidak ditemukan');
            $this->redirect(\App\Config\App::BASE_URL . '/produk');
        }

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data, [
            'nama'        => 'required',
            'harga_beli'  => 'required|numeric',
            'harga_jual'  => 'required|numeric'
        ]);

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            $this->back();
        }

        $updateData = [
            'kode'        => $data['kode'],
            'barcode'     => $data['barcode'] ?? null,
            'nama'        => $data['nama'],
            'id_kategori' => !empty($data['id_kategori']) ? $data['id_kategori'] : null,
            'id_supplier' => !empty($data['id_supplier']) ? $data['id_supplier'] : null,
            'harga_beli'  => $data['harga_beli'],
            'harga_jual'  => $data['harga_jual'],
            'stok'        => $data['stok'] ?? 0,
            'stok_minimum'=> $data['stok_minimum'] ?? 5,
            'aktif'       => $data['aktif'] ?? 1
        ];

        // Jika ada gambar baru
        if (!empty($_FILES['gambar']['name'])) {
            $uploadResult = $this->uploadFile($_FILES['gambar'], 'uploads/produk');
            if (isset($uploadResult['error'])) {
                Session::setFlash('error', $uploadResult['error']);
                $this->back();
            }
            // Hapus gambar lama
            if ($produk['gambar'] && file_exists(__DIR__ . '/../../public/uploads/produk/' . $produk['gambar'])) {
                unlink(__DIR__ . '/../../public/uploads/produk/' . $produk['gambar']);
            }
            $updateData['gambar'] = $uploadResult['filename'];
        }

        $produkModel->update($id, $updateData);
        Session::setFlash('success', 'Produk berhasil diperbarui');
        $this->redirect(\App\Config\App::BASE_URL . '/produk');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        if (!Session::validateCsrf($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token tidak valid'], 403);
        }

        $produkModel = new Produk();
        $produk = $produkModel->find($id);
        if (!$produk) {
            $this->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        // Hapus gambar
        if ($produk['gambar'] && file_exists(__DIR__ . '/../../public/uploads/produk/' . $produk['gambar'])) {
            unlink(__DIR__ . '/../../public/uploads/produk/' . $produk['gambar']);
        }

        $produkModel->delete($id);
        $this->json(['success' => true, 'message' => 'Produk berhasil dihapus']);
    }
}