<?php
/**
 * Produk Controller - Controller untuk Manajemen Produk
 */

require_once APP_DIR . 'Models/Produk.php';
require_once APP_DIR . 'Models/Kategori.php';

class ProdukController {
    
    private $db;
    private $produkModel;
    private $kategoriModel;
    
    public function __construct($database) {
        $this->db = $database;
        $this->produkModel = new Produk($database);
        $this->kategoriModel = new Kategori($database);
    }
    
    /**
     * List semua produk
     */
    public function index() {
        AuthMiddleware::requireLogin();
        
        $page = getCurrentPage();
        $per_page = ITEMS_PER_PAGE;
        $offset = getPageOffset($page, $per_page);
        
        // Get search parameter
        $search = sanitize($_GET['search'] ?? '');
        $kategori_id = (int)($_GET['kategori_id'] ?? 0);
        
        // Get data
        if (!empty($search)) {
            $produk_list = $this->produkModel->searchWithCategory($search, $kategori_id ?: null, $per_page, $offset);
            $total = count($produk_list); // Simplified count
        } else {
            $produk_list = $this->produkModel->getActive($per_page, $offset);
            $total = $this->produkModel->count();
        }
        
        $kategori_list = $this->kategoriModel->getWithProductCount();
        $pagination = generatePagination($total, $per_page, $page, 'produk');
        $title = 'Manajemen Produk - KasirKu';
        
        require VIEW_DIR . 'produk/index.php';
    }
    
    /**
     * Show create produk form
     */
    public function create() {
        AuthMiddleware::requireAnyRole([ROLE_ADMIN, ROLE_OWNER]);
        
        $kategori_list = $this->kategoriModel->getActive();
        $title = 'Tambah Produk - KasirKu';
        
        require VIEW_DIR . 'produk/form.php';
    }
    
    /**
     * Store produk
     */
    public function store() {
        AuthMiddleware::requireAnyRole([ROLE_ADMIN, ROLE_OWNER]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request');
        }
        
        if (!CsrfMiddleware::verifyToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            jsonResponse(false, 'CSRF Token tidak valid', null, 403);
        }
        
        // Get input
        $nama_produk = sanitize($_POST['nama_produk'] ?? '');
        $kategori_id = (int)($_POST['kategori_id'] ?? 0);
        $harga_beli = (float)($_POST['harga_beli'] ?? 0);
        $harga_jual = (float)($_POST['harga_jual'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);
        $stok_minimum = (int)($_POST['stok_minimum'] ?? 5);
        $satuan = sanitize($_POST['satuan'] ?? 'pcs');
        $barcode = sanitize($_POST['barcode'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        
        // Validasi
        ValidationHelper::clearErrors();
        ValidationHelper::required($nama_produk, 'Nama produk');
        ValidationHelper::minValue($kategori_id, 1, 'Kategori');
        ValidationHelper::minValue($harga_beli, 0, 'Harga beli');
        ValidationHelper::minValue($harga_jual, 0, 'Harga jual');
        
        if ($harga_jual < $harga_beli) {
            ValidationHelper::addError('Harga jual tidak boleh lebih kecil dari harga beli');
        }
        
        if (!empty($barcode) && !ValidationHelper::unique($this->db, $barcode, 'produk', 'barcode', 'Barcode')) {
            ValidationHelper::addError('Barcode sudah digunakan');
        }
        
        if (ValidationHelper::hasErrors()) {
            jsonResponse(false, ValidationHelper::getFirstError());
        }
        
        // Generate kode produk
        $kode_produk = generateProductCode();
        
        // Handle image upload
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = FileHelper::upload($_FILES['gambar'], UPLOAD_PRODUCT_DIR, ALLOWED_IMAGE_TYPES);
            if (!$upload_result['success']) {
                jsonResponse(false, $upload_result['message']);
            }
            $gambar = $upload_result['path'];
        }
        
        // Insert produk
        $data = [
            'kode_produk' => $kode_produk,
            'nama_produk' => $nama_produk,
            'kategori_id' => $kategori_id,
            'deskripsi' => $deskripsi,
            'harga_beli' => $harga_beli,
            'harga_jual' => $harga_jual,
            'stok' => $stok,
            'stok_minimum' => $stok_minimum,
            'satuan' => $satuan,
            'barcode' => $barcode,
            'gambar' => $gambar,
            'status' => 1
        ];
        
        $produk_id = $this->produkModel->insert($data);
        
        if ($produk_id) {
            logActivity($this->db, 'CREATE_PRODUK', 'produk', $produk_id, null, $data);
            jsonResponse(true, 'Produk berhasil ditambahkan', ['id' => $produk_id]);
        } else {
            jsonResponse(false, 'Gagal menambahkan produk');
        }
    }
    
    /**
     * Show edit produk form
     */
    public function edit($id) {
        AuthMiddleware::requireAnyRole([ROLE_ADMIN, ROLE_OWNER]);
        
        $produk = $this->produkModel->getWithDetails($id);
        if (!$produk) {
            redirect('produk');
        }
        
        $kategori_list = $this->kategoriModel->getActive();
        $title = 'Edit Produk - KasirKu';
        
        require VIEW_DIR . 'produk/form.php';
    }
    
    /**
     * Update produk
     */
    public function update($id) {
        AuthMiddleware::requireAnyRole([ROLE_ADMIN, ROLE_OWNER]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request');
        }
        
        if (!CsrfMiddleware::verifyToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            jsonResponse(false, 'CSRF Token tidak valid', null, 403);
        }
        
        $produk = $this->produkModel->getById($id);
        if (!$produk) {
            jsonResponse(false, 'Produk tidak ditemukan', null, 404);
        }
        
        // Get input
        $nama_produk = sanitize($_POST['nama_produk'] ?? '');
        $kategori_id = (int)($_POST['kategori_id'] ?? 0);
        $harga_beli = (float)($_POST['harga_beli'] ?? 0);
        $harga_jual = (float)($_POST['harga_jual'] ?? 0);
        $stok_minimum = (int)($_POST['stok_minimum'] ?? 5);
        $satuan = sanitize($_POST['satuan'] ?? 'pcs');
        $barcode = sanitize($_POST['barcode'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        
        // Validasi
        ValidationHelper::clearErrors();
        ValidationHelper::required($nama_produk, 'Nama produk');
        ValidationHelper::minValue($kategori_id, 1, 'Kategori');
        ValidationHelper::minValue($harga_beli, 0, 'Harga beli');
        ValidationHelper::minValue($harga_jual, 0, 'Harga jual');
        
        if ($harga_jual < $harga_beli) {
            ValidationHelper::addError('Harga jual tidak boleh lebih kecil dari harga beli');
        }
        
        if (ValidationHelper::hasErrors()) {
            jsonResponse(false, ValidationHelper::getFirstError());
        }
        
        // Handle image upload
        $gambar = $produk['gambar'];
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = FileHelper::upload($_FILES['gambar'], UPLOAD_PRODUCT_DIR, ALLOWED_IMAGE_TYPES);
            if (!$upload_result['success']) {
                jsonResponse(false, $upload_result['message']);
            }
            // Delete old image
            if ($produk['gambar']) {
                FileHelper::delete($produk['gambar']);
            }
            $gambar = $upload_result['path'];
        }
        
        // Update produk
        $data = [
            'nama_produk' => $nama_produk,
            'kategori_id' => $kategori_id,
            'deskripsi' => $deskripsi,
            'harga_beli' => $harga_beli,
            'harga_jual' => $harga_jual,
            'stok_minimum' => $stok_minimum,
            'satuan' => $satuan,
            'barcode' => $barcode,
            'gambar' => $gambar
        ];
        
        if ($this->produkModel->update($id, $data)) {
            logActivity($this->db, 'UPDATE_PRODUK', 'produk', $id, $produk, $data);
            jsonResponse(true, 'Produk berhasil diupdate');
        } else {
            jsonResponse(false, 'Gagal update produk');
        }
    }
    
    /**
     * Delete produk
     */
    public function delete($id) {
        AuthMiddleware::requireAnyRole([ROLE_ADMIN, ROLE_OWNER]);
        
        $produk = $this->produkModel->getById($id);
        if (!$produk) {
            jsonResponse(false, 'Produk tidak ditemukan', null, 404);
        }
        
        // Delete image
        if ($produk['gambar']) {
            FileHelper::delete($produk['gambar']);
        }
        
        // Soft delete (set status to 0)
        if ($this->produkModel->update($id, ['status' => 0])) {
            logActivity($this->db, 'DELETE_PRODUK', 'produk', $id, $produk);
            jsonResponse(true, 'Produk berhasil dihapus');
        } else {
            jsonResponse(false, 'Gagal menghapus produk');
        }
    }
    
    /**
     * Search produk (untuk POS)
     */
    public function search() {
        AuthMiddleware::requireLogin();
        header('Content-Type: application/json');
        
        $keyword = sanitize($_GET['q'] ?? '');
        $limit = 20;
        
        if (strlen($keyword) < 2) {
            jsonResponse(false, 'Keyword minimal 2 karakter');
        }
        
        $produk_list = $this->produkModel->searchWithCategory($keyword, null, $limit);
        
        jsonResponse(true, 'Data produk', $produk_list);
    }
}

?>
