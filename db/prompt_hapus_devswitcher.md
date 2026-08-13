# PROMPT: HAPUS FITUR PM DEVSWITCHER DARI SELURUH PROYEK

Saya memiliki proyek PHP bernama "FormalWear (Functional Elegance)". Tolong hapus fitur **PM DevSwitcher / PM UI Review Inspector** secara total dari semua file proyek.

---

## 🏗️ TECH STACK
- Backend: PHP vanilla
- Frontend: HTML + Vanilla CSS + Vanilla JS
- Server: php -S localhost:8000

---

## 📁 DAFTAR FILE YANG HARUS DIMODIFIKASI / DIHAPUS

```
FormalWear/
├── dev_switcher.php      ← HAPUS FILE INI SEPENUHNYA
├── config.php            ← Hapus handler logic login_as=pm
├── index.php             ← Hapus include dev_switcher.php
├── katalog.php           ← Hapus include dev_switcher.php
├── dashboard.php         ← Hapus include dev_switcher.php
├── assets/js/main.js     ← Hapus event listener DevSwitcher
└── assets/css/style.css  ← Hapus CSS block DEV SWITCHER ROLE COMPONENT
```

---

## ✅ DAFTAR TUGAS DETIL PER FILE

### 1. Hapus File `dev_switcher.php`
Hapus file `dev_switcher.php` di root direktori proyek.

---

### 2. `config.php`
Hapus blok handler `login_as` PM (baris 49–58):
```php
// HAPUS BLOK INI:
// Handle PM DevSwitcher Quick Login Mode
if (isset($_GET['login_as']) && $_GET['login_as'] === 'pm') {
    $_SESSION['user'] = [
        'email' => 'pm.lead@functionalelegance.com',
        'name' => 'Project Manager',
        'role' => 'PM / Reviewer'
    ];
    header("Location: index.php");
    exit;
}
```

---

### 3. `index.php`
Hapus baris include `dev_switcher.php` (baris ~320):
```php
// HAPUS BARIS INI:
<?php require_once __DIR__ . '/dev_switcher.php'; ?>
```

---

### 4. `katalog.php`
Hapus baris include `dev_switcher.php` (baris ~283):
```php
// HAPUS BARIS INI:
<?php require_once __DIR__ . '/dev_switcher.php'; ?>
```

---

### 5. `dashboard.php`
Hapus baris include `dev_switcher.php` (baris ~170):
```php
// HAPUS BARIS INI:
<?php require_once __DIR__ . '/dev_switcher.php'; ?>
```

---

### 6. `assets/js/main.js`
Hapus blok listener DevSwitcher (baris 132–145):
```javascript
// HAPUS BLOK INI:
// DevSwitcher PM Tool Toggle
const devSwitcher = document.getElementById('devSwitcher');
const devSwitcherToggle = document.getElementById('devSwitcherToggle');
const devSwitcherClose = document.getElementById('devSwitcherClose');

if (devSwitcher && devSwitcherToggle && devSwitcherClose) {
  devSwitcherToggle.addEventListener('click', () => {
    devSwitcher.classList.add('open');
  });

  devSwitcherClose.addEventListener('click', () => {
    devSwitcher.classList.remove('open');
  });
}
```

---

### 7. `assets/css/style.css`
Hapus seluruh section CSS `DEV SWITCHER ROLE COMPONENT` (baris 308–464):
```css
/* HAPUS SELURUH BLOK DARI MARGIN/COMMENT INI HINGGA AKHIR BLOK DEVSWITCHER */
/* ==========================================================================
   DEV SWITCHER ROLE COMPONENT (PM REVIEW WIDGET)
   ========================================================================== */
.dev-switcher-wrap { ... }
.dev-switcher-trigger { ... }
.dev-switcher-panel { ... }
.dev-switcher-header { ... }
.dev-switcher-title { ... }
.dev-switcher-close-btn { ... }
.dev-switcher-section-title { ... }
.dev-switcher-links { ... }
.dev-link-pill { ... }
.dev-switcher-footer { ... }
.status-dot { ... }
```

---

## ⚠️ ATURAN PENTING
1. Hapus fitur DevSwitcher tanpa mengganggu widget Chatbot Modal atau komponen UI lainnya.
2. Pastikan tidak ada `require_once` yang mengarah ke `dev_switcher.php` yang tersisa.
3. Setelah penghapusan, pastikan tidak ada error JavaScript di console browser.
