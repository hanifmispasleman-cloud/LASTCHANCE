<?php
/**
 * Transaksi Model - Model untuk tabel transaksi
 */

class Transaksi extends BaseModel {
    
    protected $table = 'transaksi';
    
    /**
     * Get transaksi by nomor
     * 
     * @param string $nomor_transaksi
     * @return array|null
     */
    public function getByNomor($nomor_transaksi) {
        return $this->getByColumn('nomor_transaksi', $nomor_transaksi);
    }
    
    /**
     * Get transaksi hari ini
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTodayTransactions($limit = null, $offset = null) {
        $today = date('Y-m-d');
        $sql = "SELECT t.*, u.nama_lengkap, p.nama_pelanggan 
                FROM {$this->table} t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
                WHERE DATE(t.tanggal_transaksi) = ?
                ORDER BY t.tanggal_transaksi DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$today]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total penjualan hari ini
     * 
     * @return float
     */
    public function getTotalTodaysSales() {
        $today = date('Y-m-d');
        $sql = "SELECT COALESCE(SUM(total), 0) as total FROM {$this->table} 
                WHERE DATE(tanggal_transaksi) = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)$result['total'];
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total transaksi hari ini
     * 
     * @return int
     */
    public function getTotalTodaysTransactions() {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE DATE(tanggal_transaksi) = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get penjualan per hari (7 hari terakhir)
     * 
     * @return array
     */
    public function getSalesLast7Days() {
        $sql = "SELECT DATE(tanggal_transaksi) as tanggal, 
                COUNT(*) as jumlah_transaksi,
                SUM(total) as total_penjualan
                FROM {$this->table}
                WHERE tanggal_transaksi >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(tanggal_transaksi)
                ORDER BY tanggal ASC";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get transaksi terbaru
     * 
     * @param int $limit
     * @return array
     */
    public function getLatestTransactions($limit = 10) {
        $sql = "SELECT t.*, u.nama_lengkap, p.nama_pelanggan
                FROM {$this->table} t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
                ORDER BY t.tanggal_transaksi DESC
                LIMIT {$limit}";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get transaksi dengan detail lengkap
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithDetails($id) {
        $sql = "SELECT t.*, u.nama_lengkap, p.nama_pelanggan
                FROM {$this->table} t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
                WHERE t.id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get penjualan dalam range tanggal
     * 
     * @param string $start_date (format: Y-m-d)
     * @param string $end_date (format: Y-m-d)
     * @return float
     */
    public function getSalesBetween($start_date, $end_date) {
        $sql = "SELECT COALESCE(SUM(total), 0) as total FROM {$this->table}
                WHERE DATE(tanggal_transaksi) BETWEEN ? AND ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$start_date, $end_date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)$result['total'];
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }
}

?>
