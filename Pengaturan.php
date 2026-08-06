<?php
namespace App\Models;

use App\Core\Model;

class Pengaturan extends Model {
    protected $table = 'pengaturan';

    public function getAllAsKeyValue() {
        $results = $this->all('kunci ASC');
        $data = [];
        foreach ($results as $row) $data[$row['kunci']] = $row['nilai'];
        return $data;
    }

    public function updateByKunci($kunci, $nilai) {
        $stmt = $this->db->prepare("UPDATE pengaturan SET nilai = :nilai WHERE kunci = :kunci");
        return $stmt->execute(['nilai' => $nilai, 'kunci' => $kunci]);
    }
}