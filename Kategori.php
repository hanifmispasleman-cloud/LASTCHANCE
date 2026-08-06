<?php
namespace App\Models;

use App\Core\Model;

class Kategori extends Model {
    protected $table = 'kategori';

    public function search($keyword) {
        $stmt = $this->db->prepare("SELECT * FROM kategori WHERE nama LIKE :keyword ORDER BY nama ASC");
        $stmt->execute(['keyword' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    public function getWithProductCount() {
        $sql = "SELECT k.*, COUNT(p.id) as total_produk FROM kategori k
                LEFT JOIN produk p ON k.id = p.id_kategori GROUP BY k.id ORDER BY k.nama ASC";
        return $this->db->query($sql)->fetchAll();
    }
}