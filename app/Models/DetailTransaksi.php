<?php
/**
 * DetailTransaksi Model - Model untuk tabel detail_transaksi
 */

class DetailTransaksi extends BaseModel {
    
    protected $table = 'detail_transaksi';
    
    /**
     * Get detail transaksi by transaksi ID
     * 
     * @param int $transaksi_id
     * @return array
     */
    public function getByTransaksiId($transaksi_id) {
        $sql = "SELECT dt.*, p.nama_produk, p.kode_produk
                FROM {$this->table} dt
                LEFT JOIN produk p ON dt.produk_id = p.id
                WHERE dt.transaksi_id = ?
                ORDER BY dt.id ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$transaksi_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get produk terlaris (7 hari)
     * 
     * @param int $limit
     * @return array
     */
    public function getTopProducts($limit = 10) {
        $sql = "SELECT p.id, p.nama_produk, p.kode_produk, p.gambar,
                SUM(dt.qty) as total_qty,
                SUM(dt.subtotal) as total_penjualan,
                COUNT(DISTINCT dt.transaksi_id) as jumlah_transaksi
                FROM {$this->table} dt
                LEFT JOIN produk p ON dt.produk_id = p.id
                LEFT JOIN transaksi t ON dt.transaksi_id = t.id
                WHERE t.tanggal_transaksi >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY p.id
                ORDER BY total_qty DESC
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
     * Insert detail transaksi
     * 
     * @param int $transaksi_id
     * @param int $produk_id
     * @param int $qty
     * @param float $harga_satuan
     * @param float $subtotal
     * @param float $diskon_item
     * @return int
     */
    public function addItem($transaksi_id, $produk_id, $qty, $harga_satuan, $subtotal, $diskon_item = 0) {
        $data = [
            'transaksi_id' => $transaksi_id,
            'produk_id' => $produk_id,
            'qty' => $qty,
            'harga_satuan' => $harga_satuan,
            'subtotal' => $subtotal,
            'diskon_item' => $diskon_item
        ];
        
        return $this->insert($data);
    }
}

?>
