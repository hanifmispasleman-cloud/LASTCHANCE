<?php
/**
 * Database - Class untuk koneksi database
 */

class Database {
    
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $name = DB_NAME;
    private $conn;
    
    /**
     * Connect to database
     */
    public function connect() {
        $this->conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->name
        );
        
        // Check connection
        if ($this->conn->connect_error) {
            error_log('Database Connection Error: ' . $this->conn->connect_error);
            die('Koneksi database gagal');
        }
        
        // Set charset
        $this->conn->set_charset('utf8mb4');
        
        return $this->conn;
    }
    
    /**
     * Get connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

?>
