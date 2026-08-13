# PROMPT REAKSI / IMPLEMENTASI UNTUK AI (GEMINI)

Saya memiliki proyek PHP bernama "FormalWear (Functional Elegance)" — sebuah aplikasi sewa pakaian formal. Tolong implementasikan fitur CRUD Katalog di Admin Panel.

---

## 🏗️ TECH STACK PROYEK
- Backend: PHP (vanilla, tanpa framework)
- Database: MySQL via PDO, database name: `formalwear_schema`
- Frontend: HTML + Vanilla CSS + Vanilla JS (ES6+)
- Server: PHP Built-in / XAMPP (php -S localhost:8000)
- Auth: Session-based (`$_SESSION['user']`)

---

## 📁 STRUKTUR FILE YANG RELEVAN

```
FormalWear/
├── admin.php           # File yang harus dimodifikasi (Admin Panel utama)
├── katalog.php         # Halaman katalog publik — harus membaca dari MySQL tabel `units`
├── config.php          # Koneksi PDO MySQL via get_db_connection()
├── assets/css/style.css
└── db/
    └── formalwear_schema.sql   # Schema MySQL referensi
```

---

## 🗄️ SKEMA DATABASE TABEL YANG RELEVAN

**Tabel `units`** (katalog produk):
```sql
CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_unit VARCHAR(30) NOT NULL UNIQUE,
    nama_unit VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    ukuran ENUM('XS','S','M','L','XL','XXL','All Size') NOT NULL DEFAULT 'All Size',
    warna VARCHAR(50) NULL,
    foto VARCHAR(255) NULL,          -- path relatif ke file gambar, contoh: assets/catalog_tux1.png
    harga_dasar DECIMAL(10,2) NOT NULL,
    harga_saat_ini DECIMAL(10,2) NOT NULL,
    status ENUM('tersedia','dipinjam','maintenance') NOT NULL DEFAULT 'tersedia',
    skor_kelayakan INT NOT NULL DEFAULT 100,
    tampil_homepage BOOLEAN NOT NULL DEFAULT FALSE,
    is_trending BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

**Tabel `categories`**:
```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    urutan_tampil INT NOT NULL DEFAULT 0,
    tampil_navbar BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- Seed: ('Jas', 1), ('Kebaya', 2), ('Toga', 3), ('Aksesoris', 4)
```

**Tabel `unit_category`** (pivot many-to-many):
```sql
CREATE TABLE unit_category (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NOT NULL REFERENCES units(id) ON DELETE CASCADE,
    category_id BIGINT UNSIGNED NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_unit_category (unit_id, category_id)
) ENGINE=InnoDB;
```

---

## 🔑 FUNGSI HELPER PENTING DI config.php

```php
// Koneksi PDO MySQL
function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=127.0.0.1;dbname=formalwear_schema;charset=utf8mb4";
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// Auth Helpers
function is_logged_in() { return isset($_SESSION['user']); }
function is_admin() { return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true; }
function sanitize_input($data) { return htmlspecialchars(stripslashes(trim($data))); }
```

---

## 🎨 CSS DESIGN TOKENS (dari assets/css/style.css)

```css
:root {
  --bg-primary: #FCF9F4;
  --bg-secondary: #F6F3EE;
  --brand-primary: #5B0617;
  --brand-hover: #420410;
  --gold-accent: #D4AF37;
  --gold-text: #735C00;
  --text-dark: #1C1C19;
  --text-body: #2B2B2B;
  --text-muted: #564242;
  --border-light: #E5E1DA;
  --error-color: #B3261E;
  --font-serif: 'Playfair Display', Georgia, serif;
  --font-sans: 'Inter', system-ui, sans-serif;
}
```

---

## ✅ TUGAS YANG HARUS DIIMPLEMENTASIKAN

### 1. Modifikasi `admin.php` — Tambah Tab "Manajemen Katalog"

Di dalam `admin.php`, tambahkan tab baru bernama **"Katalog"** di sidebar admin. Tab ini harus memiliki fitur CRUD lengkap:

#### A. **READ** — Tabel Daftar Unit Katalog
- Ambil semua data dari tabel `units` menggunakan `get_db_connection()`
- JOIN dengan `unit_category` dan `categories` untuk menampilkan nama kategori
- Tampilkan dalam tabel HTML dengan kolom:  
  `No | Foto | Kode Unit | Nama Unit | Kategori | Ukuran | Warna | Harga Dasar | Harga Saat Ini | Status | Trending | Aksi`
- Beri badge warna untuk status: hijau=tersedia, merah=dipinjam, abu=maintenance

#### B. **CREATE** — Form Tambah Produk Baru
- Tampilkan form modal/inline dengan field:
  - Kode Unit (text, required, unique)
  - Nama Unit (text, required)
  - Deskripsi (textarea)
  - Ukuran (select: XS/S/M/L/XL/XXL/All Size)
  - Warna (text)
  - URL/Path Foto (text, contoh: assets/catalog_tux1.png)
  - Harga Dasar (number, required)
  - Status (select: tersedia/dipinjam/maintenance)
  - Kategori (checkbox multiple dari tabel `categories`)
  - Tampil di Homepage (checkbox)
  - Is Trending (checkbox)
- Simpan ke tabel `units`, lalu INSERT relasi ke `unit_category`

#### C. **UPDATE** — Form Edit Produk
- Tombol "Edit" di tiap baris tabel membuka modal pre-filled dengan data produk
- Field yang bisa diubah: semua field di atas kecuali `id`
- Gunakan UPDATE SQL: `UPDATE units SET ... WHERE id = ?`
- Update juga relasi `unit_category`: DELETE lama, INSERT baru

#### D. **DELETE** — Hapus Produk
- Tombol "Hapus" dengan konfirmasi JavaScript `confirm()`
- DELETE dari tabel `units` (CASCADE akan hapus `unit_category` otomatis)
- Tampilkan flash message sukses setelah operasi

#### E. **TOGGLE STATUS** — Ubah Status Cepat
- Tombol toggle kecil di kolom Status untuk ganti antara `tersedia` ↔ `dipinjam`

---

### 2. Modifikasi `katalog.php` — Baca Data dari MySQL `units`

Saat ini `katalog.php` membaca dari `data.json`. Ubah agar membaca dari tabel MySQL `units`:

```php
// Ganti ini:
$data = get_db_data();
$catalog_items = $data['katalog'];

// Menjadi ini (query MySQL):
$pdo = get_db_connection();
$stmt = $pdo->query("
    SELECT u.*, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS kategori_nama
    FROM units u
    LEFT JOIN unit_category uc ON u.id = uc.unit_id
    LEFT JOIN categories c ON uc.category_id = c.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$catalog_items = $stmt->fetchAll();
```

Sesuaikan juga key nama field di HTML katalog.php:
- Lama: `$item['code']` → Baru: `$item['kode_unit']`
- Lama: `$item['name']` → Baru: `$item['nama_unit']`
- Lama: `$item['category']` → Baru: `$item['kategori_nama']`
- Lama: `$item['size']` → Baru: `$item['ukuran']`
- Lama: `$item['price']` → Baru: `$item['harga_saat_ini']`
- Lama: `$item['base_price']` → Baru: `$item['harga_dasar']`
- Lama: `$item['status']` → Baru: `$item['status']` (sama, tapi value lowercase: tersedia/dipinjam)
- Lama: `$item['image']` → Baru: `$item['foto']`

---

## 📐 STYLE & UX RULES

- Gunakan CSS Variables dari design tokens di atas (JANGAN inline hex langsung)
- Desain modal menggunakan glassmorphism / overlay gelap
- Flash message sukses/error muncul di atas konten tab dengan animasi fade
- Semua form menggunakan `sanitize_input()` sebelum disimpan ke DB
- Gunakan Prepared Statement PDO (?) untuk semua query INSERT/UPDATE/DELETE
- Sidebar admin sudah ada di admin.php, cukup tambahkan tab baru di dalam struktur yang sudah ada
- Kode PHP CRUD ditulis di bagian atas admin.php (sebelum HTML), di dalam blok `if ($_SERVER['REQUEST_METHOD'] === 'POST')`

---

## ⚠️ PENTING

1. Jangan hapus tab/fitur yang sudah ada di admin.php (tab Dashboard, Beranda, Dynamic Pricing, Users)
2. Jangan gunakan framework PHP (Laravel, Slim, dll)
3. Jangan gunakan Tailwind CSS — gunakan Vanilla CSS dengan CSS Variables yang sudah ada
4. Semua operasi DB harus menggunakan PDO Prepared Statement untuk keamanan SQL Injection
5. Harga di tabel `units` menggunakan field `harga_saat_ini` (bukan `price`)
