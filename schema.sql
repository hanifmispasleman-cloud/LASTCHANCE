-- =============================================================
--  KasirKu – Skema Database Relasional (Referensi Backend)
--  File: backend/schema.sql
--  Target: PostgreSQL 14+  |  Kompatibel: MySQL 8+ (lihat catatan)
--  Digunakan sebagai referensi jika sistem dihubungkan ke backend
--  berbasis Python (FastAPI / Flask + SQLAlchemy / Tortoise ORM).
--  Data saat ini disimpan di IndexedDB (Dexie.js) di browser.
-- =============================================================

-- -----------------------------------------------------------
-- EXTENSION (PostgreSQL only)
-- -----------------------------------------------------------
CREATE EXTENSION IF NOT EXISTS "pgcrypto";   -- gen_random_uuid()
CREATE EXTENSION IF NOT EXISTS "pg_trgm";   -- pencarian ILIKE cepat

-- -----------------------------------------------------------
-- TABEL: users
-- -----------------------------------------------------------
CREATE TABLE users (
    id            TEXT          PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    username      VARCHAR(80)   NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,   -- bcrypt / argon2 hash
    email         VARCHAR(255),
    is_active     BOOLEAN       NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at    TIMESTAMPTZ   NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_users_username UNIQUE (username),
    CONSTRAINT ck_users_username_len CHECK (LENGTH(username) >= 3)
);

CREATE INDEX idx_users_username ON users (LOWER(username));

-- -----------------------------------------------------------
-- TABEL: businesses  (Multi-Tenant: 1 user = banyak bisnis)
-- -----------------------------------------------------------
CREATE TABLE businesses (
    id          TEXT          PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    user_id     TEXT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name        VARCHAR(150)  NOT NULL,
    type        VARCHAR(50)   NOT NULL DEFAULT 'F&B',   -- F&B | Ritel | Otomotif | Jasa | Lainnya
    address     TEXT,
    phone       VARCHAR(30),
    logo_url    TEXT,
    created_at  TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_businesses_user_id ON businesses (user_id);

-- -----------------------------------------------------------
-- TABEL: ingredients  (Master Bahan Baku / Gudang)
-- -----------------------------------------------------------
CREATE TABLE ingredients (
    id              TEXT            PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    business_id     TEXT            NOT NULL REFERENCES businesses(id) ON DELETE CASCADE,
    name            VARCHAR(150)    NOT NULL,
    unit            VARCHAR(20)     NOT NULL DEFAULT 'pcs',  -- kg | gr | ltr | ml | pcs | dll
    price_per_unit  NUMERIC(15,2)   NOT NULL DEFAULT 0,
    stock           NUMERIC(15,3)   NOT NULL DEFAULT 0,
    min_stock       NUMERIC(15,3)            DEFAULT 0,  -- batas stok minimum (alert)
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT ck_ingredients_stock       CHECK (stock >= 0),
    CONSTRAINT ck_ingredients_price       CHECK (price_per_unit >= 0)
);

CREATE INDEX idx_ingredients_business ON ingredients (business_id);
CREATE INDEX idx_ingredients_name_trgm ON ingredients USING GIN (name gin_trgm_ops);

-- -----------------------------------------------------------
-- TABEL: products  (Menu Jual)
-- -----------------------------------------------------------
CREATE TABLE products (
    id           TEXT            PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    business_id  TEXT            NOT NULL REFERENCES businesses(id) ON DELETE CASCADE,
    name         VARCHAR(200)    NOT NULL,
    category     VARCHAR(100),
    description  TEXT,
    price        NUMERIC(15,2)   NOT NULL DEFAULT 0,
    hpp          NUMERIC(15,2)   NOT NULL DEFAULT 0,  -- Harga Pokok Penjualan (dihitung dari resep)
    emoji        VARCHAR(10)              DEFAULT '🍽️',
    is_active    BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at   TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT ck_products_price CHECK (price >= 0),
    CONSTRAINT ck_products_hpp   CHECK (hpp >= 0)
);

CREATE INDEX idx_products_business    ON products (business_id);
CREATE INDEX idx_products_category    ON products (business_id, category);
CREATE INDEX idx_products_active      ON products (business_id, is_active);
CREATE INDEX idx_products_name_trgm   ON products USING GIN (name gin_trgm_ops);

-- -----------------------------------------------------------
-- TABEL: recipes  (Komposisi Resep: Produk <-> Bahan Baku)
-- -----------------------------------------------------------
CREATE TABLE recipes (
    id             TEXT          PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    product_id     TEXT          NOT NULL REFERENCES products(id)     ON DELETE CASCADE,
    business_id    TEXT          NOT NULL REFERENCES businesses(id)   ON DELETE CASCADE,
    ingredient_id  TEXT          NOT NULL REFERENCES ingredients(id)  ON DELETE CASCADE,
    quantity       NUMERIC(15,4) NOT NULL DEFAULT 0,   -- jumlah bahan per 1 porsi produk
    created_at     TIMESTAMPTZ   NOT NULL DEFAULT NOW(),

    CONSTRAINT ck_recipes_quantity   CHECK (quantity > 0),
    CONSTRAINT uq_recipe_product_ing UNIQUE (product_id, ingredient_id)
);

CREATE INDEX idx_recipes_product    ON recipes (product_id);
CREATE INDEX idx_recipes_ingredient ON recipes (ingredient_id);
CREATE INDEX idx_recipes_business   ON recipes (business_id);

-- -----------------------------------------------------------
-- TABEL: transactions  (Header Transaksi Kasir)
-- -----------------------------------------------------------
CREATE TABLE transactions (
    id              TEXT            PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    business_id     TEXT            NOT NULL REFERENCES businesses(id) ON DELETE CASCADE,
    transaction_date TIMESTAMPTZ    NOT NULL DEFAULT NOW(),
    total           NUMERIC(15,2)   NOT NULL DEFAULT 0,
    total_hpp       NUMERIC(15,2)   NOT NULL DEFAULT 0,
    gross_profit    NUMERIC(15,2)   NOT NULL DEFAULT 0,
    payment_method  VARCHAR(50)     NOT NULL DEFAULT 'Tunai',  -- Tunai | QRIS | Transfer | dll
    status          VARCHAR(20)     NOT NULL DEFAULT 'done',   -- done | void | pending
    notes           TEXT,
    created_at      TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT ck_transactions_total CHECK (total >= 0)
);

CREATE INDEX idx_transactions_business ON transactions (business_id);
CREATE INDEX idx_transactions_date     ON transactions (business_id, transaction_date DESC);
CREATE INDEX idx_transactions_status   ON transactions (business_id, status);

-- -----------------------------------------------------------
-- TABEL: transaction_items  (Detail Item per Transaksi)
-- -----------------------------------------------------------
CREATE TABLE transaction_items (
    id              TEXT            PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    transaction_id  TEXT            NOT NULL REFERENCES transactions(id) ON DELETE CASCADE,
    product_id      TEXT            REFERENCES products(id) ON DELETE SET NULL,
    product_name    VARCHAR(200)    NOT NULL,  -- snapshot nama saat transaksi
    qty             INTEGER         NOT NULL DEFAULT 1,
    price           NUMERIC(15,2)   NOT NULL DEFAULT 0,  -- harga jual saat transaksi
    hpp             NUMERIC(15,2)   NOT NULL DEFAULT 0,  -- hpp snapshot saat transaksi
    subtotal        NUMERIC(15,2)   GENERATED ALWAYS AS (price * qty) STORED,

    CONSTRAINT ck_tx_items_qty   CHECK (qty > 0),
    CONSTRAINT ck_tx_items_price CHECK (price >= 0)
);

CREATE INDEX idx_tx_items_transaction ON transaction_items (transaction_id);
CREATE INDEX idx_tx_items_product     ON transaction_items (product_id);

-- -----------------------------------------------------------
-- TABEL: opex_entries  (Biaya Operasional / OPEX)
-- -----------------------------------------------------------
CREATE TABLE opex_entries (
    id           TEXT            PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    business_id  TEXT            NOT NULL REFERENCES businesses(id) ON DELETE CASCADE,
    category     VARCHAR(100)    NOT NULL DEFAULT 'Lainnya',  -- Listrik | Gaji | Sewa | dll
    description  TEXT,
    amount       NUMERIC(15,2)   NOT NULL DEFAULT 0,
    entry_date   DATE            NOT NULL DEFAULT CURRENT_DATE,
    created_at   TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT ck_opex_amount CHECK (amount > 0)
);

CREATE INDEX idx_opex_business  ON opex_entries (business_id);
CREATE INDEX idx_opex_date      ON opex_entries (business_id, entry_date DESC);
CREATE INDEX idx_opex_category  ON opex_entries (business_id, category);

-- -----------------------------------------------------------
-- TABEL: waste_entries  (Catatan Waste / Bahan Rusak/Basi)
-- -----------------------------------------------------------
CREATE TABLE waste_entries (
    id               TEXT            PRIMARY KEY DEFAULT gen_random_uuid()::TEXT,
    business_id      TEXT            NOT NULL REFERENCES businesses(id)   ON DELETE CASCADE,
    ingredient_id    TEXT            REFERENCES ingredients(id)           ON DELETE SET NULL,
    ingredient_name  VARCHAR(150)    NOT NULL,  -- snapshot nama
    quantity         NUMERIC(15,4)   NOT NULL DEFAULT 0,
    unit             VARCHAR(20)     NOT NULL DEFAULT 'pcs',
    value_lost       NUMERIC(15,2)   NOT NULL DEFAULT 0,  -- kerugian finansial
    reason           TEXT,                                 -- alasan waste (opsional)
    entry_date       DATE            NOT NULL DEFAULT CURRENT_DATE,
    created_at       TIMESTAMPTZ     NOT NULL DEFAULT NOW(),

    CONSTRAINT ck_waste_qty CHECK (quantity > 0)
);

CREATE INDEX idx_waste_business    ON waste_entries (business_id);
CREATE INDEX idx_waste_date        ON waste_entries (business_id, entry_date DESC);
CREATE INDEX idx_waste_ingredient  ON waste_entries (ingredient_id);

-- -----------------------------------------------------------
-- VIEW: v_daily_summary  (Rekap Harian per Bisnis)
-- -----------------------------------------------------------
CREATE OR REPLACE VIEW v_daily_summary AS
SELECT
    t.business_id,
    DATE(t.transaction_date)          AS tx_date,
    COUNT(t.id)                       AS tx_count,
    SUM(t.total)                      AS total_omset,
    SUM(t.total_hpp)                  AS total_hpp,
    SUM(t.gross_profit)               AS total_gross_profit,
    COALESCE(SUM(o.amount), 0)        AS total_opex,
    SUM(t.gross_profit) - COALESCE(SUM(o.amount), 0) AS net_profit
FROM transactions t
LEFT JOIN opex_entries o
    ON  o.business_id = t.business_id
    AND o.entry_date  = DATE(t.transaction_date)
WHERE t.status = 'done'
GROUP BY t.business_id, DATE(t.transaction_date)
ORDER BY tx_date DESC;

-- -----------------------------------------------------------
-- VIEW: v_product_profitability  (Profitabilitas per Produk)
-- -----------------------------------------------------------
CREATE OR REPLACE VIEW v_product_profitability AS
SELECT
    p.business_id,
    p.id                                          AS product_id,
    p.name                                        AS product_name,
    p.category,
    p.price,
    p.hpp,
    p.price - p.hpp                               AS gross_margin,
    CASE WHEN p.price > 0
         THEN ROUND(((p.price - p.hpp) / p.price * 100)::NUMERIC, 2)
         ELSE 0 END                               AS margin_pct,
    COALESCE(SUM(ti.qty),   0)                   AS total_qty_sold,
    COALESCE(SUM(ti.subtotal), 0)                AS total_revenue,
    COALESCE(SUM(ti.hpp * ti.qty), 0)            AS total_hpp_sold
FROM products p
LEFT JOIN transaction_items ti ON ti.product_id = p.id
LEFT JOIN transactions       t  ON t.id = ti.transaction_id AND t.status = 'done'
WHERE p.is_active = TRUE
GROUP BY p.business_id, p.id, p.name, p.category, p.price, p.hpp
ORDER BY total_revenue DESC;

-- -----------------------------------------------------------
-- VIEW: v_ingredient_status  (Status & Valuasi Gudang)
-- -----------------------------------------------------------
CREATE OR REPLACE VIEW v_ingredient_status AS
SELECT
    i.business_id,
    i.id,
    i.name,
    i.unit,
    i.price_per_unit,
    i.stock,
    i.min_stock,
    i.stock * i.price_per_unit                   AS stock_value,
    CASE
        WHEN i.stock <= 0                         THEN 'HABIS'
        WHEN i.min_stock > 0
             AND i.stock <= i.min_stock           THEN 'MENIPIS'
        ELSE                                           'OK'
    END                                           AS stock_status
FROM ingredients i
ORDER BY stock_status, i.name;

-- -----------------------------------------------------------
-- FUNGSI: fn_deduct_stock_on_sale()
-- Dipanggil via TRIGGER setelah INSERT ke transaction_items
-- untuk memotong stok bahan baku sesuai resep secara atomik.
-- -----------------------------------------------------------
CREATE OR REPLACE FUNCTION fn_deduct_stock_on_sale()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE
    rec RECORD;
BEGIN
    FOR rec IN
        SELECT r.ingredient_id, r.quantity * NEW.qty AS total_used
        FROM   recipes r
        WHERE  r.product_id = NEW.product_id
    LOOP
        UPDATE ingredients
           SET stock     = GREATEST(0, stock - rec.total_used),
               updated_at = NOW()
         WHERE id = rec.ingredient_id;
    END LOOP;
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_deduct_stock
AFTER INSERT ON transaction_items
FOR EACH ROW EXECUTE FUNCTION fn_deduct_stock_on_sale();

-- -----------------------------------------------------------
-- DATA AWAL (Seed) – Contoh untuk development / testing
-- Hapus blok ini di production.
-- -----------------------------------------------------------
/*
INSERT INTO users (id, username, password_hash) VALUES
  ('usr_demo', 'demo', '$2b$12$HASH_PLACEHOLDER');

INSERT INTO businesses (id, user_id, name, type) VALUES
  ('biz_demo', 'usr_demo', 'Warung Makan Demo', 'F&B');

INSERT INTO ingredients (id, business_id, name, unit, price_per_unit, stock, min_stock) VALUES
  ('ing_001', 'biz_demo', 'Ayam Potong',  'kg',  35000, 10,  2),
  ('ing_002', 'biz_demo', 'Beras',        'kg',  12000, 25,  5),
  ('ing_003', 'biz_demo', 'Minyak Goreng','ltr', 15000,  5,  1);

INSERT INTO products (id, business_id, name, category, price, hpp, emoji) VALUES
  ('prd_001', 'biz_demo', 'Nasi Ayam Goreng', 'Makanan', 20000, 8500, '🍛'),
  ('prd_002', 'biz_demo', 'Es Teh Manis',     'Minuman',  5000, 1200, '🧋');

INSERT INTO recipes (id, product_id, business_id, ingredient_id, quantity) VALUES
  ('rcp_001', 'prd_001', 'biz_demo', 'ing_001', 0.2),
  ('rcp_002', 'prd_001', 'biz_demo', 'ing_002', 0.1),
  ('rcp_003', 'prd_001', 'biz_demo', 'ing_003', 0.02);
*/

-- =============================================================
-- CATATAN KOMPATIBILITAS MySQL 8+
-- =============================================================
-- 1. Ganti gen_random_uuid()::TEXT -> UUID() (MySQL)
-- 2. Ganti TIMESTAMPTZ -> DATETIME (MySQL)
-- 3. Hapus GENERATED ALWAYS AS ... STORED dan hitung di aplikasi
-- 4. Hapus CREATE EXTENSION (tidak didukung MySQL)
-- 5. Ganti NUMERIC -> DECIMAL (MySQL)
-- 6. Hapus GIN index, gunakan FULLTEXT index (MySQL)
-- 7. Trigger syntax sedikit berbeda – sesuaikan delimiter
-- =============================================================
