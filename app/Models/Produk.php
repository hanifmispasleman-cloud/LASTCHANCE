<?php
/**
 * Produk Model - Model untuk tabel products
 */

class Produk {
    
    private $db;
    private $table = 'products';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get product by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Get product by SKU
     */
    public function getBySku($sku) {
        $query = "SELECT * FROM {$this->table} WHERE sku = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $sku);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Get all products
     */
    public function getAll($limit = 10, $offset = 0, $filters = []) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        $types = '';
        
        if (isset($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (isset($filters['category_id'])) {
            $query .= " AND category_id = ?";
            $params[] = $filters['category_id'];
            $types .= 'i';
        }
        
        if (isset($filters['search'])) {
            $query .= " AND (name LIKE ? OR sku LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $types .= 'ss';
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Create product
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (name, sku, category_id, price, cost, stock, min_stock, description, status, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssiiddiis', 
            $data['name'],
            $data['sku'],
            $data['category_id'],
            $data['price'],
            $data['cost'],
            $data['stock'],
            $data['min_stock'],
            $data['description'],
            $data['status']
        );
        
        return $stmt->execute();
    }
    
    /**
     * Update product
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        $types = '';
        
        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $params[] = $data['name'];
            $types .= 's';
        }
        
        if (isset($data['price'])) {
            $fields[] = 'price = ?';
            $params[] = $data['price'];
            $types .= 'd';
        }
        
        if (isset($data['stock'])) {
            $fields[] = 'stock = ?';
            $params[] = $data['stock'];
            $types .= 'i';
        }
        
        if (isset($data['status'])) {
            $fields[] = 'status = ?';
            $params[] = $data['status'];
            $types .= 's';
        }
        
        $fields[] = 'updated_at = NOW()';
        $params[] = $id;
        $types .= 'i';
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }
    
    /**
     * Delete product
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    
    /**
     * Get low stock products
     */
    public function getLowStock() {
        $query = "SELECT * FROM {$this->table} WHERE stock <= min_stock AND status = 'active' ORDER BY stock ASC";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Count total products
     */
    public function count($filters = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        $types = '';
        
        if (isset($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (isset($filters['category_id'])) {
            $query .= " AND category_id = ?";
            $params[] = $filters['category_id'];
            $types .= 'i';
        }
        
        $stmt = $this->db->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc()['total'];
    }
}

?>
