-- ============================================
-- DATABASE KASIRKU - Point of Sale System
-- ============================================

CREATE DATABASE IF NOT EXISTS kasirku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kasirku;

-- TABLE: users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(150) NOT NULL,
    role ENUM('admin', 'kasir', 'owner') NOT NULL DEFAULT 'kasir',
    status TINYINT(1) NOT NULL DEFAULT 1,
    foto_profil VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: pengaturan
CREATE TABLE pengaturan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_toko VARCHAR(255) NOT NULL,
    logo VARCHAR(255),
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(150),
    pajak DECIMAL(5, 2) DEFAULT 10.00,
    mata_uang VARCHAR(5) DEFAULT 'IDR',
    footer_struk TEXT,
    tagline VARCHAR(255),
    jam_operasional VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: kategori
CREATE TABLE kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT,
    icon VARCHAR(50),
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_nama (nama_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: produk
CREATE TABLE produk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_produk VARCHAR(50) NOT NULL UNIQUE,
    nama_produk VARCHAR(255) NOT NULL,
    kategori_id INT NOT NULL,
    deskripsi TEXT,
    harga_beli DECIMAL(12, 2) NOT NULL,
    harga_jual DECIMAL(12, 2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    stok_minimum INT NOT NULL DEFAULT 5,
    satuan VARCHAR(20) DEFAULT 'pcs',
    barcode VARCHAR(100) UNIQUE,
    gambar VARCHAR(255),
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE,
    INDEX idx_kode_produk (kode_produk),
    INDEX idx_nama_produk (nama_produk),
    INDEX idx_kategori (kategori_id),
    INDEX idx_status (status),
    INDEX idx_barcode (barcode),
    INDEX idx_stok (stok)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: supplier
CREATE TABLE supplier (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_supplier VARCHAR(200) NOT NULL,
    kontak_person VARCHAR(150),
    telepon VARCHAR(20),
    email VARCHAR(150),
    alamat TEXT,
    kota VARCHAR(100),
    provinsi VARCHAR(100),
    kode_pos VARCHAR(10),
    rekening_bank VARCHAR(30),
    nama_bank VARCHAR(50),
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama (nama_supplier),
    INDEX idx_telepon (telepon),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: pelanggan
CREATE TABLE pelanggan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_pelanggan VARCHAR(200) NOT NULL,
    telepon VARCHAR(20),
    email VARCHAR(150),
    alamat TEXT,
    kota VARCHAR(100),
    provinsi VARCHAR(100),
    kode_pos VARCHAR(10),
    tipe_pelanggan ENUM('regular', 'member') DEFAULT 'regular',
    nomor_member VARCHAR(50) UNIQUE,
    poin_reward INT DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama (nama_pelanggan),
    INDEX idx_telepon (telepon),
    INDEX idx_tipe (tipe_pelanggan),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: transaksi
CREATE TABLE transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_transaksi VARCHAR(50) NOT NULL UNIQUE,
    pelanggan_id INT,
    user_id INT NOT NULL,
    tanggal_transaksi DATETIME NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    diskon DECIMAL(15, 2) DEFAULT 0,
    pajak DECIMAL(15, 2) DEFAULT 0,
    total DECIMAL(15, 2) NOT NULL,
    metode_pembayaran ENUM('tunai', 'transfer', 'qris') NOT NULL,
    status_pembayaran ENUM('lunas', 'cicil', 'pending') DEFAULT 'lunas',
    catatan TEXT,
    kembalian DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_nomor_transaksi (nomor_transaksi),
    INDEX idx_tanggal (tanggal_transaksi),
    INDEX idx_user_id (user_id),
    INDEX idx_metode_pembayaran (metode_pembayaran),
    INDEX idx_status_pembayaran (status_pembayaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: detail_transaksi
CREATE TABLE detail_transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    qty INT NOT NULL,
    harga_satuan DECIMAL(12, 2) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    diskon_item DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT,
    INDEX idx_transaksi_id (transaksi_id),
    INDEX idx_produk_id (produk_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: activity_log
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_value LONGTEXT,
    new_value LONGTEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEED DATA AWAL
INSERT INTO kategori (nama_kategori, deskripsi, status) VALUES
('Makanan & Minuman', 'Kategori makanan dan minuman', 1),
('Elektronik', 'Produk elektronik', 1),
('Fashion', 'Pakaian dan aksesori', 1);

-- Default User: admin / password: admin123
INSERT INTO users (username, email, password, nama_lengkap, role, status) VALUES
('admin', 'admin@kasirku.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/ym', 'Administrator', 'admin', 1);

INSERT INTO pengaturan (nama_toko, alamat, telepon, email, pajak, mata_uang, footer_struk, tagline) VALUES
('KasirKu Store', 'Jl. Merdeka No. 123, Jakarta', '021-1234567', 'info@kasirku.com', 10.00, 'IDR', 'Terima kasih telah berbelanja', 'POS Terpercaya untuk UMKM Indonesia');
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
