<?php
require_once __DIR__ . '/config.php';

// Auth Guard: Admin Only Access
if (!is_logged_in() || (!is_admin() && (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] !== 'admin'))) {
  header('Location: login.php');
  exit;
}

$data = get_db_data();
$beranda = $data['beranda'];

$search_email = isset($_GET['search_email']) ? sanitize_input($_GET['search_email']) : '';

// Fetch users from MySQL database
try {
  $pdo = get_db_connection();
  if (!empty($search_email)) {
    $stmt_u = $pdo->prepare('
            SELECT u.id, u.name, u.email, u.role, u.is_active, DATE(u.created_at) AS registered_at,
                   p.no_hp AS phone, p.ukuran_biasa AS size
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            WHERE u.email LIKE ?
            ORDER BY u.id ASC
        ');
    $stmt_u->execute(['%' . $search_email . '%']);
  } else {
    $stmt_u = $pdo->query('
            SELECT u.id, u.name, u.email, u.role, u.is_active, DATE(u.created_at) AS registered_at,
                   p.no_hp AS phone, p.ukuran_biasa AS size
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            ORDER BY u.id ASC
        ');
  }
  $users = $stmt_u->fetchAll();
} catch (PDOException $e) {
  $users = [];
}

$search_code = isset($_GET['search_code']) ? sanitize_input($_GET['search_code']) : '';

// Fetch catalog items from MySQL database
try {
  $pdo = get_db_connection();
  if (!empty($search_code)) {
    $stmt = $pdo->prepare("
            SELECT u.id, u.kode_unit AS code, u.nama_unit AS name, u.foto AS image, u.harga_dasar AS base_price, u.status, u.ukuran AS size, u.is_active, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS category
            FROM units u
            LEFT JOIN unit_category uc ON u.id = uc.unit_id
            LEFT JOIN categories c ON uc.category_id = c.id
            WHERE u.kode_unit LIKE ?
            GROUP BY u.id
            ORDER BY u.id ASC
        ");
    $stmt->execute(['%' . $search_code . '%']);
  } else {
    $stmt = $pdo->query("
            SELECT u.id, u.kode_unit AS code, u.nama_unit AS name, u.foto AS image, u.harga_dasar AS base_price, u.status, u.ukuran AS size, u.is_active, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS category
            FROM units u
            LEFT JOIN unit_category uc ON u.id = uc.unit_id
            LEFT JOIN categories c ON uc.category_id = c.id
            GROUP BY u.id
            ORDER BY u.id ASC
        ");
  }
  $katalog = $stmt->fetchAll();
} catch (PDOException $e) {
  $katalog = [];
}

$active_tab = isset($_GET['tab']) ? sanitize_input($_GET['tab']) : 'dashboard';
$flash_msg = '';
$flash_type = 'success';

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = isset($_POST['action']) ? sanitize_input($_POST['action']) : '';

  // Action 1: Update Konten Beranda
  if ($action === 'update_beranda') {
    $data['beranda']['hero_title'] = sanitize_input($_POST['hero_title']);
    $data['beranda']['hero_subtitle'] = sanitize_input($_POST['hero_subtitle']);
    $data['beranda']['hero_btn_primary'] = sanitize_input($_POST['hero_btn_primary']);
    $data['beranda']['hero_btn_secondary'] = sanitize_input($_POST['hero_btn_secondary']);
    $data['beranda']['announcement'] = sanitize_input($_POST['announcement']);

    save_db_data($data);
    $flash_msg = 'Konten Beranda berhasil diperbarui!';
    $active_tab = 'beranda';
  }

  // Action 2: Tambah Produk Katalog Baru ke MySQL
  if ($action === 'add_katalog') {
    try {
      $pdo = get_db_connection();
      $kode = sanitize_input($_POST['code']);
      $nama = sanitize_input($_POST['name']);
      $deskripsi = isset($_POST['description']) ? sanitize_input($_POST['description']) : '';
      $ukuran = sanitize_input($_POST['size']);
      $warna = isset($_POST['color']) ? sanitize_input($_POST['color']) : 'Hitam';
      $foto = !empty($_POST['image']) ? sanitize_input($_POST['image']) : 'assets/product_tuxedo.png';
      $harga_dasar = floatval($_POST['price']);
      $harga_saat_ini = $harga_dasar;
      $status = strtolower(sanitize_input($_POST['status']));
      $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 1;

      $stmt = $pdo->prepare('
                INSERT INTO units (kode_unit, nama_unit, deskripsi, ukuran, warna, foto, harga_dasar, harga_saat_ini, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
      $stmt->execute([$kode, $nama, $deskripsi, $ukuran, $warna, $foto, $harga_dasar, $harga_saat_ini, $status]);
      $unit_id = $pdo->lastInsertId();

      if ($unit_id && $category_id) {
        $stmt_cat = $pdo->prepare('INSERT INTO unit_category (unit_id, category_id) VALUES (?, ?)');
        $stmt_cat->execute([$unit_id, $category_id]);
      }

      // Sync with JSON for dynamic pricing compatibility
      $new_item = [
        'id' => intval($unit_id),
        'code' => $kode,
        'category' => 'Jas',
        'name' => $nama,
        'base_price' => $harga_dasar,
        'price' => $harga_saat_ini,
        'status' => ucfirst($status),
        'size' => $ukuran,
        'demand_level' => '🟢 Permintaan Normal',
        'image' => $foto
      ];
      $data['katalog'][] = $new_item;
      save_db_data($data);

      $flash_msg = 'Produk katalog baru berhasil ditambahkan ke database!';
    } catch (PDOException $e) {
      if ($e->getCode() == 23000) {
        $flash_msg = "Gagal menambah produk: Kode Unit '{$kode}' sudah digunakan! Gunakan Kode Unit lain yang unik.";
      } else {
        $flash_msg = 'Gagal menambah produk: ' . $e->getMessage();
      }
      $flash_type = 'error';
    }
    $active_tab = 'katalog';
  }

  // Action 2b: Edit Produk Katalog di MySQL
  if ($action === 'edit_katalog') {
    try {
      $pdo = get_db_connection();
      $id = intval($_POST['item_id']);
      $kode = sanitize_input($_POST['code']);
      $nama = sanitize_input($_POST['name']);
      $ukuran = sanitize_input($_POST['size']);
      $foto = !empty($_POST['image']) ? sanitize_input($_POST['image']) : 'assets/product_tuxedo.png';
      $harga_dasar = floatval($_POST['price']);
      $status = strtolower(sanitize_input($_POST['status']));
      $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 1;

      $stmt = $pdo->prepare('
                UPDATE units 
                SET kode_unit = ?, nama_unit = ?, ukuran = ?, foto = ?, harga_dasar = ?, harga_saat_ini = ?, status = ?
                WHERE id = ?
            ');
      $stmt->execute([$kode, $nama, $ukuran, $foto, $harga_dasar, $harga_dasar, $status, $id]);

      // Update category relation
      $stmt_del = $pdo->prepare('DELETE FROM unit_category WHERE unit_id = ?');
      $stmt_del->execute([$id]);
      $stmt_cat = $pdo->prepare('INSERT INTO unit_category (unit_id, category_id) VALUES (?, ?)');
      $stmt_cat->execute([$id, $category_id]);

      // Update JSON data copy
      foreach ($data['katalog'] as &$item) {
        if ($item['id'] === $id) {
          $item['code'] = $kode;
          $item['name'] = $nama;
          $item['size'] = $ukuran;
          $item['image'] = $foto;
          $item['base_price'] = $harga_dasar;
          $item['price'] = $harga_dasar;
          $item['status'] = ucfirst($status);
          break;
        }
      }
      save_db_data($data);

      $flash_msg = 'Produk katalog berhasil diperbarui!';
    } catch (PDOException $e) {
      $flash_msg = 'Gagal mengedit produk: ' . $e->getMessage();
      $flash_type = 'error';
    }
    $active_tab = 'katalog';
  }

  // Action 3: Toggle Status Produk (Tersedia / Dipinjam)
  if ($action === 'toggle_katalog_status') {
    $id = intval($_POST['item_id']);
    try {
      $pdo = get_db_connection();
      $stmt = $pdo->prepare('SELECT status FROM units WHERE id = ?');
      $stmt->execute([$id]);
      $current = $stmt->fetchColumn();

      $new_status = ($current === 'tersedia') ? 'dipinjam' : 'tersedia';
      $stmt_up = $pdo->prepare('UPDATE units SET status = ? WHERE id = ?');
      $stmt_up->execute([$new_status, $id]);

      foreach ($data['katalog'] as &$item) {
        if ($item['id'] === $id) {
          $item['status'] = ucfirst($new_status);
          break;
        }
      }
      save_db_data($data);
      $flash_msg = 'Status produk berhasil diubah!';
    } catch (PDOException $e) {
      $flash_msg = 'Gagal mengubah status: ' . $e->getMessage();
    }
    $active_tab = 'katalog';
  }

  // Action 4: Soft Delete Produk Katalog (Non-aktifkan)
  if ($action === 'delete_katalog') {
    $id = intval($_POST['item_id']);
    try {
      $pdo = get_db_connection();
      $stmt = $pdo->prepare('UPDATE units SET is_active = 0 WHERE id = ?');
      $stmt->execute([$id]);
      $flash_msg = 'Produk katalog berhasil dinonaktifkan (Soft Delete)!';
    } catch (PDOException $e) {
      $flash_msg = 'Gagal menonaktifkan produk: ' . $e->getMessage();
      $flash_type = 'error';
    }
    $active_tab = 'katalog';
  }

  // Action 4b: Aktifkan Kembali Produk Katalog
  if ($action === 'activate_katalog') {
    $id = intval($_POST['item_id']);
    try {
      $pdo = get_db_connection();
      $stmt = $pdo->prepare('UPDATE units SET is_active = 1 WHERE id = ?');
      $stmt->execute([$id]);
      $flash_msg = 'Produk katalog berhasil diaktifkan kembali!';
    } catch (PDOException $e) {
      $flash_msg = 'Gagal mengaktifkan produk: ' . $e->getMessage();
      $flash_type = 'error';
    }
    $active_tab = 'katalog';
  }

  // Action 5: Tambah User Account Baru ke MySQL
  if ($action === 'add_user') {
    try {
      $pdo = get_db_connection();
      $name = sanitize_input($_POST['name']);
      $email = sanitize_input($_POST['email']);
      $phone = sanitize_input($_POST['phone']);
      $size = sanitize_input($_POST['size']);
      $role_input = sanitize_input($_POST['role']);
      $password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : 'password123';

      // Map role ke enum DB ('admin' atau 'anggota')
      $role = (strtolower($role_input) === 'admin' || strtolower($role_input) === 'pm / reviewer') ? 'admin' : 'anggota';

      // Check if email already exists
      $stmt_check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
      $stmt_check->execute([$email]);
      if ($stmt_check->fetch()) {
        $flash_msg = "Gagal menambah user: Email '{$email}' sudah terdaftar!";
        $flash_type = 'error';
      } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)');
        $stmt->execute([$name, $email, $hashed_password, $role]);
        $user_id = $pdo->lastInsertId();

        // Insert into profiles table
        $stmt_p = $pdo->prepare("INSERT INTO profiles (user_id, no_hp, alamat, ukuran_biasa) VALUES (?, ?, '', ?)");
        $stmt_p->execute([$user_id, $phone, !empty($size) ? $size : 'M']);

        $flash_msg = 'Account user baru berhasil ditambahkan ke database!';
      }
    } catch (PDOException $e) {
      $flash_msg = 'Gagal menambah user: ' . $e->getMessage();
      $flash_type = 'error';
    }
    $active_tab = 'users';
  }

  // Action 6: Toggle User Status (is_active 1 <-> 0)
  if ($action === 'toggle_user_status') {
    $id = intval($_POST['user_id']);
    try {
      $pdo = get_db_connection();
      $stmt = $pdo->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?');
      $stmt->execute([$id]);
      $flash_msg = 'Status keaktifan user account berhasil diubah!';
    } catch (PDOException $e) {
      $flash_msg = 'Gagal mengubah status user: ' . $e->getMessage();
      $flash_type = 'error';
    }
    $active_tab = 'users';
  }

  // Action 7: Hapus / Soft Delete User Account
  if ($action === 'delete_user') {
    $id = intval($_POST['user_id']);
    try {
      $pdo = get_db_connection();
      $stmt = $pdo->prepare('UPDATE users SET is_active = 0 WHERE id = ?');
      $stmt->execute([$id]);
      $flash_msg = 'User account berhasil dinonaktifkan!';
    } catch (PDOException $e) {
      $flash_msg = 'Gagal menonaktifkan user: ' . $e->getMessage();
      $flash_type = 'error';
    }
    $active_tab = 'users';
  }

  // Refresh local copy
  $data = get_db_data();
  $beranda = $data['beranda'];

  try {
    $pdo = get_db_connection();
    if (!empty($search_email)) {
      $stmt_u = $pdo->prepare('
                SELECT u.id, u.name, u.email, u.role, u.is_active, DATE(u.created_at) AS registered_at,
                       p.no_hp AS phone, p.ukuran_biasa AS size
                FROM users u
                LEFT JOIN profiles p ON u.id = p.user_id
                WHERE u.email LIKE ?
                ORDER BY u.id ASC
            ');
      $stmt_u->execute(['%' . $search_email . '%']);
    } else {
      $stmt_u = $pdo->query('
                SELECT u.id, u.name, u.email, u.role, u.is_active, DATE(u.created_at) AS registered_at,
                       p.no_hp AS phone, p.ukuran_biasa AS size
                FROM users u
                LEFT JOIN profiles p ON u.id = p.user_id
                ORDER BY u.id ASC
            ');
    }
    $users = $stmt_u->fetchAll();
  } catch (PDOException $e) {
    $users = [];
  }

  try {
    $pdo = get_db_connection();
    if (!empty($search_code)) {
      $stmt = $pdo->prepare("
                SELECT u.id, u.kode_unit AS code, u.nama_unit AS name, u.foto AS image, u.harga_dasar AS base_price, u.status, u.ukuran AS size, u.is_active, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS category
                FROM units u
                LEFT JOIN unit_category uc ON u.id = uc.unit_id
                LEFT JOIN categories c ON uc.category_id = c.id
                WHERE u.kode_unit LIKE ?
                GROUP BY u.id
                ORDER BY u.id ASC
            ");
      $stmt->execute(['%' . $search_code . '%']);
    } else {
      $stmt = $pdo->query("
                SELECT u.id, u.kode_unit AS code, u.nama_unit AS name, u.foto AS image, u.harga_dasar AS base_price, u.status, u.ukuran AS size, u.is_active, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS category
                FROM units u
                LEFT JOIN unit_category uc ON u.id = uc.unit_id
                LEFT JOIN categories c ON uc.category_id = c.id
                GROUP BY u.id
                ORDER BY u.id ASC
            ");
    }
    $katalog = $stmt->fetchAll();
  } catch (PDOException $e) {
    $katalog = [];
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Functional Elegance</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .admin-wrapper {
      display: flex;
      min-height: 100vh;
      width: 100vw;
      background-color: var(--bg-primary);
    }
    .admin-sidebar {
      width: 260px;
      background: var(--brand-primary);
      color: #FFFFFF;
      display: flex;
      flex-direction: column;
      padding: 28px 20px;
      border-right: 1px solid rgba(212, 175, 55, 0.3);
      flex-shrink: 0;
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
    }
    .admin-brand {
      font-family: var(--font-serif);
      font-size: 22px;
      font-weight: 700;
      color: var(--gold-accent);
      margin-bottom: 36px;
      display: flex;
      align-items: center;
      gap: 12px;
      letter-spacing: -0.01em;
    }
    .admin-nav {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .admin-nav-item a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 18px;
      border-radius: 8px;
      color: rgba(255, 255, 255, 0.8);
      font-size: 14px;
      font-weight: 500;
      text-decoration: none;
      transition: var(--transition-fast);
    }
    .admin-nav-item a:hover, .admin-nav-item a.active {
      background: rgba(212, 175, 55, 0.18);
      color: #FFFFFF;
      font-weight: 600;
    }
    .admin-nav-item a.active {
      border-left: 3px solid var(--gold-accent);
      background: rgba(212, 175, 55, 0.22);
    }
    .admin-main {
      flex: 1;
      background-color: var(--bg-primary);
      padding: 36px 48px;
      overflow-y: auto;
      max-width: 100%;
    }
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 32px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--border-light);
    }
    .admin-title {
      font-family: var(--font-serif);
      font-size: 28px;
      font-weight: 700;
      color: var(--brand-primary);
    }
    .btn-public-site {
      padding: 10px 20px;
      border: 1px solid var(--brand-primary);
      color: var(--brand-primary);
      border-radius: 8px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      transition: var(--transition-fast);
      background-color: #FFFFFF;
      box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .btn-public-site:hover {
      background-color: var(--brand-primary);
      color: #FFFFFF;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }
    .stat-card {
      background: #FFFFFF;
      border: 1px solid var(--border-light);
      border-radius: 12px;
      padding: 22px 24px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
      transition: var(--transition-fast);
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    }
    .stat-label {
      font-size: 12px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .stat-value {
      font-family: var(--font-serif);
      font-size: 32px;
      font-weight: 700;
      color: var(--brand-primary);
    }
    .admin-table-card {
      background: #FFFFFF;
      border: 1px solid var(--border-light);
      border-radius: 14px;
      padding: 28px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
      margin-bottom: 32px;
    }
    .table-responsive {
      width: 100%;
      overflow-x: auto;
      border-radius: 8px;
      border: 1px solid var(--border-light);
    }
    .table-header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .table-title {
      font-family: var(--font-serif);
      font-size: 20px;
      font-weight: 700;
      color: var(--brand-primary);
    }
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    .admin-table th, .admin-table td {
      padding: 14px 18px;
      text-align: left;
      border-bottom: 1px solid var(--border-light);
      vertical-align: middle;
    }
    .admin-table th {
      background-color: var(--bg-secondary);
      color: var(--brand-primary);
      font-weight: 700;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .admin-table tbody tr:last-child td {
      border-bottom: none;
    }
    .admin-table tbody tr:hover {
      background-color: rgba(246, 243, 238, 0.5);
    }
    .thumb-img {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      object-fit: cover;
      border: 1px solid var(--border-light);
    }
    .btn-action-small {
      padding: 7px 14px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      text-decoration: none;
      transition: var(--transition-fast);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
    }
    .btn-toggle {
      background-color: #EBF8FF;
      color: #2B6CB0;
      border: 1px solid #BEE3F8;
    }
    .btn-toggle:hover {
      background-color: #2B6CB0;
      color: #FFFFFF;
    }
    .btn-delete {
      background-color: var(--error-bg);
      color: var(--error-color);
      border: 1px solid rgba(179, 38, 30, 0.2);
    }
    .btn-delete:hover {
      background-color: var(--error-color);
      color: #FFFFFF;
    }
    .admin-form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
    }
    .flash-alert {
      padding: 14px 20px;
      background-color: #DEF7EC;
      color: #03543F;
      border: 1px solid #84E1BC;
      border-radius: 8px;
      margin-bottom: 24px;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }
  </style>
</head>
<body style="flex-direction: row;">

  <div class="admin-wrapper">
    
    <!-- Admin Left Sidebar -->
    <aside class="admin-sidebar">
      <div class="admin-brand">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
        </svg>
        Admin Panel
      </div>

      <ul class="admin-nav">
        <li class="admin-nav-item">
          <a href="admin.php?tab=dashboard" class="<?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
            📊 <span>Overview Dashboard</span>
          </a>
        </li>
        <li class="admin-nav-item">
          <a href="admin.php?tab=katalog" class="<?php echo $active_tab === 'katalog' ? 'active' : ''; ?>">
            🛍️ <span>Kelola Katalog</span>
          </a>
        </li>
        <li class="admin-nav-item">
          <a href="admin.php?tab=beranda" class="<?php echo $active_tab === 'beranda' ? 'active' : ''; ?>">
            🏠 <span>Kelola Beranda</span>
          </a>
        </li>
        <li class="admin-nav-item">
          <a href="admin.php?tab=users" class="<?php echo $active_tab === 'users' ? 'active' : ''; ?>">
            👥 <span>Kelola Account User</span>
          </a>
        </li>
      </ul>
    </aside>

    <!-- Admin Main Content -->
    <main class="admin-main">
      
      <div class="admin-header">
        <div>
          <h1 class="admin-title">Pusat Kontrol Admin Panel</h1>
          <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Kelola materi beranda, produk katalog, dan akun pengguna.</p>
        </div>
        <a href="index.php" class="btn-public-site" target="_blank">🌐 Lihat Web Publik ↗</a>
      </div>

      <?php if (!empty($flash_msg)): ?>
        <div class="flash-alert">
          ✓ <?php echo $flash_msg; ?>
        </div>
      <?php endif; ?>

      <!-- TAB 1: OVERVIEW DASHBOARD -->
      <?php if ($active_tab === 'dashboard'): ?>
        
        <!-- Metrics Stats Grid -->
        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-label">Total Produk Katalog</span>
            <span class="stat-value"><?php echo count($katalog); ?> Unit</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">Account User Terdaftar</span>
            <span class="stat-value"><?php echo count($users); ?> User</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">Unit Tersedia</span>
            <span class="stat-value"><?php echo count(array_filter($katalog, function ($i) { return strtolower($i['status']) === 'tersedia'; })); ?> Unit</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">Unit Dipinjam</span>
            <span class="stat-value"><?php echo count(array_filter($katalog, function ($i) { return strtolower($i['status']) === 'dipinjam'; })); ?> Unit</span>
          </div>
        </div>

        <!-- Tabel Ringkasan Katalog -->
        <div class="admin-table-card">
          <div class="table-header-row">
            <h2 class="table-title">Ringkasan Katalog</h2>
            <a href="admin.php?tab=katalog" class="forgot-link">Lihat Semua Katalog →</a>
          </div>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Foto</th>
                  <th>Nama Produk</th>
                  <th>Kategori</th>
                  <th>Harga Dasar</th>
                  <th>Status Sewa</th>
                  <th>Status Keaktifan</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($katalog as $item):
                  $is_act = isset($item['is_active']) ? intval($item['is_active']) : 1;
                  ?>
                  <tr style="<?php echo !$is_act ? 'opacity: 0.6; background-color: rgba(0,0,0,0.02);' : ''; ?>">
                    <td><strong><?php echo $item['code']; ?></strong></td>
                    <td><img src="<?php echo $item['image']; ?>" class="thumb-img" alt=""></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['category']; ?></td>
                    <td>Rp <?php echo number_format($item['base_price'], 0, ',', '.'); ?></td>
                    <td>
                      <span class="status-badge <?php echo strtolower($item['status']) === 'tersedia' ? 'status-available' : 'status-borrowed'; ?>">
                        <?php echo $item['status']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="status-badge <?php echo $is_act ? 'status-available' : 'status-borrowed'; ?>">
                        <?php echo $is_act ? '● Aktif' : '○ Non-Aktif'; ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

      <!-- TAB 2: KELOLA KATALOG -->
      <?php if ($active_tab === 'katalog'): ?>
        
        <!-- Form Tambah Produk Baru -->
        <div class="admin-table-card">
          <h2 class="table-title" style="margin-bottom: 16px;">+ Tambah Produk Katalog Baru</h2>
          <form method="POST" action="admin.php?tab=katalog" class="auth-form">
            <input type="hidden" name="action" value="add_katalog">
            <div class="admin-form-grid">
              <div class="form-group">
                <label class="form-label">Kode Unit (e.g. TUX-005)</label>
                <input type="text" name="code" class="form-input" placeholder="TUX-005" required>
              </div>
              <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="name" class="form-input" placeholder="Royal Black Velvet Tuxedo" required>
              </div>
              <div class="form-group">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select">
                  <option value="Tuxedo Klasik">Tuxedo Klasik</option>
                  <option value="Jas Pengantin">Jas Pengantin</option>
                  <option value="Batik Sutra">Batik Sutra</option>
                  <option value="Kebaya Modern">Kebaya Modern</option>
                  <option value="Kemeja Formal">Kemeja Formal</option>
                  <option value="Aksesoris">Aksesoris</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Harga Dasar (Base Rp / 3 Hari)</label>
                <input type="number" name="price" class="form-input" placeholder="1500000" required>
              </div>
              <div class="form-group">
                <label class="form-label">Ukuran Tersedia</label>
                <select name="size" class="form-select">
                  <option value="S">S</option>
                  <option value="M" selected>M</option>
                  <option value="L">L</option>
                  <option value="XL">XL</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Status Awal</label>
                <select name="status" class="form-select">
                  <option value="Tersedia">Tersedia</option>
                  <option value="Dipinjam">Dipinjam</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px; margin-top: 16px;">
              Simpan Produk Baru
            </button>
          </form>
        </div>

        <!-- Tabel Kelola Produk -->
        <div class="admin-table-card">
          <div class="table-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <h2 class="table-title" style="margin: 0;">Daftar Produk Katalog (<?php echo count($katalog); ?> Unit)</h2>
            
            <!-- Form Cari Kode Unit -->
            <form method="GET" action="admin.php" style="display: flex; gap: 8px; align-items: center;">
              <input type="hidden" name="tab" value="katalog">
              <input type="text" name="search_code" class="form-input" placeholder="Cari Kode Unit (e.g. TUX-001)" value="<?php echo htmlspecialchars($search_code); ?>" style="padding: 8px 14px; font-size: 13px; width: 240px;">
              <button type="submit" class="btn-submit" style="width: auto; padding: 8px 16px; font-size: 13px;">🔍 Cari</button>
              <?php if (!empty($search_code)): ?>
                <a href="admin.php?tab=katalog" class="btn-action-small btn-delete" style="padding: 8px 12px; font-size: 13px; text-decoration: none;">Reset</a>
              <?php endif; ?>
            </form>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th style="width: 50px; text-align: center;">No.</th>
                  <th>Kode</th>
                  <th>Foto</th>
                  <th>Nama Produk</th>
                  <th>Kategori</th>
                  <th>Harga dasar</th>
                  <th>Status Sewa</th>
                  <th>Status Keaktifan</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                foreach ($katalog as $item):
                  $is_act = isset($item['is_active']) ? intval($item['is_active']) : 1;
                  ?>
                  <tr style="<?php echo !$is_act ? 'opacity: 0.6; background-color: rgba(0,0,0,0.02);' : ''; ?>">
                    <td style="text-align: center; color: var(--text-muted); font-weight: 600;"><?php echo $no++; ?></td>
                    <td><strong><?php echo $item['code']; ?></strong></td>
                    <td><img src="<?php echo $item['image']; ?>" class="thumb-img" alt=""></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['category']; ?></td>
                    <td>Rp <?php echo number_format($item['base_price'], 0, ',', '.'); ?></td>
                    <td>
                      <span class="status-badge <?php echo strtolower($item['status']) === 'tersedia' ? 'status-available' : 'status-borrowed'; ?>">
                        <?php echo $item['status']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="status-badge <?php echo $is_act ? 'status-available' : 'status-borrowed'; ?>">
                        <?php echo $is_act ? '● Aktif' : '○ Non-Aktif'; ?>
                      </span>
                    </td>
                    <td>
                      <div style="display: flex; gap: 8px;">
                        <!-- Edit Modal Trigger -->
                        <button type="button" class="btn-action-small" style="background: var(--gold-accent); color: #000;" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">Edit</button>

                        <form method="POST" action="admin.php?tab=katalog">
                          <input type="hidden" name="action" value="toggle_katalog_status">
                          <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                          <button type="submit" class="btn-action-small btn-toggle">Ubah Status Sewa</button>
                        </form>

                        <?php if ($is_act): ?>
                          <form method="POST" action="admin.php?tab=katalog" onsubmit="return confirm('Nonaktifkan produk ini? Produk tidak akan muncul di katalog publik.');">
                            <input type="hidden" name="action" value="delete_katalog">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-action-small btn-delete">Non-Aktifkan</button>
                          </form>
                        <?php else: ?>
                          <form method="POST" action="admin.php?tab=katalog">
                            <input type="hidden" name="action" value="activate_katalog">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-action-small" style="background: #DEF7EC; color: #03543F; border: 1px solid #84E1BC;">Aktifkan</button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Modal Edit Produk Katalog -->
        <div id="editModalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center;">
          <div style="background: #FFFFFF; padding: 32px; border-radius: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border: 1px solid var(--gold-accent);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
              <h2 style="font-family: var(--font-serif); color: var(--brand-primary); font-size: 20px;">✏️ Edit Produk Katalog</h2>
              <button type="button" onclick="closeEditModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            
            <form method="POST" action="admin.php?tab=katalog">
              <input type="hidden" name="action" value="edit_katalog">
              <input type="hidden" name="item_id" id="edit_item_id">
              
              <div class="admin-form-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                  <label class="form-label">Kode Unit</label>
                  <input type="text" name="code" id="edit_code" class="form-input" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Nama Produk</label>
                  <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Harga Dasar (Rp)</label>
                  <input type="number" name="price" id="edit_price" class="form-input" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Ukuran</label>
                  <select name="size" id="edit_size" class="form-select">
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                    <option value="XXL">XXL</option>
                    <option value="All Size">All Size</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Status Ketersediaan</label>
                  <select name="status" id="edit_status" class="form-select">
                    <option value="Tersedia">Tersedia</option>
                    <option value="Dipinjam">Dipinjam</option>
                    <option value="Maintenance">Maintenance</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Path Foto (URL)</label>
                  <input type="text" name="image" id="edit_image" class="form-input">
                </div>
              </div>

              <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" onclick="closeEditModal()" class="btn-nav-outline" style="padding: 8px 20px;">Batal</button>
                <button type="submit" class="btn-submit" style="width: auto; padding: 8px 24px;">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>

        <script>
          function openEditModal(item) {
            document.getElementById('edit_item_id').value = item.id;
            document.getElementById('edit_code').value = item.code || item.kode_unit || '';
            document.getElementById('edit_name').value = item.name || item.nama_unit || '';
            document.getElementById('edit_price').value = item.base_price || item.harga_dasar || item.price || 0;
            document.getElementById('edit_size').value = item.size || item.ukuran || 'M';
            document.getElementById('edit_status').value = item.status || 'Tersedia';
            document.getElementById('edit_image').value = item.image || item.foto || '';
            document.getElementById('editModalOverlay').style.display = 'flex';
          }
          function closeEditModal() {
            document.getElementById('editModalOverlay').style.display = 'none';
          }
        </script>
      <?php endif; ?>

      <!-- TAB 3: KELOLA KONTEN BERANDA -->
      <?php if ($active_tab === 'beranda'): ?>
        <div class="admin-table-card">
          <h2 class="table-title" style="margin-bottom: 20px;">Edit Konten Halaman Beranda (Landing Page)</h2>
          
          <form method="POST" action="admin.php?tab=beranda" class="auth-form">
            <input type="hidden" name="action" value="update_beranda">
            
            <div class="form-group">
              <label class="form-label">Headline Utama (Hero Title)</label>
              <input type="text" name="hero_title" class="form-input" value="<?php echo htmlspecialchars($beranda['hero_title']); ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label">Subtitle Beranda (Hero Subtitle)</label>
              <textarea name="hero_subtitle" class="form-textarea" required><?php echo htmlspecialchars($beranda['hero_subtitle']); ?></textarea>
            </div>

            <div class="admin-form-grid">
              <div class="form-group">
                <label class="form-label">Teks Tombol Utama</label>
                <input type="text" name="hero_btn_primary" class="form-input" value="<?php echo htmlspecialchars($beranda['hero_btn_primary']); ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Teks Tombol Sekunder</label>
                <input type="text" name="hero_btn_secondary" class="form-input" value="<?php echo htmlspecialchars($beranda['hero_btn_secondary']); ?>" required>
              </div>
            </div>

            <div class="form-group" style="margin-top: 12px;">
              <label class="form-label">Pengumuman Ticker / Banner Promo</label>
              <input type="text" name="announcement" class="form-input" value="<?php echo htmlspecialchars($beranda['announcement']); ?>">
            </div>

            <button type="submit" class="btn-submit" style="width: auto; padding: 12px 32px; margin-top: 16px;">
              Update Konten Beranda
            </button>
          </form>
        </div>
      <?php endif; ?>

      <!-- TAB 4: KELOLA USER ACCOUNT -->
      <?php if ($active_tab === 'users'): ?>
        
        <!-- Form Tambah User Account Baru -->
        <div class="admin-table-card">
          <h2 class="table-title" style="margin-bottom: 16px;">+ Tambah Account User Baru</h2>
          <form method="POST" action="admin.php?tab=users" class="auth-form">
            <input type="hidden" name="action" value="add_user">
            <div class="admin-form-grid">
              <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-input" placeholder="Ahmad Subagyo" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email User</label>
                <input type="email" name="email" class="form-input" placeholder="ahmad@domain.com" required>
              </div>
              <div class="form-group">
                <label class="form-label">No. Handphone</label>
                <input type="tel" name="phone" class="form-input" placeholder="08123456789">
              </div>
              <div class="form-group">
                <label class="form-label">Ukuran Pakaian Biasa</label>
                <select name="size" class="form-select">
                  <option value="S">S</option>
                  <option value="M" selected>M</option>
                  <option value="L">L</option>
                  <option value="XL">XL</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Role User</label>
                <select name="role" class="form-select">
                  <option value="Customer" selected>Customer</option>
                  <option value="VIP Customer">VIP Customer</option>
                  <option value="PM / Reviewer">PM / Reviewer</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px; margin-top: 16px;">
              Buat Account User
            </button>
          </form>
        </div>

        <!-- Tabel Daftar User Accounts -->
        <div class="admin-table-card">
          <div class="table-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <h2 class="table-title" style="margin: 0;">Daftar Account User Terdaftar (<?php echo count($users); ?> User)</h2>
            
            <!-- Form Cari Email User -->
            <form method="GET" action="admin.php" style="display: flex; gap: 8px; align-items: center;">
              <input type="hidden" name="tab" value="users">
              <input type="email" name="search_email" class="form-input" placeholder="Cari Email (e.g. user@email.com)" value="<?php echo htmlspecialchars($search_email); ?>" style="padding: 8px 14px; font-size: 13px; width: 250px;">
              <button type="submit" class="btn-submit" style="width: auto; padding: 8px 16px; font-size: 13px;">🔍 Cari</button>
              <?php if (!empty($search_email)): ?>
                <a href="admin.php?tab=users" class="btn-action-small btn-delete" style="padding: 8px 12px; font-size: 13px; text-decoration: none;">Reset</a>
              <?php endif; ?>
            </form>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th style="width: 60px; text-align: center;">ID</th>
                  <th>Nama Lengkap</th>
                  <th>Email</th>
                  <th>No. Telepon</th>
                  <th style="text-align: center; width: 80px;">Ukuran</th>
                  <th style="text-align: center; width: 100px;">Role</th>
                  <th style="white-space: nowrap; width: 120px;">Tgl Daftar</th>
                  <th style="text-align: center; width: 130px;">Status Keaktifan</th>
                  <th style="text-align: center; width: 120px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($users as $u):
                  $user_active = !empty($u['is_active']);
                  ?>
                  <tr style="<?php echo !$user_active ? 'opacity: 0.6; background-color: rgba(0,0,0,0.02);' : ''; ?>">
                    <td style="text-align: center;"><strong>#<?php echo $u['id']; ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                    <td><a href="mailto:<?php echo htmlspecialchars($u['email']); ?>" style="color: var(--brand-primary); text-decoration: none;"><?php echo htmlspecialchars($u['email']); ?></a></td>
                    <td><?php echo !empty($u['phone']) ? htmlspecialchars($u['phone']) : '<span style="color: var(--text-muted);">-</span>'; ?></td>
                    <td style="text-align: center;"><span class="status-badge" style="background: #EBE8E3; color: var(--text-dark);"><?php echo !empty($u['size']) ? htmlspecialchars($u['size']) : 'M'; ?></span></td>
                    <td style="text-align: center;"><span class="status-badge" style="background: rgba(91, 6, 23, 0.08); color: var(--brand-primary); font-weight: 600;"><?php echo ucfirst($u['role']); ?></span></td>
                    <td style="white-space: nowrap; font-size: 13px; color: var(--text-muted);"><?php echo !empty($u['registered_at']) ? date('d M Y', strtotime($u['registered_at'])) : '-'; ?></td>
                    <td style="text-align: center;">
                      <span class="status-badge <?php echo $user_active ? 'status-available' : 'status-borrowed'; ?>">
                        <?php echo $user_active ? '● Aktif' : '○ Non-Aktif'; ?>
                      </span>
                    </td>
                    <td style="text-align: center;">
                      <form method="POST" action="admin.php?tab=users">
                        <input type="hidden" name="action" value="toggle_user_status">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <button type="submit" class="btn-action-small <?php echo $user_active ? 'btn-delete' : 'btn-toggle'; ?>">
                          <?php echo $user_active ? 'Non-Aktifkan' : 'Aktifkan'; ?>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      <?php endif; ?>

    </main>

  </div>

  <script src="assets/js/main.js"></script>
</body>
</html>
