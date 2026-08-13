<?php
require_once __DIR__ . '/config.php';

// If already logged in AND not inspecting UI preview, redirect to index.php
if (is_logged_in() && !isset($_GET['preview'])) {
    header("Location: index.php");
    exit;
}

$name = '';
$email = '';
$phone = '';
$size = '';
$address = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $size = isset($_POST['size']) ? sanitize_input($_POST['size']) : '';
    $address = isset($_POST['address']) ? sanitize_input($_POST['address']) : '';
    $terms = isset($_POST['terms']);

    if (empty($name)) {
        $errors['name'] = 'Nama lengkap wajib diisi';
    }
    if (empty($email) || !validate_email($email)) {
        $errors['email'] = 'Email tidak valid';
    }
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = 'Minimal 8 karakter';
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Password tidak cocok';
    }
    if (!$terms) {
        $errors['terms'] = 'Anda harus menyetujui syarat & ketentuan';
    }

    if (empty($errors)) {
        try {
            $pdo = get_db_connection();
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert into users table
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'anggota')");
                $stmt->execute([$name, $email, $hashed_password]);
                $user_id = $pdo->lastInsertId();
                
                // Insert into profiles table
                $stmt = $pdo->prepare("INSERT INTO profiles (user_id, no_hp, alamat, ukuran_biasa) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $phone, $address, !empty($size) ? $size : 'M']);

                $_SESSION['user'] = [
                    'id' => $user_id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'size' => $size,
                    'address' => $address,
                    'role' => 'anggota'
                ];
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Gagal mendaftar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun Baru - Functional Elegance</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Fullscreen Centered Container matching Figma Node 1:59 -->
  <div class="register-page-wrapper">
    
    <!-- Background layer with downloaded Figma background image -->
    <div class="register-bg-layer"></div>

    <!-- Centered Glassmorphism Card -->
    <div class="register-card">
      
      <!-- Header -->
      <div class="register-header">
        <h1 class="register-title">Daftar Akun Baru</h1>
        <p class="register-subtitle">Lengkapi data diri Anda untuk memulai pengalaman sewa yang elegan.</p>
      </div>

      <!-- Registration Form -->
      <form class="auth-form" method="POST" action="register.php" novalidate>
        
        <!-- Nama Lengkap -->
        <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
          <label for="name" class="form-label">Nama Lengkap</label>
          <div class="input-container">
            <input 
              type="text" 
              id="name" 
              name="name" 
              class="form-input" 
              value="<?php echo htmlspecialchars($name); ?>" 
              placeholder="Masukkan nama lengkap" 
              required
            >
          </div>
          <?php if (isset($errors['name'])): ?>
            <span class="error-message"><?php echo $errors['name']; ?></span>
          <?php endif; ?>
        </div>

        <!-- Email -->
        <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
          <label for="email" class="form-label">Email</label>
          <div class="input-container">
            <input 
              type="email" 
              id="email" 
              name="email" 
              class="form-input" 
              value="<?php echo htmlspecialchars($email); ?>" 
              placeholder="nama@email.com" 
              required
            >
          </div>
          <?php if (isset($errors['email'])): ?>
            <span class="error-message"><?php echo $errors['email']; ?></span>
          <?php endif; ?>
        </div>

        <!-- No. Handphone -->
        <div class="form-group">
          <label for="phone" class="form-label">No. Handphone</label>
          <div class="input-container">
            <input 
              type="tel" 
              id="phone" 
              name="phone" 
              class="form-input" 
              value="<?php echo htmlspecialchars($phone); ?>" 
              placeholder="08xx xxxx xxxx"
            >
          </div>
        </div>

        <!-- Password -->
        <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
          <label for="password" class="form-label">Password</label>
          <div class="input-container">
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-input" 
              placeholder="Minimal 8 karakter" 
              required
            >
            <button type="button" class="toggle-password-btn" id="togglePassword" aria-label="Toggle password visibility">
              <svg width="22" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
          
          <!-- Indikator Kekuatan Password -->
          <div class="password-strength-wrap">
            <div class="strength-bars">
              <div class="strength-bar" id="bar1"></div>
              <div class="strength-bar" id="bar2"></div>
              <div class="strength-bar" id="bar3"></div>
            </div>
            <span class="strength-text" id="strengthText">Kekuatan password: Lemah</span>
          </div>

          <?php if (isset($errors['password'])): ?>
            <span class="error-message"><?php echo $errors['password']; ?></span>
          <?php endif; ?>
        </div>

        <!-- Konfirmasi Password -->
        <div class="form-group <?php echo isset($errors['confirm_password']) ? 'has-error' : ''; ?>">
          <label for="confirm_password" class="form-label">Konfirmasi Password</label>
          <div class="input-container">
            <input 
              type="password" 
              id="confirm_password" 
              name="confirm_password" 
              class="form-input" 
              placeholder="Ulangi password" 
              required
            >
          </div>
          <?php if (isset($errors['confirm_password'])): ?>
            <span class="error-message"><?php echo $errors['confirm_password']; ?></span>
          <?php endif; ?>
        </div>

        <!-- Ukuran Biasa -->
        <div class="form-group">
          <label for="size" class="form-label">Ukuran Biasa</label>
          <div class="input-container">
            <select id="size" name="size" class="form-select">
              <option value="" disabled selected>Pilih ukuran</option>
              <option value="S" <?php echo $size === 'S' ? 'selected' : ''; ?>>S (Small)</option>
              <option value="M" <?php echo $size === 'M' ? 'selected' : ''; ?>>M (Medium)</option>
              <option value="L" <?php echo $size === 'L' ? 'selected' : ''; ?>>L (Large)</option>
              <option value="XL" <?php echo $size === 'XL' ? 'selected' : ''; ?>>XL (Extra Large)</option>
              <option value="Custom" <?php echo $size === 'Custom' ? 'selected' : ''; ?>>Custom / Ukuran Khusus</option>
            </select>
          </div>
        </div>

        <!-- Alamat Lengkap -->
        <div class="form-group">
          <label for="address" class="form-label">Alamat Lengkap</label>
          <div class="input-container">
            <textarea 
              id="address" 
              name="address" 
              class="form-textarea" 
              placeholder="Masukkan alamat pengiriman lengkap"
            ><?php echo htmlspecialchars($address); ?></textarea>
          </div>
        </div>

        <!-- Syarat & Ketentuan -->
        <div class="form-group <?php echo isset($errors['terms']) ? 'has-error' : ''; ?>">
          <label class="checkbox-container">
            <input type="checkbox" name="terms" required checked>
            <span class="checkbox-label">
              Saya menyetujui <a href="#">Syarat & Ketentuan</a> serta Kebijakan Privasi yang berlaku.
            </span>
          </label>
          <?php if (isset($errors['terms'])): ?>
            <span class="error-message"><?php echo $errors['terms']; ?></span>
          <?php endif; ?>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-submit">
          <span>Daftar</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>

      </form>

      <!-- Footer Link -->
      <div class="form-footer">
        <p class="footer-text">
          Sudah punya akun? 
          <a href="index.php" class="footer-link">Masuk di sini</a>
        </p>
      </div>

    </div>

  </div>

  <script src="assets/js/main.js"></script>
</body>
</html>
