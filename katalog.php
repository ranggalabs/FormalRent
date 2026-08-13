<?php
require_once __DIR__ . '/config.php';

// Auth Guard: redirect to login.php if user is not logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$data = get_db_data();

// Fetch catalog items from MySQL database
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("
        SELECT u.*, GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') AS kategori_nama
        FROM units u
        LEFT JOIN unit_category uc ON u.id = uc.unit_id
        LEFT JOIN categories c ON uc.category_id = c.id
        WHERE u.is_active = 1
        GROUP BY u.id
        ORDER BY u.id ASC
    ");
    $catalog_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $catalog_items = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Katalog Koleksi Formal - Functional Elegance</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Top Navigation Bar (Figma Node #1:660) -->
  <header class="top-navbar">
    <div class="navbar-container">
      <a href="index.php" class="brand-logo">
        <svg class="brand-icon" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
        </svg>
        Functional Elegance
      </a>
      
        <ul class="nav-links">
          <li><a href="index.php" class="nav-link">Beranda</a></li>
          <li><a href="katalog.php" class="nav-link active">Katalog</a></li>
          <li><a href="index.php#kategori" class="nav-link">Kategori</a></li>
          <li><a href="index.php#cara-sewa" class="nav-link">Cara Sewa</a></li>
          <?php if (is_admin() || (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin')): ?>
            <li><a href="admin.php" class="nav-link" style="color: var(--gold-text); font-weight: 700;">⚙️ Admin Panel</a></li>
          <?php endif; ?>
        </ul>

      <div class="navbar-actions">
        <?php if (is_logged_in()): ?>
          <a href="dashboard.php" class="btn-nav-solid">Dashboard</a>
          <a href="logout.php" class="btn-nav-outline">Keluar</a>
        <?php else: ?>
          <a href="login.php" class="btn-nav-outline">Masuk</a>
          <a href="register.php" class="btn-nav-solid">Daftar</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Main Catalog Page Content (Figma Node #1:425) -->
  <div class="catalog-page-wrapper">

    <div class="catalog-layout">
      
      <!-- Aside - Sidebar Filters (Figma Node #1:427) -->
      <aside class="catalog-sidebar">
        
        <!-- Search Unit -->
        <div class="filter-group">
          <label class="filter-title">Pencarian</label>
          <div class="search-input-box">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="catalogSearchInput" placeholder="Cari kode atau nama...">
          </div>
        </div>

        <!-- Filter Kategori -->
        <div class="filter-group">
          <label class="filter-title">Kategori</label>
          <div class="filter-options-list">
            <label class="filter-checkbox-label">
              <input type="checkbox" class="cat-filter" value="Jas Pengantin">
              <span>Jas Pengantin Pria</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="checkbox" class="cat-filter" value="Tuxedo Klasik">
              <span>Tuxedo Klasik</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="checkbox" class="cat-filter" value="Batik Sutra">
              <span>Batik Sutra</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="checkbox" class="cat-filter" value="Kemeja Formal">
              <span>Kemeja Formal</span>
            </label>
          </div>
        </div>

        <!-- Filter Ukuran -->
        <div class="filter-group">
          <label class="filter-title">Ukuran</label>
          <div class="size-buttons-row">
            <button class="btn-size-chip" data-size="S">S</button>
            <button class="btn-size-chip active" data-size="M">M</button>
            <button class="btn-size-chip active" data-size="L">L</button>
            <button class="btn-size-chip" data-size="XL">XL</button>
          </div>
        </div>

        <!-- Filter Harga Sewa -->
        <div class="filter-group">
          <label class="filter-title">Harga Sewa</label>
          <div class="price-range-wrap">
            <input type="range" id="priceRange" min="0" max="2500000" step="50000" value="2500000">
            <div class="price-labels">
              <span>Rp 0</span>
              <span id="priceMaxLabel">Maks: Rp 2.5jt</span>
            </div>
          </div>
        </div>

        <!-- Filter Status Ketersediaan -->
        <div class="filter-group">
          <label class="filter-title">Status Ketersediaan</label>
          <div class="filter-options-list">
            <label class="filter-checkbox-label">
              <input type="radio" name="statusFilter" value="ALL" checked>
              <span>Semua Status</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="statusFilter" value="Tersedia">
              <span>Tersedia</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="statusFilter" value="Dipinjam">
              <span>Dipinjam</span>
            </label>
          </div>
        </div>

        <!-- Reset Filter Button -->
        <button type="button" class="btn-reset-filter" id="resetFiltersBtn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 4 23 10 17 10"></polyline>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
          </svg>
          Reset Filter
        </button>

      </aside>

      <!-- Product Grid Area (Figma Node #1:512) -->
      <main class="catalog-content">
        
        <!-- Header Bar -->
        <div class="catalog-header-bar">
          <h1 class="catalog-page-title">Koleksi Formal</h1>
          <div class="catalog-header-right">
            <span class="item-count-text">Menampilkan 1-<?php echo count($catalog_items); ?> dari <?php echo count($catalog_items); ?> unit</span>
            <select class="sort-select">
              <option value="terbaru">Terbaru</option>
              <option value="terpopuler">Terpopuler</option>
              <option value="termurah">Harga Termurah</option>
              <option value="termahal">Harga Termahal</option>
            </select>
          </div>
        </div>

        <!-- Product Cards Grid (Figma Node #1:526) -->
        <div class="catalog-grid" id="catalogGrid">
          
          <?php foreach ($catalog_items as $item): 
            $kode = isset($item['kode_unit']) ? $item['kode_unit'] : (isset($item['code']) ? $item['code'] : '');
            $nama = isset($item['nama_unit']) ? $item['nama_unit'] : (isset($item['name']) ? $item['name'] : '');
            $kategori = !empty($item['kategori_nama']) ? $item['kategori_nama'] : (isset($item['category']) ? $item['category'] : 'Umum');
            $ukuran = isset($item['ukuran']) ? $item['ukuran'] : (isset($item['size']) ? $item['size'] : '');
            $harga = isset($item['harga_saat_ini']) ? $item['harga_saat_ini'] : (isset($item['price']) ? $item['price'] : 0);
            $harga_dasar = isset($item['harga_dasar']) ? $item['harga_dasar'] : (isset($item['base_price']) ? $item['base_price'] : $harga);
            $status = isset($item['status']) ? ucfirst($item['status']) : 'Tersedia';
            $foto = !empty($item['foto']) ? $item['foto'] : (isset($item['image']) ? $item['image'] : 'assets/product_tuxedo.png');
          ?>
            <div class="catalog-item-card" 
                 data-code="<?php echo htmlspecialchars($kode); ?>"
                 data-name="<?php echo htmlspecialchars($nama); ?>"
                 data-cat="<?php echo htmlspecialchars($kategori); ?>"
                 data-size="<?php echo htmlspecialchars($ukuran); ?>"
                 data-price="<?php echo $harga; ?>"
                 data-status="<?php echo htmlspecialchars($status); ?>">
              
              <div class="catalog-img-box">
                <img src="<?php echo htmlspecialchars($foto); ?>" alt="<?php echo htmlspecialchars($nama); ?>">
                <div class="status-badge <?php echo strtolower($status) === 'tersedia' ? 'status-available' : 'status-borrowed'; ?>" 
                     style="position: absolute; top: 12px; right: 12px; backdrop-filter: blur(4px);">
                  ● <?php echo htmlspecialchars($status); ?>
                </div>
              </div>

              <div class="catalog-card-body">
                <div class="card-code-row">
                  <span><?php echo htmlspecialchars($kode); ?></span>
                  <span><?php echo htmlspecialchars($kategori); ?></span>
                </div>
                <h3 class="catalog-card-name"><?php echo htmlspecialchars($nama); ?></h3>
                
                <div class="catalog-price-tag">
                  Rp <?php echo number_format($harga, 0, ',', '.'); ?> <span>/ 3 hari</span>
                </div>

                <a href="#" class="btn-card-detail">
                  <span>Lihat Detail</span>
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </a>
              </div>

            </div>
          <?php endforeach; ?>

        </div>

        <!-- Pagination (Figma Node #1:620) -->
        <div class="pagination-wrapper">
          <button class="btn-page" disabled>‹</button>
          <button class="btn-page active">1</button>
          <button class="btn-page">2</button>
          <button class="btn-page">3</button>
          <span style="color: var(--text-muted);">...</span>
          <button class="btn-page">›</button>
        </div>

      </main>

    </div>
  </div>

  <!-- Footer (Figma Node #1:635) -->
  <footer class="footer-main">
    <div class="footer-container">
      
      <div class="footer-col" style="grid-column: span 2;">
        <h2 class="footer-title">Functional Elegance</h2>
        <p class="footer-desc">Menyediakan koleksi pakaian formal premium untuk momen spesial Anda dengan kualitas butik yang tak tertandingi.</p>
        <p class="footer-copyright">© 2026 Functional Elegance Formal Wear. All rights reserved.</p>
      </div>

      <div class="footer-col">
        <h3 class="footer-col-heading">Tautan Cepat</h3>
        <ul class="footer-list">
          <li><a href="index.php#tentang-kami">Tentang Kami</a></li>
          <li><a href="#">Bantuan</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h3 class="footer-col-heading">Legal</h3>
        <ul class="footer-list">
          <li><a href="#">Kebijakan Privasi</a></li>
          <li><a href="#">Syarat & Ketentuan</a></li>
        </ul>
      </div>

    </div>
  </footer>

  <?php require_once __DIR__ . '/chatbot_modal.php'; ?>
  <script src="assets/js/main.js"></script>
</body>
</html>
