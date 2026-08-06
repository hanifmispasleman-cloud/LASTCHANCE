<?php
/**
 * Konfigurasi Database KasirKu
 * 
 * File ini berisi konfigurasi koneksi ke database MySQL
 * Gunakan prepared statement untuk keamanan
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'kasirku';
    private $db_user = 'root';
    private $db_pass = '';
    private $charset = 'utf8mb4';
    
    private $conn;

    /**
     * Koneksi ke database
     * 
     * @return PDO
     */
    public function connect() {
        $this->conn = null;

        try {
            $dsn = 'mysql:host=' . $this->host . 
                   ';dbname=' . $this->db_name . 
                   ';charset=' . $this->charset;

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->db_user, $this->db_pass, $options);

        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please check error log.');
        }

        return $this->conn;
    }

    /**
     * Dapatkan koneksi database
     * 
     * @return PDO
     */
    public function getConnection() {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>
