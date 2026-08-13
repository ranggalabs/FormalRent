<?php
require_once __DIR__ . '/config.php';

if (!is_logged_in()) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Functional Elegance</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .dashboard-header {
      background-color: var(--bg-primary);
      border-bottom: 1px solid var(--border-light);
      padding: 20px 48px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .user-pill {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .user-greeting {
      font-size: 14px;
      font-weight: 500;
      color: var(--text-dark);
    }
    .btn-logout {
      padding: 8px 16px;
      background-color: transparent;
      border: 1px solid var(--brand-primary);
      color: var(--brand-primary);
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: var(--transition-fast);
    }
    .btn-logout:hover {
      background-color: var(--brand-primary);
      color: #FFFFFF;
    }
    .dashboard-main {
      padding: 48px;
      max-width: 1200px;
      margin: 0 auto;
      width: 100%;
    }
    .welcome-banner {
      background: linear-gradient(135deg, var(--brand-primary) 0%, #30020A 100%);
      color: #FFFFFF;
      padding: 48px;
      border-radius: 12px;
      margin-bottom: 40px;
      box-shadow: 0 10px 30px rgba(91, 6, 23, 0.2);
    }
    .welcome-banner h2 {
      font-family: var(--font-serif);
      font-size: 32px;
      margin-bottom: 12px;
    }
    .welcome-banner p {
      font-size: 16px;
      color: rgba(255, 255, 255, 0.85);
      max-width: 600px;
    }
    .catalog-title {
      font-family: var(--font-serif);
      font-size: 24px;
      margin-bottom: 24px;
      color: var(--text-dark);
    }
    .catalog-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 24px;
    }
    .card {
      background: #FFFFFF;
      border: 1px solid var(--border-light);
      border-radius: 8px;
      overflow: hidden;
      transition: var(--transition-fast);
    }
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }
    .card-img {
      height: 220px;
      background-color: #EBE8E3;
      background-size: cover;
      background-position: center;
    }
    .card-body {
      padding: 20px;
    }
    .card-title {
      font-family: var(--font-serif);
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 8px;
    }
    .card-price {
      color: var(--brand-primary);
      font-weight: 700;
      font-size: 16px;
    }
  </style>
</head>
<body style="flex-direction: column;">

  <!-- Dashboard Top Navigation Bar -->
  <header class="dashboard-header">
    <a href="dashboard.php" class="brand-logo">
      <svg class="brand-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
      </svg>
      Functional Elegance
    </a>
    <div class="user-pill">
      <span class="user-greeting">Halo, <strong><?php echo htmlspecialchars($user['name']); ?></strong></span>
      <a href="logout.php" class="btn-logout">Keluar</a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="dashboard-main">
    <div class="welcome-banner">
      <h2>Selamat Datang di Functional Elegance</h2>
      <p>Anda berhasil masuk ke portal eksklusif koleksi busana formal. Temukan setelan jas premium dan gaun malam elegan untuk momen terbaik Anda.</p>
    </div>

    <h3 class="catalog-title">Koleksi Terpopuler</h3>
    <div class="catalog-grid">
      <div class="card">
        <div class="card-img" style="background-image: url('assets/hero_image.png');"></div>
        <div class="card-body">
          <h4 class="card-title">Midnight Burgundy Tuxedo Set</h4>
          <p class="card-price">Rp 4.500.000</p>
        </div>
      </div>
      <div class="card">
        <div class="card-img" style="background-image: url('assets/hero_image.png');"></div>
        <div class="card-body">
          <h4 class="card-title">Champagne Gold Couture Kebaya</h4>
          <p class="card-price">Rp 5.200.000</p>
        </div>
      </div>
      <div class="card">
        <div class="card-img" style="background-image: url('assets/hero_image.png');"></div>
        <div class="card-body">
          <h4 class="card-title">Classic Obsidian Tailored Suit</h4>
          <p class="card-price">Rp 3.800.000</p>
        </div>
      </div>
    </div>
  </main>

  <script src="assets/js/main.js"></script>
</body>
</html>
