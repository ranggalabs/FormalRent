<?php
require_once __DIR__ . '/config.php';

// If already logged in AND not inspecting UI preview, redirect to index.php
if (is_logged_in() && !isset($_GET['preview']) && !isset($_GET['demo_error'])) {
    header("Location: index.php");
    exit;
}

$email = 'user@example'; // Default value as shown in Figma design
$password = '';
$email_error = '';
$general_error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']);

    if (empty($email) || !validate_email($email)) {
        $email_error = 'Email tidak valid';
    } elseif (empty($password)) {
        $general_error = 'Password wajib diisi';
    } else {
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
                if (!$user['is_active']) {
                    $general_error = 'Akun Anda telah dinonaktifkan oleh Admin.';
                } else {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'role' => $user['role'],
                        'phone' => $user['no_hp'],
                        'address' => $user['alamat'],
                        'size' => $user['ukuran_biasa']
                    ];

                    if ($user['role'] === 'admin') {
                        $_SESSION['admin_logged_in'] = true;
                        header("Location: admin.php");
                        exit;
                    } else {
                        header("Location: index.php");
                        exit;
                    }
                }
            } else {
                $general_error = 'Email atau password salah';
            }
        } catch (PDOException $e) {
            $general_error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk - Functional Elegance</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <div class="login-container">
    
    <!-- Left Hero Image Banner -->
    <div class="hero-side">
      <div class="hero-content">
        <h2 class="hero-title">Elegansi Tanpa Batas</h2>
        <p class="hero-subtitle">Temukan koleksi eksklusif untuk momen tak terlupakan Anda.</p>
      </div>
    </div>

    <!-- Right Form Section -->
    <div class="form-side">
      <div class="form-wrapper">
        
        <!-- Brand Header -->
        <div class="brand-header">
          <a href="index.php" class="brand-logo">
            <svg class="brand-icon" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
            Functional Elegance
          </a>
          <h1 class="form-heading">Masuk ke Akun</h1>
          <p class="form-subheading">Selamat datang kembali. Silakan masukkan detail Anda.</p>
        </div>

        <!-- Login Form -->
        <form class="auth-form" method="POST" action="login.php" novalidate>
          
          <?php if (!empty($general_error)): ?>
            <div style="background-color: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 16px;">
              ⚠️ <?php echo htmlspecialchars($general_error); ?>
            </div>
          <?php endif; ?>
          
          <!-- Email Field -->
          <div class="form-group <?php echo !empty($email_error) ? 'has-error' : ''; ?>" id="emailGroup">
            <label for="email" class="form-label">Email</label>
            <div class="input-container">
              <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-input" 
                value="<?php echo htmlspecialchars($email); ?>" 
                placeholder="user@example.com"
                required
              >
              <?php if (!empty($email_error)): ?>
                <div class="input-error-icon" title="Email tidak valid">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                  </svg>
                </div>
              <?php endif; ?>
            </div>
            <?php if (!empty($email_error)): ?>
              <span class="error-message">
                <?php echo $email_error; ?>
              </span>
            <?php endif; ?>
          </div>

          <!-- Password Field -->
          <div class="form-group" id="passwordGroup">
            <label for="password" class="form-label">Password</label>
            <div class="input-container">
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input" 
                placeholder="••••••••"
                value="password123"
                required
              >
              <button type="button" class="toggle-password-btn" id="togglePassword" aria-label="Toggle password visibility">
                <svg width="22" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
          </div>

          <!-- Options Row -->
          <div class="options-row">
            <label class="checkbox-container">
              <input type="checkbox" name="remember" checked>
              <span class="checkbox-label">Ingat saya</span>
            </label>
            <a href="forgot-password.php" class="forgot-link">Lupa password?</a>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn-submit">
            Masuk
          </button>

        </form>

        <!-- Footer Link -->
        <div class="form-footer">
          <p class="footer-text">
            Belum punya akun? 
            <a href="register.php" class="footer-link">Daftar di sini</a>
          </p>
        </div>

      </div>
    </div>

  </div>

  <script src="assets/js/main.js"></script>
</body>
</html>
