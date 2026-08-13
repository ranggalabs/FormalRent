# PROMPT: IMPLEMENTASI FITUR SOFT DELETE DI SEMUA FITUR ADMIN PANEL

Saya memiliki proyek PHP bernama "FormalWear (Functional Elegance)".
Tolong implementasikan fitur **Soft Delete** di Admin Panel — menghapus data berarti
menonaktifkan data (bukan menghapus permanen), dan data yang tidak aktif tidak muncul
di halaman publik `katalog.php`.

---

## 🏗️ TECH STACK
- Backend: PHP vanilla, tanpa framework
- Database: MySQL via PDO, database: `formalwear_schema`
- Frontend: HTML + Vanilla CSS + Vanilla JS
- Server: php -S localhost:8000
- Auth: Session-based (`$_SESSION['user']`, `$_SESSION['admin_logged_in']`)

---

## 📁 FILE YANG HARUS DIMODIFIKASI

```
FormalWear/
├── admin.php        # Fitur CRUD Katalog + User — tambah kolom "Aktif" & ubah delete ke soft delete
├── katalog.php      # Harus memfilter hanya unit dengan is_active = 1
├── config.php       # Koneksi PDO, helper functions
└── db/
    └── formalwear_schema.sql   # Referensi schema
```

---

## 🗄️ SCHEMA DATABASE YANG RELEVAN

### Tabel `units` (saat ini — tidak ada kolom is_active):
```sql
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
```

### Tabel `users` (sudah ada kolom is_active):
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'anggota') NOT NULL DEFAULT 'anggota',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,  -- ← sudah ada!
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

---

## 🔑 SQL YANG HARUS DIJALANKAN DI phpMyAdmin

**WAJIB dijalankan terlebih dahulu sebelum implementasi PHP:**

```sql
-- 1. Tambahkan kolom is_active ke tabel units (soft delete untuk katalog)
ALTER TABLE units
ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE AFTER is_trending;

-- 2. Pastikan semua unit yang sudah ada ter-set aktif
UPDATE units SET is_active = TRUE WHERE is_active IS NULL;

-- 3. (Opsional) Tambahkan index untuk performa query filter
CREATE INDEX idx_units_is_active ON units (is_active);
```

> Tabel `users` sudah memiliki kolom `is_active`, sehingga **tidak perlu ALTER**.
> Namun admin.php saat ini menyimpan users di `db/data.json`, bukan MySQL.
> Soft delete users perlu diimplementasikan di level JSON juga.

---

## ✅ DAFTAR PERUBAHAN LENGKAP PER FILE

---

### 1. `admin.php`

#### A. TAB KATALOG — Ubah Action `delete_katalog` menjadi Soft Delete

**Sebelum** (baris ~165–182):
```php
if ($action === 'delete_katalog') {
    $id = intval($_POST['item_id']);
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("DELETE FROM units WHERE id = ?");
    $stmt->execute([$id]);
    // ...
}
```

**Sesudah** (soft delete via is_active = 0):
```php
if ($action === 'delete_katalog') {
    $id = intval($_POST['item_id']);
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE units SET is_active = FALSE WHERE id = ?");
        $stmt->execute([$id]);
        $flash_msg = 'Produk berhasil dinonaktifkan!';
    } catch (PDOException $e) {
        $flash_msg = 'Gagal menonaktifkan produk: ' . $e->getMessage();
    }
    $active_tab = 'katalog';
}
```

#### B. TAB KATALOG — Tambah Action `activate_katalog` (reaktivasi)

```php
if ($action === 'activate_katalog') {
    $id = intval($_POST['item_id']);
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE units SET is_active = TRUE WHERE id = ?");
        $stmt->execute([$id]);
        $flash_msg = 'Produk berhasil diaktifkan kembali!';
    } catch (PDOException $e) {
        $flash_msg = 'Gagal mengaktifkan produk: ' . $e->getMessage();
    }
    $active_tab = 'katalog';
}
```

#### C. TAB KATALOG — Query Fetch Katalog: tampilkan SEMUA unit (aktif & tidak aktif) di admin

Di bagian PHP admin yang mengambil data katalog dari MySQL, query harus menampilkan
**semua unit termasuk yang is_active = FALSE**, agar admin bisa melihat dan mengaktifkan kembali:

```php
// Di admin.php — fetch catalog (tampilkan semua termasuk non-aktif)
$stmt = $pdo->query("
    SELECT u.*, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS kategori_nama
    FROM units u
    LEFT JOIN unit_category uc ON u.id = uc.unit_id
    LEFT JOIN categories c ON uc.category_id = c.id
    GROUP BY u.id
    ORDER BY u.is_active DESC, u.created_at DESC
");
```

#### D. TAB KATALOG — Tambah Kolom `Aktif` di Tabel HTML

Di tabel daftar produk katalog admin, tambahkan kolom baru **"Aktif"** antara kolom
"Status" dan "Aksi":

```html
<th>Aktif</th>
```

Di setiap baris `<tr>`, tampilkan nilai `is_active`:
```php
<td>
  <span class="status-badge <?php echo $item['is_active'] ? 'status-available' : 'status-borrowed'; ?>">
    <?php echo $item['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
  </span>
</td>
```

#### E. TAB KATALOG — Ubah Tombol "Hapus" menjadi Toggle Aktif/Nonaktif

Ganti tombol "Hapus" di setiap baris tabel dengan tombol yang menampilkan:
- **"Nonaktifkan"** jika `is_active = 1` (warna merah/error)
- **"Aktifkan"** jika `is_active = 0` (warna hijau)

```html
<?php if ($item['is_active']): ?>
  <form method="POST" action="admin.php?tab=katalog"
        onsubmit="return confirm('Nonaktifkan produk ini? Data tidak akan dihapus.');">
    <input type="hidden" name="action" value="delete_katalog">
    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
    <button type="submit" class="btn-action-small btn-delete">Nonaktifkan</button>
  </form>
<?php else: ?>
  <form method="POST" action="admin.php?tab=katalog">
    <input type="hidden" name="action" value="activate_katalog">
    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
    <button type="submit" class="btn-action-small btn-toggle">Aktifkan</button>
  </form>
<?php endif; ?>
```

#### F. TAB KATALOG — Tambah Filter Tab "Aktif / Semua" (Opsional tapi Direkomendasikan)

Tambahkan filter kecil di atas tabel:
```html
<div style="display:flex; gap:8px; margin-bottom:12px;">
  <a href="admin.php?tab=katalog&show=all"   class="btn-action-small <?php echo (!isset($_GET['show']) || $_GET['show']==='all') ? 'btn-toggle' : ''; ?>">Semua</a>
  <a href="admin.php?tab=katalog&show=aktif" class="btn-action-small <?php echo (isset($_GET['show']) && $_GET['show']==='aktif') ? 'btn-toggle' : ''; ?>">Aktif Saja</a>
  <a href="admin.php?tab=katalog&show=nonaktif" class="btn-action-small <?php echo (isset($_GET['show']) && $_GET['show']==='nonaktif') ? 'btn-delete' : ''; ?>">Tidak Aktif</a>
</div>
```

Sesuaikan query fetch di admin dengan filter:
```php
$show_filter = isset($_GET['show']) ? $_GET['show'] : 'all';
$where_clause = '';
if ($show_filter === 'aktif')    $where_clause = 'WHERE u.is_active = 1';
if ($show_filter === 'nonaktif') $where_clause = 'WHERE u.is_active = 0';

$stmt = $pdo->query("
    SELECT u.*, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS kategori_nama
    FROM units u
    LEFT JOIN unit_category uc ON u.id = uc.unit_id
    LEFT JOIN categories c ON uc.category_id = c.id
    $where_clause
    GROUP BY u.id
    ORDER BY u.is_active DESC, u.created_at DESC
");
```

#### G. TAB USER — Soft Delete Users (JSON-based)

Di `data.json`, setiap user sudah memiliki field `status` (Aktif/Suspended).
Ubah action `delete_user` menjadi soft delete — set `status = 'Tidak Aktif'`:

**Sebelum:**
```php
if ($action === 'delete_user') {
    $data['users'] = array_values(array_filter($data['users'], fn($u) => $u['id'] !== $id));
    save_db_data($data);
}
```

**Sesudah:**
```php
if ($action === 'delete_user') {
    $id = intval($_POST['user_id']);
    foreach ($data['users'] as &$u) {
        if ($u['id'] === $id) {
            $u['status'] = 'Tidak Aktif';
            break;
        }
    }
    save_db_data($data);
    $flash_msg = 'User berhasil dinonaktifkan!';
    $active_tab = 'users';
}
```

Tambahkan juga action `activate_user`:
```php
if ($action === 'activate_user') {
    $id = intval($_POST['user_id']);
    foreach ($data['users'] as &$u) {
        if ($u['id'] === $id) {
            $u['status'] = 'Aktif';
            break;
        }
    }
    save_db_data($data);
    $flash_msg = 'User berhasil diaktifkan kembali!';
    $active_tab = 'users';
}
```

#### H. TAB USER — Tambah Kolom "Aktif" di Tabel HTML

Di tabel user, tambahkan kolom "Aktif" yang menampilkan badge:
```html
<th>Aktif</th>
```
```php
<td>
  <span class="status-badge <?php echo $u['status'] === 'Aktif' ? 'status-available' : 'status-borrowed'; ?>">
    <?php echo $u['status'] === 'Aktif' ? 'Aktif' : 'Tidak Aktif'; ?>
  </span>
</td>
```

Ubah tombol "Hapus" user menjadi toggle seperti katalog:
```html
<?php if ($u['status'] === 'Aktif'): ?>
  <form method="POST" action="admin.php?tab=users"
        onsubmit="return confirm('Nonaktifkan user ini?');">
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
    <button type="submit" class="btn-action-small btn-delete">Nonaktifkan</button>
  </form>
<?php else: ?>
  <form method="POST" action="admin.php?tab=users">
    <input type="hidden" name="action" value="activate_user">
    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
    <button type="submit" class="btn-action-small btn-toggle">Aktifkan</button>
  </form>
<?php endif; ?>
```

---

### 2. `katalog.php`

#### A. Filter Query MySQL — Hanya Tampilkan Unit yang `is_active = 1`

**Sebelum:**
```php
$stmt = $pdo->query("
    SELECT u.*, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS kategori_nama
    FROM units u
    LEFT JOIN unit_category uc ON u.id = uc.unit_id
    LEFT JOIN categories c ON uc.category_id = c.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
```

**Sesudah (tambahkan WHERE u.is_active = 1):**
```php
$stmt = $pdo->query("
    SELECT u.*, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS kategori_nama
    FROM units u
    LEFT JOIN unit_category uc ON u.id = uc.unit_id
    LEFT JOIN categories c ON uc.category_id = c.id
    WHERE u.is_active = 1
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
```

---

## ⚠️ URUTAN IMPLEMENTASI YANG BENAR

1. **PERTAMA** — Jalankan SQL ALTER TABLE di phpMyAdmin:
   ```sql
   ALTER TABLE units ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE AFTER is_trending;
   UPDATE units SET is_active = TRUE;
   CREATE INDEX idx_units_is_active ON units (is_active);
   ```

2. **KEDUA** — Edit `katalog.php`: tambahkan `WHERE u.is_active = 1` pada query.

3. **KETIGA** — Edit `admin.php`:
   - Ubah action `delete_katalog` → soft delete (UPDATE SET is_active = FALSE)
   - Tambah action `activate_katalog`
   - Tambah kolom "Aktif" + badge di tabel katalog
   - Ubah tombol Hapus → tombol toggle Nonaktifkan/Aktifkan
   - Ubah action `delete_user` → soft delete (set status = 'Tidak Aktif')
   - Tambah action `activate_user`
   - Tambah kolom "Aktif" + badge di tabel user
   - Ubah tombol Hapus user → tombol toggle Nonaktifkan/Aktifkan

4. **KEEMPAT** — Hapus juga baris kode yang memanggil `recalculate_catalog_dynamic_prices`
   dan `$data['dynamic_pricing']` di handler `add_katalog` dan `edit_katalog` jika masih ada
   (fitur Dynamic Pricing sudah dihapus sebelumnya).

---

## ⚠️ ATURAN PENTING

1. Jangan gunakan `DELETE FROM units` — ganti semua dengan `UPDATE units SET is_active = FALSE`.
2. Data yang di-nonaktifkan harus **tidak muncul** di `katalog.php` (publik) tapi **tetap muncul**
   di `admin.php` agar bisa diaktifkan kembali.
3. Jangan gunakan framework PHP (Laravel, dll) atau Tailwind CSS.
4. Gunakan PDO Prepared Statement untuk semua query MySQL.
5. Pertahankan semua fitur lain yang sudah ada: Edit, Ubah Status (tersedia/dipinjam), Add User, dll.
6. Tidak ada PHP error maupun undefined variable.
