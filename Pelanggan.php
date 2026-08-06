<?php
namespace App\Models;

use App\Core\Model;

class Pelanggan extends Model {
    protected $table = 'pelanggan';

    public function search($keyword) {
        $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE nama LIKE :keyword OR telepon LIKE :keyword2 ORDER BY nama ASC");
        $stmt->execute(['keyword' => "%{$keyword}%", 'keyword2' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }
}