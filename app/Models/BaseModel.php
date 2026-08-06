<?php
/**
 * Base Model - Class dasar untuk semua Model
 * 
 * Menyediakan fungsi CRUD dasar dan query builder sederhana
 */

class BaseModel {
    
    protected $db;
    protected $table;
    
    /**
     * Constructor
     * 
     * @param PDO $database
     */
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all records
     * 
     * @param string $order_by
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll($order_by = 'id DESC', $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$order_by}";
        
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
     * Get record by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        
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
     * Search records
     * 
     * @param string $search_column
     * @param string $search_value
     * @param string $order_by
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function search($search_column, $search_value, $order_by = 'id DESC', $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE {$search_column} LIKE ? ORDER BY {$order_by}";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
            if ($offset !== null) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['%' . $search_value . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count all records
     * 
     * @return int
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        try {
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count dengan WHERE clause
     * 
     * @param string $column
     * @param string $value
     * @return int
     */
    public function countWhere($column, $value) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$value]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Insert record
     * 
     * @param array $data
     * @return int (last insert ID)
     */
    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Update record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $set_clause = implode(', ', array_map(function($key) {
            return "{$key} = ?";
        }, array_keys($data)));
        
        $sql = "UPDATE {$this->table} SET {$set_clause} WHERE id = ?";
        $values = array_values($data);
        $values[] = $id;
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete record
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get record by column value
     * 
     * @param string $column
     * @param mixed $value
     * @return array|null
     */
    public function getByColumn($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$value]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Execute raw query
     * 
     * @param string $sql
     * @param array $params
     * @return PDOStatement|false
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get last inserted ID
     * 
     * @return int
     */
    public function lastInsertId() {
        return (int)$this->db->lastInsertId();
    }
}

?>
