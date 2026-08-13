<?php
require_once __DIR__ . '/config.php';

// Auth Guard: redirect to login.php if user is not logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$data = get_db_data();
$beranda = $data['beranda'];
$katalog = $data['katalog'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Functional Elegance - Sewa Formal Wear Premium</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Top Announcement Ticker Banner if set in Admin -->
  <?php if (!empty($beranda['announcement'])): ?>
    <div style="background-color: var(--brand-primary); color: var(--gold-accent); text-align: center; padding: 8px 16px; font-size: 13px; font-weight: 600; letter-spacing: 0.02em;">
      ✨ <?php echo htmlspecialchars($beranda['announcement']); ?>
    </div>
  <?php endif; ?>

  <!-- Top Navigation Bar (Figma Node #1:394) -->
  <header class="top-navbar" style="<?php echo !empty($beranda['announcement']) ? 'top: 34px;' : ''; ?>">
    <div class="navbar-container">
      <a href="index.php" class="brand-logo">
        <svg class="brand-icon" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
        </svg>
        Functional Elegance
      </a>
      
        <ul class="nav-links">
          <li><a href="index.php" class="nav-link active">Beranda</a></li>
          <li><a href="katalog.php" class="nav-link">Katalog</a></li>
          <li><a href="#kategori" class="nav-link">Kategori</a></li>
          <li><a href="#cara-sewa" class="nav-link">Cara Sewa</a></li>
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

  <!-- Main Content Body -->
  <main>

    <!-- Hero Section (Figma Node #1:141) -->
    <section class="hero-wrapper" style="<?php echo !empty($beranda['announcement']) ? 'padding-top: 170px;' : ''; ?>">
      <div class="hero-grid">
        <div class="hero-text-col">
          <h1 class="hero-headline"><?php echo htmlspecialchars($beranda['hero_title']); ?></h1>
          <p class="hero-description"><?php echo htmlspecialchars($beranda['hero_subtitle']); ?></p>
          <div class="hero-buttons">
            <a href="katalog.php" class="btn-hero-primary"><?php echo htmlspecialchars($beranda['hero_btn_primary']); ?></a>
            <a href="#cara-sewa" class="btn-hero-secondary"><?php echo htmlspecialchars($beranda['hero_btn_secondary']); ?></a>
          </div>
        </div>
        <div class="hero-image-col">
          <img src="assets/home_hero.png" alt="Functional Elegance Formalwear Display">
        </div>
      </div>
    </section>

    <!-- Section - Produk Paling Laris (Figma Node #1:154) -->
    <section class="section-popular" id="katalog">
      <div class="section-header-row">
        <div>
          <h2 class="section-title">Produk Paling Laris</h2>
          <p class="section-subtitle">Pilihan favorit pelanggan kami minggu ini.</p>
        </div>
        <a href="katalog.php" class="see-all-link">Lihat Semua Katalog →</a>
      </div>

      <div class="products-grid">
        <?php foreach (array_slice($katalog, 0, 4) as $index => $item): ?>
          <div class="product-card">
            <div class="product-img-wrapper">
              <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
              <?php if ($index === 0 || $index === 2): ?>
                <div class="trending-badge">
                  <span>★</span> Trending
                </div>
              <?php endif; ?>
            </div>
            <div class="product-body">
              <div class="product-meta">
                <h3 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                <span class="status-badge <?php echo $item['status'] === 'Tersedia' ? 'status-available' : 'status-borrowed'; ?>">
                  <?php echo $item['status']; ?>
                </span>
              </div>
              <span class="product-category"><?php echo $item['category']; ?></span>
              <div class="product-price">
                Rp <?php echo number_format($item['price'], 0, ',', '.'); ?> <span>/ 3 hari</span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Section - Rekomendasi Sesuai Budget (Figma Node #1:226) -->
    <section class="section-budget">
      <h2 class="section-title">Rekomendasi Sesuai Budget</h2>
      <p class="section-subtitle">Temukan pilihan busana formal yang pas dengan anggaran acara Anda tanpa mengorbankan kualitas.</p>

      <div class="budget-tabs">
        <button class="budget-tab-btn active">Hemat</button>
        <button class="budget-tab-btn">Standar</button>
        <button class="budget-tab-btn">Premium</button>
      </div>

      <div class="budget-cards-grid">
        <div class="budget-card">
          <div class="budget-icon-circle">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
              <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
            </svg>
          </div>
          <h3 class="budget-card-title">Paket Wisuda</h3>
          <p class="budget-card-desc">Kebaya basic / Jas standar + bawahan</p>
          <span class="budget-card-price">Mulai Rp 150k</span>
        </div>

        <div class="budget-card">
          <div class="budget-icon-circle">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
              <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
          </div>
          <h3 class="budget-card-title">Set Toga</h3>
          <p class="budget-card-desc">Jubah, topi, dan perlengkapan wisuda</p>
          <span class="budget-card-price">Mulai Rp 100k</span>
        </div>

        <div class="budget-card">
          <div class="budget-icon-circle">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"></path>
            </svg>
          </div>
          <h3 class="budget-card-title">Kemeja Formal</h3>
          <p class="budget-card-desc">Berbagai warna untuk acara semi-formal</p>
          <span class="budget-card-price">Mulai Rp 75k</span>
        </div>
      </div>
    </section>

    <!-- Section - Jelajahi Kategori (Figma Node #1:271) -->
    <section class="section-categories" id="kategori">
      <h2 class="section-title" style="text-align: center;">Jelajahi Kategori</h2>

      <div class="categories-grid">
        <a href="katalog.php" class="category-card">
          <img src="assets/cat_jas.png" alt="Kategori Jas">
          <div class="category-card-content">
            <h3 class="category-card-title">Jas</h3>
          </div>
        </a>

        <a href="katalog.php" class="category-card">
          <img src="assets/cat_kebaya.png" alt="Kategori Kebaya">
          <div class="category-card-content">
            <h3 class="category-card-title">Kebaya</h3>
          </div>
        </a>

        <a href="katalog.php" class="category-card">
          <img src="assets/cat_toga.png" alt="Kategori Toga">
          <div class="category-card-content">
            <h3 class="category-card-title">Toga</h3>
          </div>
        </a>

        <a href="katalog.php" class="category-card">
          <img src="assets/cat_aksesoris.png" alt="Kategori Aksesoris">
          <div class="category-card-content">
            <h3 class="category-card-title">Aksesoris</h3>
          </div>
        </a>
      </div>
    </section>

    <!-- Section - Cara Kerja (Figma Node #1:311) -->
    <section class="section-how" id="cara-sewa">
      <div class="how-container">
        <h2 class="section-title">Cara Kerja</h2>

        <div class="how-steps-grid">
          <div class="how-line"></div>

          <div class="how-step-item">
            <div class="how-icon-wrap">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
            </div>
            <h3 class="how-step-title">1. Pilih Pakaian</h3>
            <p class="how-step-desc">Jelajahi katalog dan temukan busana yang sesuai dengan acara Anda.</p>
          </div>

          <div class="how-step-item">
            <div class="how-icon-wrap">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
            </div>
            <h3 class="how-step-title">2. Tentukan Tanggal</h3>
            <p class="how-step-desc">Pilih tanggal peminjaman dan pengembalian (standar 3 hari).</p>
          </div>

          <div class="how-step-item">
            <div class="how-icon-wrap">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
              </svg>
            </div>
            <h3 class="how-step-title">3. Pengiriman</h3>
            <p class="how-step-desc">Pakaian dikirim ke alamat Anda dalam kondisi bersih dan wangi.</p>
          </div>

          <div class="how-step-item">
            <div class="how-icon-wrap">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
              </svg>
            </div>
            <h3 class="how-step-title">4. Kembalikan</h3>
            <p class="how-step-desc">Kemas kembali dan kurir kami akan mengambilnya. Tidak perlu dicuci!</p>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Footer (Figma Node #1:357) -->
  <footer class="footer-main" id="tentang-kami">
    <div class="footer-container">
      
      <div class="footer-col">
        <h2 class="footer-title">Functional Elegance</h2>
        <p class="footer-desc">Menyediakan solusi pakaian formal premium untuk setiap momen berharga Anda.</p>
        <p class="footer-copyright">© 2026 Functional Elegance Formal Wear. All rights reserved.</p>
      </div>

      <div class="footer-col">
        <h3 class="footer-col-heading">Tautan</h3>
        <ul class="footer-list">
          <li><a href="#tentang-kami">Tentang Kami</a></li>
          <li><a href="#">Bantuan</a></li>
          <li><a href="#">Kebijakan Privasi</a></li>
          <li><a href="#">Syarat & Ketentuan</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h3 class="footer-col-heading">Kontak</h3>
        <ul class="footer-list">
          <li><a href="mailto:hello@functionalelegance.com">Email: hello@functionalelegance.com</a></li>
          <li><a href="https://wa.me/6281234567890">WhatsApp: +62 812 3456 7890</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h3 class="footer-col-heading">Ikuti Kami</h3>
        <ul class="footer-list" style="flex-direction: row; gap: 16px;">
          <li>
            <a href="#" aria-label="Instagram">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
              </svg>
            </a>
          </li>
          <li>
            <a href="#" aria-label="Facebook">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
              </svg>
            </a>
          </li>
        </ul>
      </div>

    </div>
  </footer>

  <?php require_once __DIR__ . '/chatbot_modal.php'; ?>
  <script src="assets/js/main.js"></script>
</body>
</html>
