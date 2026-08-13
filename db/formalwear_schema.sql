-- =========================================================
-- SISTEM PEMINJAMAN/SEWA FORMAL WEAR
-- Schema + Seed Data
-- Fitur unik: Chatbot Budget-Matching & Dynamic Pricing
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- 1. USERS
-- =========================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'anggota') NOT NULL DEFAULT 'anggota',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- 2. PROFILES  (1 user = 1 profile)
-- =========================================================
CREATE TABLE profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    no_hp VARCHAR(20) NOT NULL,
    alamat TEXT NULL,
    foto VARCHAR(255) NULL,
    ukuran_biasa ENUM('XS','S','M','L','XL','XXL') NULL,
    kategori_favorit_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 3. CATEGORIES
-- =========================================================
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    urutan_tampil INT NOT NULL DEFAULT 0,
    tampil_navbar BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- 4. UNITS
-- =========================================================
CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_unit VARCHAR(30) NOT NULL UNIQUE,
    nama_unit VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    ukuran ENUM('XS','S','M','L','XL','XXL','All Size') NOT NULL DEFAULT 'All Size',
    warna VARCHAR(50) NULL,
    foto VARCHAR(255) NULL,
    harga_dasar DECIMAL(10,2) NOT NULL,
    harga_saat_ini DECIMAL(10,2) NOT NULL,
    status ENUM('tersedia','dipinjam','maintenance') NOT NULL DEFAULT 'tersedia',
    skor_kelayakan INT NOT NULL DEFAULT 100,
    tampil_homepage BOOLEAN NOT NULL DEFAULT FALSE,
    is_trending BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- 5. UNIT_CATEGORY (pivot many-to-many)
-- =========================================================
CREATE TABLE unit_category (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_uc_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    CONSTRAINT fk_uc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_unit_category (unit_id, category_id)
) ENGINE=InnoDB;

-- =========================================================
-- 6. LOANS (peminjaman)
-- =========================================================
CREATE TABLE loans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    tgl_pinjam DATE NOT NULL,
    tgl_jatuh_tempo DATE NOT NULL,
    tgl_kembali DATE NULL,
    status ENUM('dipinjam','terlambat','kembali') NOT NULL DEFAULT 'dipinjam',
    denda DECIMAL(10,2) NOT NULL DEFAULT 0,
    kondisi_saat_kembali ENUM('baik','perlu_cuci','rusak') NULL,
    catatan TEXT NULL,
    diproses_oleh BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_loans_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_loans_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    CONSTRAINT fk_loans_admin FOREIGN KEY (diproses_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 7. PRICING_LOGS (riwayat dynamic pricing)
-- =========================================================
CREATE TABLE pricing_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NOT NULL,
    harga_lama DECIMAL(10,2) NOT NULL,
    harga_baru DECIMAL(10,2) NOT NULL,
    alasan ENUM('demand_tinggi','demand_rendah','override_admin','musim_wisuda') NOT NULL,
    diubah_oleh BIGINT UNSIGNED NULL,
    is_auto_generated BOOLEAN NOT NULL DEFAULT TRUE,
    status_approval ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pricing_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    CONSTRAINT fk_pricing_admin FOREIGN KEY (diubah_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 8. DEMAND_SNAPSHOT (agregat demand periodik, feed dynamic pricing)
-- =========================================================
CREATE TABLE demand_snapshot (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    periode_mulai DATE NOT NULL,
    periode_selesai DATE NOT NULL,
    jumlah_dipinjam INT NOT NULL DEFAULT 0,
    level_demand ENUM('rendah','sedang','tinggi') NOT NULL DEFAULT 'sedang',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_demand_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    CONSTRAINT fk_demand_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 9. CHAT_SESSIONS (log chatbot budget-matching)
-- =========================================================
CREATE TABLE chat_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    budget_input DECIMAL(10,2) NOT NULL,
    kebutuhan_acara VARCHAR(100) NULL,
    kategori_diminati_id BIGINT UNSIGNED NULL,
    hasil_rekomendasi JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_chat_category FOREIGN KEY (kategori_diminati_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 10. HOMEPAGE_SETTINGS (admin kelola konten halaman user)
-- =========================================================
CREATE TABLE homepage_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(50) NOT NULL UNIQUE, -- contoh: 'hero_banner', 'chatbot_hook_text'
    section_title VARCHAR(150) NULL,
    content TEXT NULL,
    image VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- SEED DATA
-- =========================================================

-- Users (password contoh sudah di-hash dengan bcrypt: "password123")
INSERT INTO users (name, email, password, role) VALUES
('Admin Utama', 'admin@formalwear.id', '$2y$10$examplehashadminpass1234567890abcdefghijk', 'admin'),
('Rangga Pratama', 'rangga@mail.com', '$2y$10$examplehashuserpass1234567890abcdefghijkl', 'anggota'),
('Siti Aisyah', 'siti@mail.com', '$2y$10$examplehashuserpass1234567890abcdefghijkl', 'anggota'),
('Budi Santoso', 'budi@mail.com', '$2y$10$examplehashuserpass1234567890abcdefghijkl', 'anggota');

-- Profiles
INSERT INTO profiles (user_id, no_hp, alamat, ukuran_biasa) VALUES
(2, '081234567890', 'Bandung, Jawa Barat', 'L'),
(3, '081298765432', 'Bandung, Jawa Barat', 'M'),
(4, '081211112222', 'Cimahi, Jawa Barat', 'XL');

-- Categories
INSERT INTO categories (nama_kategori, urutan_tampil) VALUES
('Jas', 1),
('Kebaya', 2),
('Toga', 3),
('Aksesoris', 4);

-- Units
INSERT INTO units (kode_unit, nama_unit, deskripsi, ukuran, warna, harga_dasar, harga_saat_ini, status, tampil_homepage, is_trending) VALUES
('JAS-001', 'Jas Hitam Slimfit', 'Jas formal cocok untuk wisuda & acara resmi', 'L', 'Hitam', 120000, 120000, 'tersedia', TRUE, TRUE),
('JAS-002', 'Jas Hitam Slimfit', 'Jas formal cocok untuk wisuda & acara resmi', 'XL', 'Hitam', 120000, 120000, 'tersedia', TRUE, FALSE),
('KBY-001', 'Kebaya Brokat Putih', 'Kebaya modern elegan untuk wisuda', 'M', 'Putih', 150000, 150000, 'tersedia', TRUE, TRUE),
('KBY-002', 'Kebaya Encim Merah', 'Kebaya tradisional untuk acara formal', 'S', 'Merah', 140000, 140000, 'tersedia', FALSE, FALSE),
('TGA-001', 'Toga S1 Hitam Biru', 'Toga wisuda jenjang Sarjana', 'All Size', 'Hitam Biru', 50000, 50000, 'tersedia', TRUE, TRUE),
('TGA-002', 'Toga S2 Hitam Merah', 'Toga wisuda jenjang Magister', 'All Size', 'Hitam Merah', 60000, 60000, 'tersedia', FALSE, FALSE),
('AKS-001', 'Selempang Wisuda', 'Aksesoris pelengkap toga', 'All Size', 'Kuning Emas', 20000, 20000, 'tersedia', TRUE, FALSE),
('AKS-002', 'Dasi Formal', 'Aksesoris pelengkap jas', 'All Size', 'Hitam', 15000, 15000, 'tersedia', TRUE, FALSE),
('SET-001', 'Paket Jas + Dasi Wisuda', 'Paket lengkap jas dan dasi untuk wisuda', 'L', 'Hitam', 130000, 130000, 'tersedia', TRUE, TRUE);

-- Unit-Category relations (multi-kategori)
INSERT INTO unit_category (unit_id, category_id) VALUES
(1, 1), -- JAS-001 -> Jas
(2, 1), -- JAS-002 -> Jas
(3, 2), -- KBY-001 -> Kebaya
(4, 2), -- KBY-002 -> Kebaya
(5, 3), -- TGA-001 -> Toga
(6, 3), -- TGA-002 -> Toga
(7, 4), -- AKS-001 -> Aksesoris
(8, 4), -- AKS-002 -> Aksesoris
(9, 1), -- SET-001 -> Jas
(9, 3); -- SET-001 -> Toga (multi-kategori)

-- Loans (contoh, termasuk 1 kasus terlambat untuk demo denda)
INSERT INTO loans (user_id, unit_id, tgl_pinjam, tgl_jatuh_tempo, tgl_kembali, status, denda, kondisi_saat_kembali, diproses_oleh) VALUES
(2, 1, '2026-07-01', '2026-07-06', '2026-07-06', 'kembali', 0, 'baik', 1),
(3, 3, '2026-08-01', '2026-08-06', NULL, 'dipinjam', 0, NULL, NULL),
(4, 5, '2026-07-20', '2026-07-25', '2026-07-28', 'kembali', 15000, 'perlu_cuci', 1);

-- Pricing logs (contoh histori dynamic pricing)
INSERT INTO pricing_logs (unit_id, harga_lama, harga_baru, alasan, diubah_oleh, is_auto_generated, status_approval) VALUES
(3, 130000, 150000, 'demand_tinggi', 1, TRUE, 'approved'),
(5, 45000, 50000, 'musim_wisuda', 1, TRUE, 'approved');

-- Demand snapshot (contoh agregat)
INSERT INTO demand_snapshot (unit_id, category_id, periode_mulai, periode_selesai, jumlah_dipinjam, level_demand) VALUES
(3, 2, '2026-07-01', '2026-07-31', 8, 'tinggi'),
(5, 3, '2026-07-01', '2026-07-31', 12, 'tinggi'),
(2, 1, '2026-07-01', '2026-07-31', 2, 'rendah');

-- Chat sessions (contoh log chatbot)
INSERT INTO chat_sessions (user_id, budget_input, kebutuhan_acara, kategori_diminati_id, hasil_rekomendasi) VALUES
(2, 150000, 'wisuda', 1, '{"rekomendasi": ["JAS-001", "AKS-002"]}'),
(3, 100000, 'wisuda', 3, '{"rekomendasi": ["TGA-001", "AKS-001"]}');

-- Homepage settings (dikelola admin)
INSERT INTO homepage_settings (section_key, section_title, content, is_active) VALUES
('hero_banner', 'Sewa Formal Wear, Gak Perlu Beli Baru', 'Sewa jas, kebaya, dan toga untuk wisuda & acara formal dengan mudah dan hemat.', TRUE),
('chatbot_hook_text', 'Hook Chatbot', 'Bingung sesuain budget? Chat aja 👗', TRUE),
('trending_section_title', 'Produk Paling Laris', 'Unit yang lagi banyak disewa minggu ini', TRUE),
('budget_section_title', 'Rekomendasi Sesuai Budget', 'Pilih formal wear sesuai kantong kamu', TRUE);
