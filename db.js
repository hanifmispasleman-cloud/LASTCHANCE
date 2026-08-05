/* =============================================================
   KasirKu – Database Layer
   File: js/db.js
   Teknologi: Dexie.js (IndexedDB wrapper) + LocalStorage
   DB Name  : KasirKu_GHPages_DB_v1  (nama unik agar tidak
              bentrok jika deploy di domain bersama github.io)
   ============================================================= */

(function () {
  'use strict';

  /* ── Konstanta Prefix (digunakan di app.js juga) ── */
  window.STORAGE_PREFIX = 'kasirku_ghpages_v1_';

  /* ── Inisialisasi Dexie ── */
  const db = new Dexie('KasirKu_GHPages_DB_v1');

  /**
   * Versi 1 – Skema lengkap awal.
   * Kolom dengan '&' = primary key unik.
   * Kolom tanpa tanda = index biasa (untuk query .where()).
   */
  db.version(1).stores({
    /* Pengguna aplikasi */
    users: '&id, username',

    /* Profil bisnis (multi-tenant) */
    businesses: '&id, userId',

    /* Produk / Menu jual */
    products: '&id, businessId, category, active',

    /* Master bahan baku gudang */
    ingredients: '&id, businessId',

    /* Komposisi resep: relasi produk <-> bahan baku */
    recipes: '&id, productId, businessId, ingredientId',

    /* Header transaksi kasir */
    transactions: '&id, businessId, date',

    /* Detail item per transaksi */
    transaction_items: '&id, transactionId',

    /* Biaya Operasional (OPEX) */
    opex_entries: '&id, businessId, date, category',

    /* Catatan waste / bahan basi */
    waste_entries: '&id, businessId, date, ingredientId',
  });

  /* ── Buka koneksi ── */
  db.open().catch(function (err) {
    console.error('[KasirKu DB] Gagal membuka database:', err.stack || err);
  });

  /* ── Expose ke global window agar bisa diakses dari app.js ── */
  window.db = db;

  /* ── Helpers LocalStorage (dengan prefix isolasi) ── */
  window.lsSet = function (key, value) {
    try { localStorage.setItem(window.STORAGE_PREFIX + key, JSON.stringify(value)); } catch (e) { /* quota */ }
  };
  window.lsGet = function (key) {
    try {
      const raw = localStorage.getItem(window.STORAGE_PREFIX + key);
      return raw ? JSON.parse(raw) : null;
    } catch { return null; }
  };
  window.lsDel = function (key) {
    try { localStorage.removeItem(window.STORAGE_PREFIX + key); } catch (e) { /* ignore */ }
  };

})();
