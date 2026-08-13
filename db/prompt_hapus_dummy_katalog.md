# PROMPT: HAPUS DATA DUMMY KATALOG DARI PROYEK

Saya memiliki proyek PHP "FormalWear (Functional Elegance)". 
Halaman http://localhost:8000/admin.php?tab=katalog dan 
http://localhost:8000/katalog.php masih menampilkan data dummy 
katalog produk. Tolong hapus semua data dummy tersebut.

---

## 🔍 SUMBER DATA DUMMY

Data katalog berasal dari DUA sumber yang harus dihapus:

### 1. File `db/data.json` — key `"katalog"` (baris 9–93)

Array `katalog` berisi 7 item dummy berikut yang harus dihapus semua:
- id:1  — TUX-001 "Midnight Velvet Tuxedo"
- id:2  — SUI-042 "Navy Royale Slim Fit"
- id:3  — BTK-108 "Sogan Keraton Silk"
- id:4  — SUI-088 "Ivory Summer Double-Breasted"
- id:5  — TUX-002 "Black Tie Peak Lapel Tuxedo"
- id:6  — KBY-015 "Modern Ivory Lace Kebaya"
- id:10 — TUX-0081 "Royal CONTOH123"

**Ubah** array `"katalog"` menjadi array kosong:
```json
"katalog": []
```

### 2. MySQL Database `formalwear_schema` — tabel `units`

Tabel `units` kemungkinan juga berisi data dummy 
(yang di-insert saat setup awal atau via admin panel).

Jalankan query SQL berikut untuk mengosongkan tabel `units` 
dan relasi `unit_category`:
```sql
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE unit_category;
TRUNCATE TABLE units;
SET FOREIGN_KEY_CHECKS = 1;
```

---

## 📁 FILE YANG HARUS DIMODIFIKASI

```
FormalWear/
├── db/data.json       → Kosongkan array "katalog" menjadi []
```

Dan eksekusi SQL di phpMyAdmin atau via PHP script 
pada database `formalwear_schema`.

---

## 🔑 KONEKSI DATABASE

```php
// Kredensial dari config.php:
DB_HOST = '127.0.0.1'
DB_USER = 'root'
DB_PASS = ''
DB_NAME = 'formalwear_schema'
```

---

## ✅ LANGKAH IMPLEMENTASI

1. **Edit `db/data.json`**: ubah `"katalog": [... 7 item ...]` 
   menjadi `"katalog": []`

2. **Kosongkan tabel MySQL**: buat file sementara 
   `db/clear_dummy.php` dengan konten:
   ```php
   <?php
   require_once __DIR__ . '/../config.php';
   $pdo = get_db_connection();
   $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
   $pdo->exec("TRUNCATE TABLE unit_category");
   $pdo->exec("TRUNCATE TABLE units");
   $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
   echo "✅ Semua data dummy katalog berhasil dihapus dari database.";
   ```
   Akses file ini sekali via browser: 
   `http://localhost:8000/db/clear_dummy.php`
   Lalu **hapus file tersebut** setelah dijalankan.

---

## ⚠️ CATATAN PENTING

- Jangan hapus tabel `categories` — berisi data master kategori 
  yang masih dibutuhkan.
- Jangan hapus tabel `users` — berisi akun pengguna yang sudah login.
- Setelah dummy dihapus, halaman `katalog.php` seharusnya menampilkan 
  pesan kosong atau grid kosong (tidak ada error PHP).
- Halaman `admin.php?tab=katalog` seharusnya menampilkan tabel 
  "0 Unit" dan form tambah produk tetap berfungsi.
- Jangan gunakan framework PHP atau Tailwind CSS.
