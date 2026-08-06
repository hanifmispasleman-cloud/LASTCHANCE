<?php
/**
 * Pelanggan Model - Model untuk tabel pelanggan
 */

class Pelanggan extends BaseModel {
    
    protected $table = 'pelanggan';
    
    /**
     * Get pelanggan aktif
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getActive($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY nama_pelanggan ASC";
        
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
     * Get member pelanggan
     * 
     * @return array
     */
    public function getMembers() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 AND tipe_pelanggan = 'member' ORDER BY nama_pelanggan ASC";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pelanggan by nomor member
     * 
     * @param string $nomor_member
     * @return array|null
     */
    public function getByNomorMember($nomor_member) {
        return $this->getByColumn('nomor_member', $nomor_member);
    }
    
    /**
     * Search pelanggan
     * 
     * @param string $keyword
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchPelanggan($keyword, $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 AND 
                (nama_pelanggan LIKE ? OR telepon LIKE ? OR email LIKE ?) 
                ORDER BY nama_pelanggan ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
}

?>
