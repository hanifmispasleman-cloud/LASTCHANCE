<?php
/**
 * Produk Model - Model untuk tabel produk
 */

class Produk extends BaseModel {
    
    protected $table = 'produk';
    
    /**
     * Get produk by kode
     * 
     * @param string $kode_produk
     * @return array|null
     */
    public function getByKode($kode_produk) {
        return $this->getByColumn('kode_produk', $kode_produk);
    }
    
    /**
     * Get produk by barcode
     * 
     * @param string $barcode
     * @return array|null
     */
    public function getByBarcode($barcode) {
        return $this->getByColumn('barcode', $barcode);
    }
    
    /**
     * Get produk by kategori
     * 
     * @param int $kategori_id
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByKategori($kategori_id, $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE kategori_id = ? AND status = 1 ORDER BY nama_produk ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$kategori_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get produk hampir habis (stok <= stok_minimum)
     * 
     * @param int $limit
     * @return array
     */
    public function getLowStock($limit = 10) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 1 AND stok <= stok_minimum 
                ORDER BY stok ASC LIMIT {$limit}";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get produk aktif
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getActive($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY nama_produk ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search produk dengan detail
     * 
     * @param string $keyword
     * @param int $kategori_id
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchWithCategory($keyword, $kategori_id = null, $limit = null, $offset = null) {
        $sql = "SELECT p.*, k.nama_kategori FROM {$this->table} p 
                LEFT JOIN kategori k ON p.kategori_id = k.id 
                WHERE p.status = 1 AND 
                (p.nama_produk LIKE ? OR p.kode_produk LIKE ? OR p.barcode LIKE ?)";
        
        $params = ['%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%'];
        
        if ($kategori_id) {
            $sql .= " AND p.kategori_id = ?";
            $params[] = $kategori_id;
        }
        
        $sql .= " ORDER BY p.nama_produk ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update stok produk
     * 
     * @param int $produk_id
     * @param int $qty_change (positif untuk tambah, negatif untuk kurangi)
     * @return bool
     */
    public function updateStok($produk_id, $qty_change) {
        $produk = $this->getById($produk_id);
        if (!$produk) return false;
        
        $new_stok = $produk['stok'] + $qty_change;
        if ($new_stok < 0) $new_stok = 0;
        
        return $this->update($produk_id, ['stok' => $new_stok]);
    }
    
    /**
     * Get produk dengan informasi stok lengkap
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithDetails($id) {
        $sql = "SELECT p.*, k.nama_kategori,
                CASE WHEN p.stok <= p.stok_minimum THEN 'danger'
                     WHEN p.stok <= (p.stok_minimum * 1.5) THEN 'warning'
                     ELSE 'success' END as stok_status
                FROM {$this->table} p
                LEFT JOIN kategori k ON p.kategori_id = k.id
                WHERE p.id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return null;
        }
    }
}

?>
