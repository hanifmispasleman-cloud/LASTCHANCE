<?php
/**
 * User Model - Model untuk tabel users
 */

class User extends BaseModel {
    
    protected $table = 'users';
    
    /**
     * Get user by username
     * 
     * @param string $username
     * @return array|null
     */
    public function getByUsername($username) {
        return $this->getByColumn('username', $username);
    }
    
    /**
     * Get user by email
     * 
     * @param string $email
     * @return array|null
     */
    public function getByEmail($email) {
        return $this->getByColumn('email', $email);
    }
    
    /**
     * Login user
     * 
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function login($username, $password) {
        $user = $this->getByUsername($username);
        
        if (!$user) {
            return null;
        }
        
        if (!password_verify($password, $user['password'])) {
            return null;
        }
        
        if (!$user['status']) {
            return null; // User not active
        }
        
        // Update last login
        $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        
        return $user;
    }
    
    /**
     * Create user
     * 
     * @param array $data
     * @return int
     */
    public function create($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        return $this->insert($data);
    }
    
    /**
     * Update user password
     * 
     * @param int $user_id
     * @param string $new_password
     * @return bool
     */
    public function updatePassword($user_id, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        return $this->update($user_id, ['password' => $hashed_password]);
    }
    
    /**
     * Verify user password
     * 
     * @param int $user_id
     * @param string $password
     * @return bool
     */
    public function verifyPassword($user_id, $password) {
        $user = $this->getById($user_id);
        if (!$user) return false;
        return password_verify($password, $user['password']);
    }
    
    /**
     * Get users by role
     * 
     * @param string $role
     * @return array
     */
    public function getByRole($role) {
        $sql = "SELECT * FROM {$this->table} WHERE role = ? AND status = 1 ORDER BY nama_lengkap ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check username available
     * 
     * @param string $username
     * @param int $exclude_user_id
     * @return bool
     */
    public function isUsernameAvailable($username, $exclude_user_id = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];
        
        if ($exclude_user_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_user_id;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] == 0;
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check email available
     * 
     * @param string $email
     * @param int $exclude_user_id
     * @return bool
     */
    public function isEmailAvailable($email, $exclude_user_id = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($exclude_user_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_user_id;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] == 0;
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }
    }
}

?>
