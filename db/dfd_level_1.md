# DATA UNTUK DFD (DATA FLOW DIAGRAM) LEVEL 1 - FORMALWEAR

Dokumen ini berisi pemetaan lengkap data untuk pembuatan **DFD Level 1** dari proyek **FormalWear (Functional Elegance)** berdasarkan audit basis kode PHP vanilla dan skema database MySQL (`formalwear_schema`).

---

## 1. ENTITAS EKSTERNAL (Role / Aktor)

Berdasarkan pemeriksaan skema database (`formalwear_schema.sql` pada tabel `users`) dan pengecekan sesi autentikasi di kode program (`is_logged_in()`, `is_admin()`, `$_SESSION['user']['role']`):

### A. Admin
- **Peran**: Pengelola utama sistem (*Superuser/Administrator*).
- **Akses & Aktivitas**:
  - Mengakses halaman Admin Panel (`admin.php`).
  - Mengelola Konten Beranda (`update_beranda`).
  - Mengelola Data Katalog Unit Produk (`add_katalog`, `edit_katalog`, `toggle_katalog_status`, `delete_katalog` / Soft Delete `is_active = 0`, `activate_katalog`).
  - Mengelola Akun User / Anggota (`add_user`, `toggle_user_status`, `delete_user` / Soft Delete status).
  - Memantau ringkasan statistik sistem pada Overview Dashboard (`admin.php?tab=dashboard`).

### B. Anggota / Customer (User Terdaftar)
- **Peran**: Pengguna umum terdaftar yang menyewa pakaian formal.
- **Akses & Aktivitas**:
  - Melakukan Autentikasi (`login.php`, `register.php`, `logout.php`).
  - Mengakses Halaman Utama/Beranda (`index.php`).
  - Mengakses Halaman Katalog Publik (`katalog.php`) — membaca unit pakaian yang berstatus **Aktif (`is_active = 1`)**.
  - Mengakses Dashboard Pelanggan (`dashboard.php`) untuk melihat profil dan riwayat penyewaan.

### C. Pengunjung / Guest (User Belum Login)
- **Peran**: Pengguna publik yang belum melakukan autentikasi.
- **Akses & Aktivitas**:
  - Dibatasi oleh Auth Guard pada halaman internal (`index.php`, `katalog.php`, `dashboard.php`, `admin.php`).
  - Hanya dapat mengakses halaman Login (`login.php`) dan Registrasi (`register.php`).

> 📌 **Catatan Perbedaan (Dokumentasi vs Implementasi)**:
> Pada skema SQL `formalwear_schema.sql`, enum role bertuliskan `ENUM('admin', 'anggota')`. Namun pada form pembuatan user di UI `admin.php` dan data JSON, role juga mencatat pilihan seperti `'Customer'`, `'VIP Customer'`, dan `'PM / Reviewer'` untuk kebutuhan klasifikasi internal.

---

## 2. PROSES UTAMA & ROLE YANG MENGAKSES

Sistem ini dibangun secara **Vanilla PHP** (Modular Script-based Architecture tanpa kerangka kerja/framework MVC seperti Laravel). Fungsi bisnis ditangani langsung oleh file-file modul utama:

### 1. Modul Autentikasi & Sesi User (`login.php`, `register.php`, `logout.php`)
- **Aktor**: Pengunjung (Guest) & User Terdaftar.
- **Batasan Akses**: Publik (tanpa login) untuk `login` & `register`; User terdaftar untuk `logout`.
- **Fungsi / Method**:
  - `POST login`: Autentikasi email & password terhadap data store, inisialisasi `$_SESSION['user']` dan `$_SESSION['admin_logged_in']`.
  - `POST register`: Registrasi akun baru, memasukkan data pengguna ke data store (`users`).
  - `GET logout`: Menghancurkan sesi aktif (`session_destroy()`) dan *redirect* ke halaman login.
  - `GET login_as=pm` (*DevSwitcher Handler* di `config.php`): Fitur penguji cepat login sebagai PM.

### 2. Modul Katalog Publik (`katalog.php`)
- **Aktor**: Anggota / Customer.
- **Batasan Akses**: Wajib Login (`is_logged_in()`).
- **Fungsi / Method**:
  - `GET index/render`: Membaca data unit dari database MySQL (`units JOIN categories`) dengan kriteria `WHERE u.is_active = 1` berurutan `ORDER BY u.id ASC`.
  - Filter & Search (Client-side JS via `main.js`): Filter pencarian berdasarkan nama, kode, kategori, ukuran, dan status ketersediaan.

### 3. Modul Pengelolaan Katalog Admin (`admin.php?tab=katalog`)
- **Aktor**: Admin.
- **Batasan Akses**: Admin Only (`is_admin()` / `role === 'admin'`).
- **Fungsi / Method**:
  - `add_katalog` (POST): Meng-insert unit baru ke tabel MySQL `units` dan `unit_category`, serta menyinkronkan ke data JSON.
  - `edit_katalog` (POST): Meng-update data unit dan relasi kategorinya di MySQL dan data JSON.
  - `toggle_katalog_status` (POST): Mengubah status sewa unit antara `'tersedia'` dan `'dipinjam'`.
  - `delete_katalog` (POST): Soft Delete unit dengan mengubah `is_active = 0` (unit tidak terhapus permanen dari DB, hanya dinonaktifkan).
  - `activate_katalog` (POST): Re-aktivasi unit dengan mengubah `is_active = 1`.

### 4. Modul Pengelolaan User Admin (`admin.php?tab=users`)
- **Aktor**: Admin.
- **Batasan Akses**: Admin Only (`is_admin()`).
- **Fungsi / Method**:
  - `add_user` (POST): Menambahkan data user baru ke data store `data.json`.
  - `toggle_user_status` / `delete_user` (POST): Soft delete status keaktifan user (`'Aktif'` / `'Tidak Aktif'`).

### 5. Modul Pengelolaan Konten Beranda Admin (`admin.php?tab=beranda`)
- **Aktor**: Admin.
- **Batasan Akses**: Admin Only (`is_admin()`).
- **Fungsi / Method**:
  - `update_beranda` (POST): Mengubah judul hero banner, subtitle, label tombol, dan teks pengumuman promo pada data store `data.json`.

### 6. Modul Overview Dashboard Admin (`admin.php?tab=dashboard`)
- **Aktor**: Admin.
- **Batasan Akses**: Admin Only (`is_admin()`).
- **Fungsi / Method**:
  - Aggregation / Read Only: Menghitung total unit katalog, total user, unit tersedia, unit dipinjam, dan merender ringkasan tabel katalog secara real-time.

---

## 3. DATA STORE (Tabel Database & Data File)

Sistem menggunakan gabungan **Database MySQL (`formalwear_schema`)** dan **JSON File Store (`db/data.json`)**.

### Daftar Tabel Database MySQL:

1. **`users`**
   - **Ditulis oleh**: `register.php` (tambah user), `admin.php` (kelola user via DB jika dihubungkan).
   - **Dibaca oleh**: `login.php` (autentikasi), `admin.php` (otorisasi role).

2. **`profiles`**
   - **Ditulis oleh**: `register.php` (saat inisialisasi profil), `dashboard.php` (update profil).
   - **Dibaca oleh**: `dashboard.php` (menampilkan profil anggota).

3. **`categories`**
   - **Ditulis oleh**: Inisialisasi awal (Seed Data SQL).
   - **Dibaca oleh**: `katalog.php` & `admin.php` (relasi JOIN kategori produk via `unit_category`).

4. **`units`**
   - **Ditulis oleh**: `admin.php` (`add_katalog`, `edit_katalog`, `toggle_katalog_status`, `delete_katalog` / Soft Delete `is_active = 0`, `activate_katalog`).
   - **Dibaca oleh**: `katalog.php` (hanya `is_active = 1`), `admin.php` (membaca seluruh unit aktif & non-aktif).

5. **`unit_category`** (Pivot Table)
   - **Ditulis oleh**: `admin.php` (`add_katalog` & `edit_katalog` untuk relasi many-to-many unit-kategori).
   - **Dibaca oleh**: `katalog.php` & `admin.php` (query `GROUP_CONCAT` nama kategori).

6. **`loans`** (Tabel Peminjaman)
   - **Ditulis oleh**: Modul transaksi peminjaman / pengembalian.
   - **Dibaca oleh**: `dashboard.php` (riwayat sewa user), `admin.php` (ringkasan status dipinjam).

7. **`homepage_settings`**
   - **Ditulis oleh**: `admin.php` (`update_beranda`).
   - **Dibaca oleh**: `index.php` (merender banner & pengumuman promo).

8. **`pricing_logs`**, **`demand_snapshot`**, **`chat_sessions`**
   - *Tabel pendukung analitik & histori chatbot*.

### Data File Store:
- **`db/data.json`**
  - **Ditulis oleh**: `admin.php` (menyimpan array `beranda`, `users`, dan backup sync `katalog`).
  - **Dibaca oleh**: `config.php` (`get_db_data()`), `index.php`, `admin.php`.

---

## 4. ALUR DATA ANTAR PROSES

Dalam sistem ini terdapat beberapa keterkaitan data antar proses:

1. **Alur Manajemen Katalog Admin ➔ Katalog Publik Pelanggan**:
   - **Proses Pengirim**: Modul *Kelola Katalog Admin* (`admin.php?tab=katalog`).
   - **Proses Penerima**: Modul *Katalog Publik* (`katalog.php`).
   - **Alur & Tabel Terlibat**:
     - Admin menambahkan/mengubah data pada tabel **`units`** dan **`unit_category`**.
     - Apabila admin melakukan *Soft Delete* (`delete_katalog`), sistem memperbarui field `is_active = 0` pada tabel **`units`**.
     - Modul Katalog Publik (`katalog.php`) membaca tabel **`units`** dengan kondisi `WHERE u.is_active = 1`. Hasilnya, produk yang dinonaktifkan oleh Admin secara otomatis tidak akan dirender di layar pelanggan.

2. **Alur Autentikasi ➔ Hak Akses Modul Admin**:
   - **Proses Pengirim**: Modul *Autentikasi* (`login.php`).
   - **Proses Penerima**: Modul *Admin Panel* (`admin.php`).
   - **Alur & Tabel Terlibat**:
     - `login.php` mengonfirmasi kredensial ke tabel **`users`** dan menyimpan role ke dalam `$_SESSION['user']`.
     - `admin.php` memeriksa variabel sesi tersebut. Jika role bukan `admin`, sistem langsung menolak akses dan membelokkan (*redirect*) ke `login.php`.

3. **Alur Pembaruan Konten Beranda Admin ➔ Halaman Utama User**:
   - **Proses Pengirim**: Modul *Kelola Beranda* (`admin.php?tab=beranda`).
   - **Proses Penerima**: Modul *Beranda Utama* (`index.php`).
   - **Alur & Tabel Terlibat**:
     - Admin memperbarui data pengumuman promo dan banner. Data ini ditulis ke data store (**`homepage_settings` / `data.json`**).
     - `index.php` membaca data store tersebut dan menampilkan teks promo real-time di bagian atas *Navigation Bar*.
