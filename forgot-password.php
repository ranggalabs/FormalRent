<?php
require_once __DIR__ . '/config.php';

$message = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    if (validate_email($email)) {
        $message = 'Tautan untuk mengatur ulang password telah dikirim ke email Anda.';
    } else {
        $message = 'Mohon masukkan email yang valid.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password - Functional Elegance</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <div class="login-container">
    
    <div class="hero-side">
      <div class="hero-content">
        <h2 class="hero-title">Pemulihan Akun</h2>
        <p class="hero-subtitle">Kami akan membantu Anda mendapatkan kembali akses ke akun Functional Elegance Anda.</p>
      </div>
    </div>

    <div class="form-side">
      <div class="form-wrapper">
        
        <div class="brand-header">
          <a href="index.php" class="brand-logo">
            <svg class="brand-icon" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Functional Elegance
          </a>
          <h1 class="form-heading">Lupa Password?</h1>
          <p class="form-subheading">Masukkan email Anda untuk menerima instruksi reset password.</p>
        </div>

        <?php if (!empty($message)): ?>
          <div style="padding: 14px; background: #EBF8FF; border: 1px solid #BEE3F8; color: #2B6CB0; border-radius: 6px; font-size: 14px;">
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="forgot-password.php">
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <div class="input-container">
              <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>" placeholder="nama@domain.com" required>
            </div>
          </div>

          <button type="submit" class="btn-submit">Kirim Tautan Reset</button>
        </form>

        <div class="form-footer">
          <p class="footer-text">
            Kembali ke 
            <a href="index.php" class="footer-link">Halaman Masuk</a>
          </p>
        </div>

      </div>
    </div>

  </div>

</body>
</html>
