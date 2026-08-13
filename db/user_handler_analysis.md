# ANALISIS FITUR KELOLA USER & AUTENTIKASI LOGIN (ADMIN.PHP & LOGIN.PHP)

Dokumen ini berisi hasil analisis mendalam dan kutipan kode asli terkait penanganan pembuatan user (`add_user`), perubahan status user (`toggle_user_status`), penghapusan user (`delete_user`), dan mekanismenya terhadap proses autentikasi login.

---

## 1. Apakah `add_user` melakukan INSERT ke tabel MySQL `users` atau `db/data.json`?

**Jawaban:**
Fungsi handler `add_user` di `admin.php` **HANYA menulis ke file JSON `db/data.json`** dan **TIDAK** melakukan query `INSERT INTO users` ke database MySQL.

### 📌 Kutipan Kode Asli `add_user` (`admin.php`):
```php
    // Action 5: Tambah User Account Baru
    if ($action === 'add_user') {
        $new_id = count($data['users']) > 0 ? max(array_column($data['users'], 'id')) + 1 : 1;
        $new_user = [
            'id' => $new_id,
            'name' => sanitize_input($_POST['name']),
            'email' => sanitize_input($_POST['email']),
            'phone' => sanitize_input($_POST['phone']),
            'size' => sanitize_input($_POST['size']),
            'role' => sanitize_input($_POST['role']),
            'registered_at' => date('Y-m-d'),
            'status' => 'Aktif'
        ];
        $data['users'][] = $new_user;
        save_db_data($data);
        $flash_msg = 'User account baru berhasil dibuat!';
        $active_tab = 'users';
    }
```

---

## 2. Apakah ada mekanisme sinkronisasi (sync) otomatis ke tabel MySQL `users`?

**Jawaban:**
**TIDAK ADA**. Tidak ditemukan background process, cron job, script sinkronisasi, maupun listener di `login.php` atau `config.php` yang menyinkronkan data user dari `db/data.json` ke tabel MySQL `users`.

---

## 3. Jika login menggunakan akun yang baru ditambahkan admin lewat Admin Panel, apakah `login.php` akan berhasil?

**Jawaban:**
**TIDAK AKAN BERHASIL (GAGAL LOGIN)**.

### Alasannya:
Halaman `login.php` melakukan `SELECT` query secara langsung ke tabel MySQL `users`. Karena `add_user` hanya menyimpan user baru ke `db/data.json` (bukan ke MySQL), maka akun baru tersebut **tidak tersimpan di database MySQL**, sehingga `login.php` akan mengembalikan error *"Email atau password salah"*.

### 📌 Kutipan Kode Asli Autentikasi (`login.php`):
```php
        try {
            $pdo = get_db_connection();
            
            // Query user along with profile details
            $stmt = $pdo->prepare("
                SELECT u.id, u.name, u.email, u.password, u.role, u.is_active, 
                       p.no_hp, p.alamat, p.ukuran_biasa
                FROM users u
                LEFT JOIN profiles p ON u.id = p.user_id
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && (password_verify($password, $user['password']) || $password === 'password123')) {
```

---

## 4. Apakah `toggle_user_status` dan `delete_user` bekerja pada tabel SQL atau `data.json`?

**Jawaban:**
Keduanya **HANYA memanipulasi file `db/data.json`** dan tidak menyentuh tabel MySQL `users` sama sekali.

### 📌 Kutipan Kode Asli `toggle_user_status` & `delete_user` (`admin.php`):
```php
    // Action 6: Toggle User Status
    if ($action === 'toggle_user_status') {
        $id = intval($_POST['user_id']);
        foreach ($data['users'] as &$u) {
            if ($u['id'] === $id) {
                $u['status'] = ($u['status'] === 'Aktif') ? 'Suspended' : 'Aktif';
                break;
            }
        }
        save_db_data($data);
        $flash_msg = 'Status user account berhasil diubah!';
        $active_tab = 'users';
    }

    // Action 7: Hapus User Account
    if ($action === 'delete_user') {
        $id = intval($_POST['user_id']);
        $data['users'] = array_values(array_filter($data['users'], function($u) use ($id) {
            return $u['id'] !== $id;
        }));
        save_db_data($data);
        $flash_msg = 'User account berhasil dihapus!';
        $active_tab = 'users';
    }
```
