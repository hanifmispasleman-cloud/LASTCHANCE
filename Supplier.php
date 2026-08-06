<?php
namespace App\Models;

use App\Core\Model;

class Supplier extends Model {
    protected $table = 'supplier';

    public function search($keyword) {
        $stmt = $this->db->prepare("SELECT * FROM supplier WHERE nama LIKE :keyword OR telepon LIKE :keyword2 ORDER BY nama ASC");
        $stmt->execute(['keyword' => "%{$keyword}%", 'keyword2' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }
}