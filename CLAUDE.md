# CLAUDE.md - FormalWear Project Guide

## 📌 Deskripsi Proyek
**FormalWear (Functional Elegance)** adalah aplikasi web katalog & penyewaan pakaian formal (jas, tuxedo, kebaya, batik sutra) berbasis **PHP** tanpa *database* SQL eksternal. Seluruh data disimpan dan dikelola dalam file data JSON (`db/data.json`). Sistem ini juga dilengkapi dengan fitur **Dynamic Pricing Engine** untuk penyesuaian harga otomatis berdasarkan musiman (wisuda, pernikahan, off-season).

---

## 🚀 Perintah Build, Run & Test

### 1. Menjalankan Server Lokal (Development)
Proyek ini dapat dijalankan menggunakan **PHP Built-in Server** tanpa memerlukan Apache/XAMPP:

```bash
# Jalankan server dari root folder proyek
php -S localhost:8000
```
Akses di browser melalui: `http://localhost:8000`

### 2. Menjalankan dengan XAMPP
Jika menggunakan XAMPP:
1. Pindahkan folder proyek ke `C:\xampp\htdocs\FormalWear`
2. Buka XAMPP Control Panel dan jalankan **Apache**
3. Akses via browser: `http://localhost/FormalWear`

### 3. Pengujian & Linting Kode
- **PHP Syntax Check**:
  ```bash
  php -l index.php
  php -l config.php
  php -l admin.php
  ```
- **Validasi Data JSON**:
  Pastikan integritas file `db/data.json` terjaga dan memiliki sintaks JSON yang valid saat melakukan pengujian manipulasi data.

---

## 📁 Struktur Proyek

```
FormalWear/
├── admin.php             # Dashboard admin (Kelola beranda, katalog, user, & dynamic pricing)
├── chatbot_modal.php     # Komponen modal chatbot interaktif (konsultasi ukuran & sewa)
├── config.php            # Konfigurasi aplikasi, helper data store JSON, & kalkulasi dynamic pricing
├── dashboard.php         # Dashboard / Profil user terdaftar
├── dev_switcher.php      # Helper switching role / dev mode saat pengujian
├── forgot-password.php   # Fitur lupa password / reset kredensial
├── index.php             # Halaman utama / landing page (Hero, keunggulan, kategori)
├── katalog.php           # Halaman katalog produk sewa dengan filter & sorting harga
├── login.php             # Halaman autentikasi login pengguna
├── logout.php            # Handler logout & penghancuran sesi
├── register.php          # Halaman pendaftaran akun baru
├── assets/               # Resource statis aplikasi
│   ├── css/
│   │   └── style.css     # Design system utama, gaya kustom & CSS utilitas
│   ├── js/
│   │   └── main.js       # Script logika frontend, manipulasi DOM, modal & interaksi UI
│   └── *.png             # Gambar aset produk, kategori, & hero illustration
└── db/
    └── data.json         # Database utama berupa file JSON (katalog, users, beranda, pricing)
```

---

## 📜 Aturan & Standar Penulisan Kode (Coding Rules)

### 1. PHP Backend
- **Data Persistence**: Gunakan helper `get_db_data()` dan `save_db_data($data)` dari [`config.php`](file:///C:/Users/Rangga%20Prasetya/Documents/FormalWear/config.php) untuk semua operasi I/O data. Jangan membuat metode pembacaan file manual baru.
- **Sanitasi Input**: Selalu gunakan fungsi `sanitize_input()` untuk mengamankan data `$_POST` atau `$_GET`.
- **Session Management**: Selalu lakukan pengecekan `session_status() === PHP_SESSION_NONE` sebelum memulai sesi.
- **Gaya Penulisan**: Gunakan `snake_case` untuk nama variabel dan fungsi PHP (contoh: `$wisuda_surge`, `recalculate_catalog_dynamic_prices()`).

### 2. Frontend (HTML / CSS / JavaScript)
- **Styling**: Gunakan file CSS terpusat di [`assets/css/style.css`](file:///C:/Users/Rangga%20Prasetya/Documents/FormalWear/assets/css/style.css). Hindari penggunaan inline CSS berlebihan.
- **JavaScript**: Tulis logika frontend pada [`assets/js/main.js`](file:///C:/Users/Rangga%20Prasetya/Documents/FormalWear/assets/js/main.js). Pastikan penanganan event terisolasi dan menggunakan sintaks Vanilla JS (ES6+).
- **Indentasi**: Gunakan **4 spasi** untuk indentasi kode PHP, HTML, CSS, dan JavaScript.

---

## 🔄 Alur Kerja (Workflow Instructions)

1. **Sebelum Mengubah Kode**:
   - Periksa ketersediaan dan struktur file [`db/data.json`](file:///C:/Users/Rangga%20Prasetya/Documents/FormalWear/db/data.json).
   - Pastikan tidak merusak skema kunci utama JSON (`beranda`, `dynamic_pricing`, `katalog`, `users`).

2. **Pengujian Fitur**:
   - Apabila mengubah logika katalog atau harga, jalankan kalkulasi `recalculate_catalog_dynamic_prices()` dan pastikan nilai `price` serta `base_price` tetap sinkron.
   - Uji alur otentikasi (login/register) dan pastikan sesi pengguna tersimpan dengan baik.

3. **Sebelum Commit / Penyelesaian Tugas**:
   - Jalankan `php -l <nama_file.php>` untuk memastikan tidak ada kesalahan sintaks PHP.
   - Pastikan format file `db/data.json` tetap valid JSON (*pretty-printed*).
