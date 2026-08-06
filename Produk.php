<?php
namespace App\Models;

use App\Core\Model;

class Produk extends Model {
    protected $table = 'produk';

    public function getAllWithKategori($filters = []) {
        $sql = "SELECT p.*, k.nama as nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id WHERE 1=1";
        $params = [];
        if (!empty($filters['keyword'])) {
            $sql .= " AND (p.nama LIKE :keyword OR p.kode LIKE :keyword2 OR p.barcode LIKE :keyword3)";
            $params['keyword'] = "%{$filters['keyword']}%";
            $params['keyword2'] = "%{$filters['keyword']}%";
            $params['keyword3'] = "%{$filters['keyword']}%";
        }
        if (!empty($filters['kategori'])) { $sql .= " AND p.id_kategori = :kategori"; $params['kategori'] = $filters['kategori']; }
        if (isset($filters['aktif']) && $filters['aktif'] !== '') { $sql .= " AND p.aktif = :aktif"; $params['aktif'] = $filters['aktif']; }
        if (!empty($filters['stok_habis'])) { $sql .= " AND p.stok <= p.stok_minimum"; }
        $sql .= " ORDER BY p.nama ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function searchLive($keyword) {
        $sql = "SELECT p.*, k.nama as nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id
                WHERE p.aktif = 1 AND (p.nama LIKE :keyword OR p.kode LIKE :keyword2 OR p.barcode LIKE :keyword3) ORDER BY p.nama ASC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['keyword' => "%{$keyword}%", 'keyword2' => "%{$keyword}%", 'keyword3' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    public function getStokHampirHabis() {
        return $this->db->query("SELECT * FROM produk WHERE stok <= stok_minimum AND aktif = 1 ORDER BY stok ASC LIMIT 10")->fetchAll();
    }

    public function updateStok($id, $qty) {
        $stmt = $this->db->prepare("UPDATE produk SET stok = stok + :qty WHERE id = :id");
        return $stmt->execute(['qty' => $qty, 'id' => $id]);
    }

    public function getProdukTerlaris($limit = 10, $startDate = null, $endDate = null) {
        $sql = "SELECT p.*, SUM(dt.qty) as total_terjual FROM detail_transaksi dt
                JOIN produk p ON dt.id_produk = p.id JOIN transaksi t ON dt.id_transaksi = t.id WHERE t.status = 'sukses'";
        $params = [];
        if ($startDate) { $sql .= " AND DATE(t.created_at) >= :start_date"; $params['start_date'] = $startDate; }
        if ($endDate) { $sql .= " AND DATE(t.created_at) <= :end_date"; $params['end_date'] = $endDate; }
        $sql .= " GROUP BY p.id ORDER BY total_terjual DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) $stmt->bindValue($key, $val);
        $stmt->bindValue('limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}