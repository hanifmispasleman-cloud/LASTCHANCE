<?php
/**
 * ProdukController - Controller untuk produk
 */

class ProdukController {
    
    private $produkModel;
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
        $this->produkModel = new Produk($database);
    }
    
    /**
     * List all products
     */
    public function index() {
        AuthMiddleware::checkAuth();
        AuthMiddleware::checkSessionTimeout();
        SecurityMiddleware::setSecurityHeaders();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Build filters
        $filters = [];
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $filters['search'] = sanitize($_GET['search']);
        }
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $filters['category_id'] = (int)$_GET['category'];
        }
        
        // Get products
        $produk = $this->produkModel->getAll($limit, $offset, $filters);
        $total = $this->produkModel->count($filters);
        
        // Get categories
        $categories = $this->getCategories();
        
        $page_title = 'Produk - KasirKu';
        
        if (isset($_SESSION['flash'])) {
            $success = $_SESSION['flash']['message'];
            unset($_SESSION['flash']);
        }
        
        include APP_PATH . 'Views/Produk/index.php';
    }
    
    /**
     * Show create/edit form
     */
    public function form() {
        AuthMiddleware::checkAuth();
        AuthMiddleware::checkSessionTimeout();
        SecurityMiddleware::setSecurityHeaders();
        
        $produk = [];
        $categories = $this->getCategories();
        $page_title = 'Form Produk - KasirKu';
        
        // If edit
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $produk = $this->produkModel->getById($id);
            
            if (!$produk) {
                set_flash('Produk tidak ditemukan', 'danger');
                redirect('produk');
            }
        }
        
        include APP_PATH . 'Views/Produk/form.php';
    }
    
    /**
     * Create product
     */
    public function store() {
        AuthMiddleware::checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('produk/create');
        }
        
        SecurityMiddleware::validateCSRFToken();
        
        // Get and sanitize input
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'sku' => sanitize($_POST['sku'] ?? ''),
            'category_id' => (int)$_POST['category'] ?? 0,
            'price' => (float)$_POST['price'] ?? 0,
            'cost' => (float)$_POST['cost'] ?? 0,
            'stock' => (int)$_POST['stock'] ?? 0,
            'min_stock' => (int)$_POST['min_stock'] ?? 0,
            'description' => sanitize($_POST['description'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        // Validate
        $errors = [];
        if (empty($data['name'])) $errors[] = 'Nama produk harus diisi';
        if (empty($data['sku'])) $errors[] = 'SKU harus diisi';
        if ($data['price'] <= 0) $errors[] = 'Harga harus lebih dari 0';
        if ($data['category_id'] <= 0) $errors[] = 'Kategori harus dipilih';
        
        if (count($errors) > 0) {
            $_SESSION['errors'] = $errors;
            redirect('produk/create');
        }
        
        // Create product
        if ($this->produkModel->create($data)) {
            set_flash('Produk berhasil ditambahkan', 'success');
            log_activity('CREATE_PRODUCT', 'Product ' . $data['name'] . ' created');
            redirect('produk');
        } else {
            set_flash('Gagal menambahkan produk', 'danger');
            redirect('produk/create');
        }
    }
    
    /**
     * Update product
     */
    public function update() {
        AuthMiddleware::checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('produk');
        }
        
        SecurityMiddleware::validateCSRFToken();
        
        $id = (int)$_POST['id'] ?? 0;
        
        if ($id <= 0) {
            set_flash('Produk tidak ditemukan', 'danger');
            redirect('produk');
        }
        
        // Get and sanitize input
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'price' => (float)$_POST['price'] ?? 0,
            'stock' => (int)$_POST['stock'] ?? 0,
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        // Validate
        if (empty($data['name'])) {
            set_flash('Nama produk harus diisi', 'danger');
            redirect('produk/' . $id . '/edit');
        }
        
        // Update product
        if ($this->produkModel->update($id, $data)) {
            set_flash('Produk berhasil diperbarui', 'success');
            log_activity('UPDATE_PRODUCT', 'Product ID ' . $id . ' updated');
            redirect('produk');
        } else {
            set_flash('Gagal memperbarui produk', 'danger');
            redirect('produk/' . $id . '/edit');
        }
    }
    
    /**
     * Delete product
     */
    public function delete() {
        AuthMiddleware::checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('produk');
        }
        
        SecurityMiddleware::validateCSRFToken();
        
        $id = (int)$_POST['id'] ?? 0;
        
        if ($id <= 0) {
            set_flash('Produk tidak ditemukan', 'danger');
            redirect('produk');
        }
        
        if ($this->produkModel->delete($id)) {
            set_flash('Produk berhasil dihapus', 'success');
            log_activity('DELETE_PRODUCT', 'Product ID ' . $id . ' deleted');
        } else {
            set_flash('Gagal menghapus produk', 'danger');
        }
        
        redirect('produk');
    }
    
    /**
     * Get all categories
     */
    private function getCategories() {
        $query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

?>
