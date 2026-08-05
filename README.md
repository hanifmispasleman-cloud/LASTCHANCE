# KasirKu – AI POS Multi-Tenant

Aplikasi kasir & Point of Sale berbasis AI untuk multi-bisnis (F&B, Ritel, Otomotif, dll).  
Berjalan **100% di browser** — tanpa server, tanpa build process, siap deploy ke GitHub Pages.

## 🚀 Cara Deploy ke GitHub Pages

### Langkah 1 – Buat Repository
1. Buka [github.com](https://github.com) → klik **New repository**
2. Beri nama, misal: `kasirku-pos`
3. Set **Public** → klik **Create repository**

### Langkah 2 – Upload File
Pilih salah satu cara:

**Cara A – Upload via Web GitHub:**
1. Klik **Add file → Upload files**
2. Drag & drop seluruh isi folder ini (index.html, css/, js/, backend/)
3. Klik **Commit changes**

**Cara B – via Git (terminal):**
```bash
git init
git add .
git commit -m "Initial commit KasirKu"
git branch -M main
git remote add origin https://github.com/USERNAME/kasirku-pos.git
git push -u origin main
```

### Langkah 3 – Aktifkan GitHub Pages
1. Di repository → **Settings** → **Pages** (menu kiri)
2. Under **Source**: pilih **Deploy from a branch**
3. Branch: `main` | Folder: `/ (root)`
4. Klik **Save**
5. Tunggu 1–2 menit → URL akan muncul:  
   `https://USERNAME.github.io/kasirku-pos/`

---

## 📁 Struktur File

```
kasirku/
├── index.html          ← Entry point, import semua CDN & script
├── .nojekyll           ← Wajib: nonaktifkan Jekyll di GitHub Pages
├── README.md           ← Dokumentasi ini
├── css/
│   └── style.css       ← Custom CSS (scrollbar, animasi, struk)
├── js/
│   ├── db.js           ← Database layer (Dexie.js / IndexedDB)
│   └── app.js          ← Aplikasi React lengkap (6 modul)
└── backend/
    └── schema.sql      ← Referensi skema PostgreSQL (opsional)
```

---

## 🛠️ Stack Teknologi (Semua via CDN, Tanpa Build)

| Library | Versi | Fungsi |
|---------|-------|--------|
| React | 18.2.0 | UI Framework |
| ReactDOM | 18.2.0 | Render ke DOM |
| Babel Standalone | 7.23.6 | Transpile JSX di browser |
| Tailwind CSS | Latest | Styling utility-first |
| Dexie.js | 3.2.4 | Wrapper IndexedDB (database lokal) |

---

## ✨ Fitur

| Modul | Fitur |
|-------|-------|
| **Autentikasi** | Login/Register lokal, sesi permanen (tidak hilang saat refresh) |
| **Multi-Bisnis** | Buat & kelola banyak profil bisnis dengan data terpisah total |
| **Kasir / POS** | Grid menu, filter kategori, keranjang, checkout, struk digital |
| **Stok Otomatis** | Bahan baku terpotong otomatis sesuai resep saat transaksi |
| **Gudang** | CRUD bahan baku, valuasi stok, alert stok menipis |
| **Waste** | Catat bahan rusak/basi, potong stok tanpa tercatat sebagai omset |
| **Menu & Resep** | CRUD menu, komposisi resep, kalkulasi HPP real-time |
| **OPEX** | Catat biaya operasional (Listrik, Gaji, Sewa, dll) per kategori |
| **Laporan Live** | Auto-refresh 5 detik, rekap Omset/HPP/Laba Bersih/Margin |
| **AI Diagnostic** | Skor kesehatan 0–100% + diagnosa + rekomendasi aksi konkret |

---

## 🔒 Isolasi Data (Penting untuk GitHub Pages)

Data disimpan secara lokal di browser dengan prefix unik agar tidak bentrok
jika beberapa app berbeda-beda di-deploy di domain yang sama (`github.io`):

- **LocalStorage prefix:** `kasirku_ghpages_v1_`
- **IndexedDB name:** `KasirKu_GHPages_DB_v1`

---

## ⚠️ Catatan Penting

- Data tersimpan **hanya di browser lokal** (IndexedDB). Membuka di browser/perangkat lain = data berbeda.
- Untuk data yang bisa diakses dari mana saja, hubungkan ke backend (lihat `backend/schema.sql`).
- File `backend/schema.sql` adalah **referensi saja** — tidak dibutuhkan untuk menjalankan aplikasi ini.

---

## 📄 Lisensi

MIT License – bebas digunakan, dimodifikasi, dan didistribusikan.
