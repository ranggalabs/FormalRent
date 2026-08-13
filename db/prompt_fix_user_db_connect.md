# PROMPT: KONEKSIKAN KELOLA USER ACCOUNT DI ADMIN PANEL KE DATABASE MYSQL

Saya memiliki proyek PHP bernama "FormalWear (Functional Elegance)". Saat ini modul **Kelola User Account** di Admin Panel (`admin.php?tab=users`) masih membaca dan menulis ke file JSON (`db/data.json`). Akibatnya, user baru yang ditambahkan admin tidak tersimpan di MySQL dan tidak bisa login, serta user terdaftar dari `register.php` tidak muncul di admin.

Tolong hubungkan fitur **Kelola User Account** di `admin.php` secara penuh ke database MySQL (`formalwear_schema`), menggunakan tabel `users` dan `profiles`.

---

## 🏗️ TECH STACK
- Backend: PHP vanilla (PDO Prepared Statement)
- Database: MySQL (`formalwear_schema`), tabel `users` dan `profiles`
- Server: php -S localhost:8000

---

## 🗄️ SKEMA TABEL DATABASE RELEVAN

```sql
-- Tabel users
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

-- Tabel profiles (1 user = 1 profile)
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
```

---

## 📁 FILE YANG HARUS DIMODIFIKASI

```
FormalWear/
└── admin.php        # Ubah data fetch $users & action handler add_user, toggle_user_status ke MySQL PDO
```

---

## ✅ PERUBAHAN DETAIL PADA `admin.php`

### 1. Ubah Query Fetch Data User ($users)

**Sebelum:**
```php
$users = $data['users'];
```

**Sesudah (Fetch dari MySQL JOIN `users` & `profiles`):**
```php
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.role, u.is_active, u.created_at AS registered_at,
               p.no_hp AS phone, p.ukuran_biasa AS size
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        ORDER BY u.id ASC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
```

Pastikan perbaikan ini diterapkan di dua tempat di `admin.php` (inisialisasi atas dan *refresh local copy* setelah submit POST).

---

### 2. Ubah Action `add_user` (POST) ke MySQL

**Sebelum:** Menambah item baru ke `$data['users']` dan `save_db_data($data)`.

**Sesudah (INSERT ke MySQL `users` & `profiles`):**
```php
if ($action === 'add_user') {
    try {
        $pdo = get_db_connection();
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $size = sanitize_input($_POST['size']);
        $role_input = sanitize_input($_POST['role']);
        $password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : 'password123';
        
        // Map role UI ke enum DB ('admin' atau 'anggota')
        $role = (strtolower($role_input) === 'admin' || strtolower($role_input) === 'pm / reviewer') ? 'admin' : 'anggota';

        // Check if email already exists
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            $flash_msg = "Gagal menambah user: Email '{$email}' sudah terdaftar!";
            $flash_type = 'error';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert users
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$name, $email, $hashed_password, $role]);
            $user_id = $pdo->lastInsertId();

            // Insert profiles
            $stmt_p = $pdo->prepare("INSERT INTO profiles (user_id, no_hp, alamat, ukuran_biasa) VALUES (?, ?, '', ?)");
            $stmt_p->execute([$user_id, $phone, $size]);

            $flash_msg = 'Account user baru berhasil ditambahkan ke database!';
        }
    } catch (PDOException $e) {
        $flash_msg = 'Gagal menambah user: ' . $e->getMessage();
        $flash_type = 'error';
    }
    $active_tab = 'users';
}
```

---

### 3. Ubah Action `toggle_user_status` (POST) ke MySQL

**Sebelum:** Mengubah nilai `status` pada `$data['users']`.

**Sesudah (UPDATE `is_active` di MySQL):**
```php
if ($action === 'toggle_user_status') {
    $id = intval($_POST['user_id']);
    try {
        $pdo = get_db_connection();
        // Toggle is_active (1 <-> 0)
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $flash_msg = 'Status keaktifan user account berhasil diubah!';
    } catch (PDOException $e) {
        $flash_msg = 'Gagal mengubah status user: ' . $e->getMessage();
        $flash_type = 'error';
    }
    $active_tab = 'users';
}
```

---

### 4. Sesuaikan Form Tambah User & Tabel User di HTML `admin.php`

#### A. Di Form Tambah User:
Tambahkan field input password default (opsional / default `password123` jika dikosongkan).

#### B. Di Tabel User:
- Gunakan `$u['is_active']` untuk badge status keaktifan (`● Aktif` jika `is_active == 1`, `○ Non-Aktif` jika `0`).
- Tombol aksi:
  - Jika `is_active == 1` ➔ Tampilkan tombol **"Non-Aktifkan"** (merah).
  - Jika `is_active == 0` ➔ Tampilkan tombol **"Aktifkan"** (hijau).

---

## ⚠️ ATURAN PENTING

1. Semua akun yang ditambahkan admin harus secara otomatis bisa dipakai login di `login.php` dengan password default yang di-hash dengan `password_hash()`.
2. Gunakan PDO Prepared Statement untuk semua query MySQL.
3. Tetap pertahankan pengurutan `ORDER BY u.id ASC` agar data baru yang diinput selalu berada di urutan paling bawah tabel.
