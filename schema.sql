-- ============================================================
-- File: database/schema.sql
-- Database: kasirku_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS kasirku_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kasirku_db;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir', 'owner') NOT NULL DEFAULT 'kasir',
    foto VARCHAR(255) DEFAULT NULL,
    aktif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Tabel kategori
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel supplier
CREATE TABLE supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT DEFAULT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama (nama)
) ENGINE=InnoDB;

-- Tabel pelanggan
CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama (nama)
) ENGINE=InnoDB;

-- Tabel produk
CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(50) NOT NULL UNIQUE,
    barcode VARCHAR(50) DEFAULT NULL,
    nama VARCHAR(200) NOT NULL,
    id_kategori INT DEFAULT NULL,
    id_supplier INT DEFAULT NULL,
    harga_beli DECIMAL(15,2) NOT NULL DEFAULT 0,
    harga_jual DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    stok_minimum INT NOT NULL DEFAULT 5,
    gambar VARCHAR(255) DEFAULT NULL,
    aktif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id) ON DELETE SET NULL,
    FOREIGN KEY (id_supplier) REFERENCES supplier(id) ON DELETE SET NULL,
    INDEX idx_kode (kode),
    INDEX idx_barcode (barcode),
    INDEX idx_nama (nama),
    INDEX idx_kategori (id_kategori),
    INDEX idx_aktif (aktif)
) ENGINE=InnoDB;

-- Tabel transaksi
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice VARCHAR(50) NOT NULL UNIQUE,
    id_user INT NOT NULL,
    id_pelanggan INT DEFAULT NULL,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    diskon DECIMAL(15,2) NOT NULL DEFAULT 0,
    pajak DECIMAL(15,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    bayar DECIMAL(15,2) NOT NULL DEFAULT 0,
    kembalian DECIMAL(15,2) NOT NULL DEFAULT 0,
    metode_pembayaran ENUM('tunai', 'transfer', 'qris') NOT NULL DEFAULT 'tunai',
    catatan TEXT DEFAULT NULL,
    status ENUM('pending', 'sukses', 'batal') DEFAULT 'sukses',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE SET NULL,
    INDEX idx_no_invoice (no_invoice),
    INDEX idx_tanggal (created_at),
    INDEX idx_user (id_user)
) ENGINE=InnoDB;

-- Tabel detail_transaksi
CREATE TABLE detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    harga_satuan DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE RESTRICT,
    INDEX idx_transaksi (id_transaksi),
    INDEX idx_produk (id_produk)
) ENGINE=InnoDB;

-- Tabel stok_masuk
CREATE TABLE stok_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produk INT NOT NULL,
    id_user INT NOT NULL,
    id_supplier INT DEFAULT NULL,
    qty INT NOT NULL,
    harga_beli DECIMAL(15,2) NOT NULL,
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_supplier) REFERENCES supplier(id) ON DELETE SET NULL,
    INDEX idx_produk (id_produk),
    INDEX idx_tanggal (created_at)
) ENGINE=InnoDB;

-- Tabel stok_keluar
CREATE TABLE stok_keluar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produk INT NOT NULL,
    id_user INT NOT NULL,
    qty INT NOT NULL,
    jenis ENUM('rusak', 'kadaluarsa', 'lainnya') NOT NULL DEFAULT 'lainnya',
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_produk (id_produk),
    INDEX idx_tanggal (created_at)
) ENGINE=InnoDB;

-- Tabel pengaturan
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunci VARCHAR(50) NOT NULL UNIQUE,
    nilai TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default pengaturan
INSERT INTO pengaturan (kunci, nilai) VALUES
('nama_toko', 'KasirKu POS'),
('alamat', 'Jl. Contoh No. 123, Kota'),
('telepon', '0812-3456-7890'),
('logo', 'logo.png'),
('pajak', '11'),
('mata_uang', 'Rp'),
('footer_struk', 'Terima kasih telah berbelanja!'),
('ukuran_struk', '58');

-- Insert default user admin (password: admin123)
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default user kasir (password: kasir123)
INSERT INTO users (nama, username, password, role) VALUES
('Kasir 1', 'kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir');

-- Insert sample kategori
INSERT INTO kategori (nama) VALUES ('Makanan'), ('Minuman'), ('Snack'), ('Alat Tulis'), ('Kebutuhan Rumah');