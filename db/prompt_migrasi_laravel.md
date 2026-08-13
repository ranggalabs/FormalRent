# 🚀 PROMPT: MIGRASI PROYEK FORMALWEAR DARI PHP NATIVE KE LARAVEL

Saya memiliki proyek web PHP Native bernama **"FormalWear (Functional Elegance)"** yang ingin dimigrasikan secara penuh ke framework **Laravel (versi 10 / 11)**.

Tolong bantu lakukan migrasi arsitektur codebase dari PHP Native ke Laravel dengan mengikuti panduan struktur, model, database migration, controller, dan routing berikut.

---

## 📌 1. OVERVIEW PROYEK & SPESIFIKASI

- **Nama Proyek**: FormalWear (Functional Elegance) - Sistem Informasi Sewa & Catalog Formal Wear
- **Stack Asal**: PHP Native + PDO MySQL (`formalwear_schema`) + HTML/CSS/JS (Vanilla)
- **Target Stack**: Laravel 10/11 + Eloquent ORM + Blade Templating (atau Vue/React jika disesuaikan) + MySQL

---

## 🗄️ 2. SKEMA DATABASE & LARAVEL MIGRATIONS

Buat file migration Laravel untuk semua tabel yang ada pada file `db/formalwear_schema.sql`:

### A. Tabel `users`
- `id` (bigIncrements)
- `name` (string 150)
- `email` (string 150, unique)
- `password` (string 255)
- `role` (enum: `'admin'`, `'anggota'`, default `'anggota'`)
- `is_active` (boolean, default `true`)
- `timestamps()`

### B. Tabel `profiles`
- `id` (bigIncrements)
- `user_id` (foreignKey ke `users.id`, onDelete cascade)
- `no_hp` (string 20)
- `alamat` (text, nullable)
- `foto` (string 255, nullable)
- `ukuran_biasa` (enum: `'XS','S','M','L','XL','XXL'`, nullable)
- `kategori_favorit_id` (foreignKey ke `categories.id`, nullable)
- `timestamps()`

### C. Tabel `categories`
- `id` (bigIncrements)
- `nama_kategori` (string 100, unique)
- `urutan_tampil` (integer, default 0)
- `tampil_navbar` (boolean, default true)
- `timestamps()`

### D. Tabel `units` (Katalog Produk)
- `id` (bigIncrements)
- `kode_unit` (string 30, unique)
- `nama_unit` (string 150)
- `deskripsi` (text, nullable)
- `ukuran` (enum: `'XS','S','M','L','XL','XXL','All Size'`, default `'All Size'`)
- `warna` (string 50, nullable)
- `foto` (string 255, nullable)
- `harga_dasar` (decimal 10,2)
- `harga_saat_ini` (decimal 10,2)
- `status` (enum: `'tersedia','dipinjam','maintenance'`, default `'tersedia'`)
- `skor_kelayakan` (integer, default 100)
- `tampil_homepage` (boolean, default false)
- `is_trending` (boolean, default false)
- `is_active` (boolean, default true) — *Soft delete/aktifkan unit*
- `timestamps()`

### E. Tabel `unit_category` (Pivot Many-to-Many)
- `id` (bigIncrements)
- `unit_id` (foreignKey ke `units.id`, onDelete cascade)
- `category_id` (foreignKey ke `categories.id`, onDelete cascade)
- Unique key: `['unit_id', 'category_id']`

### F. Tabel `loans` (Peminjaman)
- `id` (bigIncrements)
- `user_id` (foreignKey ke `users.id`, onDelete cascade)
- `unit_id` (foreignKey ke `units.id`, onDelete cascade)
- `tgl_pinjam` (date)
- `tgl_jatuh_tempo` (date)
- `tgl_kembali` (date, nullable)
- `status` (enum: `'dipinjam','terlambat','kembali'`, default `'dipinjam'`)
- `denda` (decimal 10,2, default 0)
- `kondisi_saat_kembali` (enum: `'baik','perlu_cuci','rusak'`, nullable)
- `catatan` (text, nullable)
- `diprosed_oleh` (foreignKey ke `users.id`, nullable)
- `timestamps()`

---

## 🧱 3. ELOQUENT MODELS & RELASI

Buat Eloquent Model untuk masing-masing tabel:

1. **`User`**:
   - `hasOne(Profile::class)`
   - `hasMany(Loan::class)`
2. **`Profile`**:
   - `belongsTo(User::class)`
   - `belongsTo(Category::class, 'kategori_favorit_id')`
3. **`Category`**:
   - `belongsToMany(Unit::class, 'unit_category')`
4. **`Unit`**:
   - `belongsToMany(Category::class, 'unit_category')`
   - `hasMany(Loan::class)`
   - Scope `scopeActive($query)` -> `WHERE is_active = 1`
5. **`Loan`**:
   - `belongsTo(User::class)`
   - `belongsTo(Unit::class)`
   - `belongsTo(User::class, 'diprosed_oleh')`

---

## 🕹️ 4. CONTROLLER & LOGIKA BISNIS

Buat Controller Laravel untuk menangani setiap fitur:

### A. `AdminController`
- `index(Request $request)`: Menampilkan Dashboard Admin (Overview metric: Total Katalog, User Terdaftar, Unit Tersedia/Dipinjam).
- `katalog(Request $request)`:
  - Mengambil daftar unit katalog dengan filter pencarian `kode_unit` (`WHERE kode_unit LIKE %...%`).
  - `store(Request $request)`: Validasi & `INSERT INTO units` + attach kategori.
  - `update(Request $request, Unit $unit)`: Update data unit katalog.
  - `toggleStatus(Unit $unit)`: Toggle `status` (Tersedia <-> Dipinjam).
  - `destroy(Unit $unit)`: Soft delete / ubah keaktifan unit (`is_active = 0`).
  - `activate(Unit $unit)`: Re-aktifkan unit (`is_active = 1`).
- `users(Request $request)`:
  - Mengambil daftar user dengan filter pencarian `email` (`WHERE email LIKE %...%`).
  - `storeUser(Request $request)`: Simpan user baru + profile + hash password.
  - `toggleUserStatus(User $user)`: Toggle `is_active` user (1 <-> 0).

### B. `CatalogController` (Public Site)
- `index(Request $request)`: Menampilkan halaman `katalog.blade.php`.
- Filter: Kategori, Ukuran, Warna, Urutan Harga/Populer.
- Hanya menampilkan unit aktif (`is_active = 1`).

### C. `HomeController`
- `index()`: Landing page beranda dengan data promo/announcement dari JSON / database `homepage_settings`.

---

## 🛣️ 5. ROUTES (`routes/web.php`)

```php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\AdminController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('katalog.index');

// Admin Routes (Gunakan Middleware Role Admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Kelola Katalog
    Route::get('/katalog', [AdminController::class, 'katalog'])->name('admin.katalog');
    Route::post('/katalog', [AdminController::class, 'storeKatalog'])->name('admin.katalog.store');
    Route::put('/katalog/{unit}', [AdminController::class, 'updateKatalog'])->name('admin.katalog.update');
    Route::patch('/katalog/{unit}/toggle', [AdminController::class, 'toggleKatalogStatus'])->name('admin.katalog.toggle');
    Route::delete('/katalog/{unit}', [AdminController::class, 'destroyKatalog'])->name('admin.katalog.destroy');
    Route::patch('/katalog/{unit}/activate', [AdminController::class, 'activateKatalog'])->name('admin.katalog.activate');
    
    // Kelola User Account
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');
});
```

---

## 🎨 6. BLADE TEMPLATES & FRONTEND

Pindahkan file HTML/PHP Native ke struktur Blade:
- `resources/views/layouts/app.blade.php` -> Master layout publik (Header/Navbar, Footer, Chatbot modal)
- `resources/views/layouts/admin.blade.php` -> Master layout Admin Panel (Sidebar, Header, Flash Alert)
- `resources/views/index.blade.php` -> Landing page
- `resources/views/katalog.blade.php` -> Katalog produk publik
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/katalog.blade.php` (Termasuk form pencarian kode unit & kolom No. otomatis)
- `resources/views/admin/users.blade.php` (Termasuk form pencarian email & status keaktifan user)

---

## ✅ CHECKLIST HASIL AKHIR YANG DIHARAPKAN

1. [ ] Seluruh migration & seeder berjalan lancar dengan `php artisan migrate --seed`.
2. [ ] Fitur login & otentikasi terhubung ke tabel `users` (bisa menggunakan Laravel Breeze/Fortify).
3. [ ] Pencarian berdasarkan **Kode Unit** di katalog admin & pencarian **Email** di user account admin berfungsi menggunakan Eloquent Query Builder.
4. [ ] Soft delete (`is_active`) berfungsi pada Katalog & Users.
5. [ ] Urutan data secara default berurutan dari ID terlama ke ID terbaru (`ORDER BY id ASC`).
