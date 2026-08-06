# KasirKu - Aplikasi Point of Sale

Aplikasi POS (Point of Sale) berbasis web untuk UMKM Indonesia.

## Fitur
- Login multi-role (Admin, Kasir, Owner)
- Dashboard dengan grafik penjualan 7 hari
- Manajemen produk, kategori, supplier, pelanggan
- Kasir dengan pencarian instan & barcode scanner
- Multi pembayaran (Tunai, Transfer, QRIS)
- Cetak struk thermal 58mm
- Laporan penjualan harian/bulanan/tahunan
- Export PDF & Excel
- Pengaturan toko & user

## Instalasi
1. Letakkan folder proyek di `htdocs` (XAMPP) atau `www` (Laragon)
2. Buat database `kasirku_db`
3. Import `database/schema.sql` ke database
4. Sesuaikan konfigurasi di `app/Config/Database.php` dan `app/Config/App.php`
5. Akses `http://localhost/KasirKu`

## Default Login
| Role  | Username | Password |
|-------|----------|----------|
| Admin | admin    | admin123 |
| Kasir | kasir    | kasir123 |

## Struktur Folder
- `app/` - Controller, Model, Core, Middleware, Helper
- `database/` - Schema SQL
- `public/` - Asset (CSS, JS, uploads) & entry point
- `views/` - Template tampilan