# 🎓 FormalWear (FormalRent) — Web Penyewaan Busana Formal

> **FormalWear (Functional Elegance)** adalah platform aplikasi web penyewaan busana formal (jas, tuxedo, kebaya, toga wisuda, dan batik sutra) yang memadukan arsitektur pengelolaan data yang responsif, hak akses multi-role, validasi input ketat, dan kalkulasi harga dinamis berbasis musim (*Dynamic Pricing Engine*).

---

## 👥 Informasi Kelompok & Tim

* **Nama Kelompok**: Bansos AI
* **Nama Team**:
  1. **Rangga Prasetya** — *Lead Developer / Fullstack*
  2. **Anggota Tim Bansos AI** — *UI/UX & Database Architecture*
* **Nama Project**: FormalWear (FormalRent)

---

## 📽️ Demo Penjelasan & Skema Database

### 1. Screen Recorder Demo Penjelasan Website
* 🎬 **Link Video Demo**: [Sematkan Link Video Record Demo Website / YouTube / Loom Di Sini]

### 2. Screen Capture & Skema Database Project
Berikut adalah diagram konseptual dan relasi antartabel database proyek:

```
 +------------------+         1 : 1         +-------------------+
 |      USERS       |-----------------------|     PROFILES      |
 | (admin / anggota)|                       | (no_hp, alamat)   |
 +------------------+                       +-------------------+
          |                                           |
          | 1 : N                                     | (kategori favorit)
          v                                           v
 +------------------+         1 : N         +-------------------+
 |      LOANS       |---------------------->|       UNITS       |
 |(max 5 hari,denda)|                       |(kode_unit UNIQUE) |
 +------------------+                       +-------------------+
          ^                                           | N
          | diproses_oleh (admin)                     |
          +-----------------------------+             v N
                                            +-------------------+
                                            |   UNIT_CATEGORY   |
                                            |  (multi-kategori) |
                                            +-------------------+
                                                      ^ N
                                                      |
                                                      | 1
                                            +-------------------+
                                            |    CATEGORIES     |
                                            |   (nama_kategori) |
                                            +-------------------+
```

* 📄 **File Skema SQL Migrasi**: [`db/formalwear_schema.sql`](file:///C:/Users/Rangga%20Prasetya/Documents/FormalWear/db/formalwear_schema.sql)
* 📄 **File DFD Level 1 & Relasi Data**: [`db/dfd_level_1.md`](file:///C:/Users/Rangga%20Prasetya/Documents/FormalWear/db/dfd_level_1.md)

---

## 📜 Requirements & Aturan Bisnis Project

1. **Hak Akses & Pengguna (2 Jenis Role)**:
   - Terdapat 2 jenis pengguna: **Admin** dan **User (Anggota)**.
   - Setiap pengguna **harus melakukan Login** untuk dapat mengakses dan mengoperasikan web.
   - User harus terdaftar sebagai anggota resmi untuk meminjam/menyewa unit.

2. **Manajemen Profil**:
   - Satu user **hanya dapat memiliki satu profile** (Relasi 1:1 antara tabel `users` dan `profiles`).
   - Setiap user dapat **mengubah data profilnya masing-masing** (Nomor HP, Alamat, Ukuran Biasa).

3. **Manajemen Unit & Kategori**:
   - Setiap unit dapat memiliki **multiple kategori** (Relasi Many-to-Many via `unit_category`).
   - Nama unit dapat berjumlah lebih dari 1 dan dibedakan melalui **Kode Unit** (`kode_unit`).
   - **Kode Unit tidak boleh sama (Harus UNIQUE)** (misal: `JAS-001`, `JAS-002`).
   - Tersedia fitur **pencarian unit** berdasarkan nama unit maupun filter kategori.

4. **Kewenangan Admin (CRUD Penuh)**:
   - Admin dapat **menambah** data unit, kategori, dan user (anggota).
   - Admin dapat **mengupdate** data unit, kategori, dan user (anggota).
   - Admin dapat **menghapus** data unit, kategori, dan user (anggota).

5. **Aturan Peminjaman & Pengembalian Unit**:
   - Setiap Anggota hanya dapat meminjam **maksimal 2 unit** secara bersamaan.
   - Durasi peminjaman **maksimal 5 hari**. Jika pinjaman lebih dari 5 hari, sistem secara otomatis mengenakan **denda keterlambatan**.
   - **Hanya Admin yang dapat melakukan proses pengembalian unit**. Setiap anggota yang ingin mengembalikan unit harus menghubungi admin.
   - Admin dapat melihat **seluruh list unit yang dipinjam** oleh semua anggota.
   - User (anggota) **hanya dapat melihat list unit yang dipinjam oleh dirinya sendiri**.
   - Admin dapat **melihat dan mencetak riwayat peminjaman unit** dari seluruh user (anggota).

6. **Validasi Input Field**:
   - Seluruh formulir (*login, register, tambah/edit unit, tambah/edit kategori, tambah/edit profile*) dilengkapi validasi formulir wajib isi (**`required`**) serta sanitasi input backend.

---

## ⚡ Fitur Utama Aplikasi

- 🔐 **Autentikasi & Autorisasi Role** (`login.php`, `register.php`, `logout.php`, `forgot-password.php`)
- 👤 **Manajemen Profil User** (`dashboard.php`)
- 👗 **Katalog Unit & Filter Multi-Kategori** (`katalog.php`)
- 🔍 **Pencarian Unit Berbasis Kode & Nama Unit** (`katalog.php`)
- 🛠️ **Panel Kontrol CRUD Admin** (`admin.php`)
- 📦 **Sistem Transaksi Peminjaman (Max 2 Unit & Max 5 Hari)** (`config.php`, `admin.php`)
- 💰 **Perhitungan Denda Keterlambatan Otomatis** (`config.php`)
- 🖨️ **Cetak Riwayat Peminjaman Anggota** (`admin.php`)
- 💬 **Chatbot Assistant Fitting & Budget-Matching** (`chatbot_modal.php`)
- 📈 **Dynamic Pricing Engine Berbasis Musim** (`config.php`)

---

## 🗄️ Tabel Migration & Struktur Database

Tabel migrasi telah disesuaikan dengan seluruh kebutuhan requirement proyek:

```sql
-- 1. Tabel Users (Admin & Anggota)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'anggota') NOT NULL DEFAULT 'anggota',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Tabel Profiles (1 User = 1 Profile)
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
);

-- 3. Tabel Categories
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    urutan_tampil INT NOT NULL DEFAULT 0,
    tampil_navbar BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Tabel Units (Kode Unit UNIQUE)
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
);

-- 5. Tabel Unit_Category (Pivot Multiple Kategori per Unit)
CREATE TABLE unit_category (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_uc_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    CONSTRAINT fk_uc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_unit_category (unit_id, category_id)
);

-- 6. Tabel Loans (Peminjaman, Denda & Approval Admin)
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
);
```

---

## 🌱 Data Seed Awal Project

Data seed awal disiapkan untuk mempermudah pengujian awal aplikasi:

```sql
-- 1. Data User Seed (Admin & Anggota)
INSERT INTO users (name, email, password, role) VALUES
('Admin Utama', 'admin@formalwear.id', '$2y$10$examplehashadminpass1234567890abcdefghijk', 'admin'),
('Rangga Pratama', 'rangga@mail.com', '$2y$10$examplehashuserpass1234567890abcdefghijkl', 'anggota'),
('Siti Aisyah', 'siti@mail.com', '$2y$10$examplehashuserpass1234567890abcdefghijkl', 'anggota'),
('Budi Santoso', 'budi@mail.com', '$2y$10$examplehashuserpass1234567890abcdefghijkl', 'anggota');

-- 2. Data Profile Seed
INSERT INTO profiles (user_id, no_hp, alamat, ukuran_biasa) VALUES
(2, '081234567890', 'Bandung, Jawa Barat', 'L'),
(3, '081298765432', 'Bandung, Jawa Barat', 'M'),
(4, '081211112222', 'Cimahi, Jawa Barat', 'XL');

-- 3. Data Kategori Seed
INSERT INTO categories (nama_kategori, urutan_tampil) VALUES
('Jas', 1),
('Kebaya', 2),
('Toga', 3),
('Aksesoris', 4);

-- 4. Data Unit Seed (Dengan Kode Unit Unik)
INSERT INTO units (kode_unit, nama_unit, deskripsi, ukuran, warna, harga_dasar, harga_saat_ini, status) VALUES
('JAS-001', 'Jas Hitam Slimfit', 'Jas formal cocok untuk wisuda & acara resmi', 'L', 'Hitam', 120000, 120000, 'tersedia'),
('JAS-002', 'Jas Hitam Slimfit', 'Jas formal cocok untuk wisuda & acara resmi', 'XL', 'Hitam', 120000, 120000, 'tersedia'),
('KBY-001', 'Kebaya Brokat Putih', 'Kebaya modern elegan untuk wisuda', 'M', 'Putih', 150000, 150000, 'tersedia'),
('KBY-002', 'Kebaya Encim Merah', 'Kebaya tradisional untuk acara formal', 'S', 'Merah', 140000, 140000, 'tersedia'),
('TGA-001', 'Toga S1 Hitam Biru', 'Toga wisuda jenjang Sarjana', 'All Size', 'Hitam Biru', 50000, 50000, 'tersedia'),
('AKS-001', 'Selempang Wisuda', 'Aksesoris pelengkap toga', 'All Size', 'Kuning Emas', 20000, 20000, 'tersedia'),
('SET-001', 'Paket Jas + Dasi Wisuda', 'Paket lengkap jas dan dasi untuk wisuda', 'L', 'Hitam', 130000, 130000, 'tersedia');

-- 5. Data Multiple Kategori Seed (Pivot Unit_Category)
INSERT INTO unit_category (unit_id, category_id) VALUES
(1, 1), -- JAS-001 -> Kategori Jas
(2, 1), -- JAS-002 -> Kategori Jas
(3, 2), -- KBY-001 -> Kategori Kebaya
(4, 2), -- KBY-002 -> Kategori Kebaya
(5, 3), -- TGA-001 -> Kategori Toga
(6, 4), -- AKS-001 -> Kategori Aksesoris
(7, 1), -- SET-001 -> Multi-kategori: Jas
(7, 3); -- SET-001 -> Multi-kategori: Toga
```

---

## 🚀 Cara Menjalankan Proyek Secara Lokal

Proyek dapat dijalankan langsung menggunakan **PHP Built-in Server**:

```bash
# 1. Jalankan perintah dari root folder FormalWear
php -S localhost:8000

# 2. Akses aplikasi dari browser
http://localhost:8000
```

### Kredensial Pengujian Default:
* **Admin**: `admin@formalwear.id` | Password: `password123`
* **Anggota**: `rangga@mail.com` | Password: `password123`
