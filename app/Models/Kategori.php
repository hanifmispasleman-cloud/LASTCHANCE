<?php
/**
 * Kategori Model - Model untuk tabel kategori
 */

class Kategori extends BaseModel {
    
    protected $table = 'kategori';
    
    /**
     * Get kategori aktif
     * 
     * @return array
     */
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY nama_kategori ASC";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get kategori dengan jumlah produk
     * 
     * @return array
     */
    public function getWithProductCount() {
        $sql = "SELECT k.*, COUNT(p.id) as jumlah_produk
                FROM {$this->table} k
                LEFT JOIN produk p ON k.id = p.kategori_id AND p.status = 1
                WHERE k.status = 1
                GROUP BY k.id
                ORDER BY k.nama_kategori ASC";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
}

?>
