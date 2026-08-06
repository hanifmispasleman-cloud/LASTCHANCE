-- ============================================
-- DATABASE KASIRKU - Point of Sale System
-- ============================================

-- Buat Database
CREATE DATABASE IF NOT EXISTS kasirku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kasirku;

-- ============================================
-- TABLE: users (Pengguna Sistem)
-- ============================================
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

-- ============================================
-- TABLE: pengaturan (Konfigurasi Toko)
-- ============================================
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

-- ============================================
-- TABLE: kategori (Kategori Produk)
-- ============================================
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

-- ============================================
-- TABLE: produk (Data Produk)
-- ============================================
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

-- ============================================
-- TABLE: supplier (Data Supplier)
-- ============================================
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

-- ============================================
-- TABLE: pelanggan (Data Pelanggan)
-- ============================================
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

-- ============================================
-- TABLE: transaksi (Transaksi POS)
-- ============================================
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

-- ============================================
-- TABLE: detail_transaksi (Detail Item Transaksi)
-- ============================================
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

-- ============================================
-- TABLE: stok_masuk (Pencatatan Stok Masuk)
-- ============================================
CREATE TABLE stok_masuk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_stok VARCHAR(50) NOT NULL UNIQUE,
    produk_id INT NOT NULL,
    supplier_id INT NOT NULL,
    qty INT NOT NULL,
    harga_beli DECIMAL(12, 2) NOT NULL,
    total_beli DECIMAL(15, 2) NOT NULL,
    tanggal_masuk DATETIME NOT NULL,
    nomor_po VARCHAR(50),
    keterangan TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_nomor_stok (nomor_stok),
    INDEX idx_produk_id (produk_id),
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_tanggal (tanggal_masuk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: stok_keluar (Pencatatan Stok Keluar Selain Transaksi)
-- ============================================
CREATE TABLE stok_keluar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_stok VARCHAR(50) NOT NULL UNIQUE,
    produk_id INT NOT NULL,
    qty INT NOT NULL,
    alasan ENUM('rusak', 'return', 'sample', 'lainnya') NOT NULL,
    tanggal_keluar DATETIME NOT NULL,
    keterangan TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_nomor_stok (nomor_stok),
    INDEX idx_produk_id (produk_id),
    INDEX idx_tanggal (tanggal_keluar)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: activity_log (Audit Trail)
-- ============================================
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

-- ============================================
-- INSERT DATA AWAL
-- ============================================

-- Insert Kategori Awal
INSERT INTO kategori (nama_kategori, deskripsi, status) VALUES
('Makanan & Minuman', 'Kategori makanan dan minuman', 1),
('Elektronik', 'Produk elektronik', 1),
('Fashion', 'Pakaian dan aksesori', 1),
('Kesehatan', 'Produk kesehatan dan wellness', 1),
('Lainnya', 'Kategori lainnya', 1);

-- Insert User Admin Awal (Password: admin123)
INSERT INTO users (username, email, password, nama_lengkap, role, status) VALUES
('admin', 'admin@kasirku.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/ym', 'Administrator', 'admin', 1);

-- Insert Pengaturan Awal
INSERT INTO pengaturan (nama_toko, alamat, telepon, email, pajak, mata_uang, footer_struk, tagline) VALUES
('KasirKu Store', 'Jl. Merdeka No. 123, Jakarta', '021-1234567', 'info@kasirku.com', 10.00, 'IDR', 'Terima kasih telah berbelanja', 'POS Terpercaya untuk UMKM Indonesia');
