<?php
/**
 * User Model - Model untuk tabel users
 */

class User {
    
    private $db;
    private $table = 'users';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} (name, email, password, phone, role, status, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssssss', 
            $data['name'],
            $data['email'],
            $data['password'],
            $data['phone'],
            $data['role'],
            $data['status']
        );
        
        return $stmt->execute();
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        $types = '';
        
        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $params[] = $data['name'];
            $types .= 's';
        }
        
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $params[] = $data['email'];
            $types .= 's';
        }
        
        if (isset($data['phone'])) {
            $fields[] = 'phone = ?';
            $params[] = $data['phone'];
            $types .= 's';
        }
        
        if (isset($data['password'])) {
            $fields[] = 'password = ?';
            $params[] = $data['password'];
            $types .= 's';
        }
        
        if (isset($data['status'])) {
            $fields[] = 'status = ?';
            $params[] = $data['status'];
            $types .= 's';
        }
        
        $fields[] = 'updated_at = NOW()';
        $params[] = $id;
        $types .= 'i';
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    
    /**
     * Get all users with pagination
     */
    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Count total users
     */
    public function count() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->query($query);
        return $result->fetch_assoc()['total'];
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($plain_password, $hashed_password) {
        return password_verify($plain_password, $hashed_password);
    }
    
    /**
     * Hash password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

?>
