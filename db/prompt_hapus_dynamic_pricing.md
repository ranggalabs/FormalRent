# PROMPT: HAPUS FITUR DYNAMIC PRICING ENGINE DARI SELURUH PROYEK

Saya memiliki proyek PHP bernama "FormalWear (Functional Elegance)". Saya ingin menghapus fitur **Dynamic Pricing Engine** secara menyeluruh dari semua file proyek.

---

## 🏗️ TECH STACK
- Backend: PHP vanilla
- Database: MySQL via PDO (`formalwear_schema`)
- Frontend: HTML + Vanilla CSS + Vanilla JS
- Server: php -S localhost:8000

---

## 📁 FILE YANG HARUS DIMODIFIKASI

```
FormalWear/
├── config.php       # Hapus fungsi recalculate_catalog_dynamic_prices() & key dynamic_pricing
├── admin.php        # Hapus tab DP, widget dashboard DP, CSS classes DP, sidebar link DP
├── katalog.php      # Hapus banner alert dynamic pricing
└── db/data.json     # Hapus key "dynamic_pricing" dari root object JSON
```

---

## ✅ DAFTAR TUGAS LENGKAP PER FILE

---

### 1. `config.php`

#### HAPUS — Fungsi `recalculate_catalog_dynamic_prices()` (baris 62–94):
```php
// Dynamic Pricing Calculation Function
function recalculate_catalog_dynamic_prices(&$data, $scenario) {
    // ... seluruh isi fungsi ini
}
```

#### HAPUS — Bagian default `dynamic_pricing` di dalam `get_db_data()` (baris 43–52):
```php
if (!isset($data['dynamic_pricing'])) {
    $data['dynamic_pricing'] = [
        'enabled' => true,
        'scenario' => 'wisuda',
        'wisuda_surge' => 20,
        'wedding_surge' => 15,
        'offseason_discount' => 10,
        'last_updated' => date('Y-m-d H:i:s')
    ];
}
```

#### HAPUS — Key `dynamic_pricing` di default return (baris 37):
```php
// Sebelum:
return ['beranda' => [], 'dynamic_pricing' => [], 'katalog' => [], 'users' => []];
// Sesudah:
return ['beranda' => [], 'katalog' => [], 'users' => []];
```

---

### 2. `admin.php`

#### HAPUS — Variabel `$dp` dari inisialisasi atas file:
```php
// Sebelum (baris ~14):
$dp = $data['dynamic_pricing'];
// Hapus baris ini sepenuhnya.
```

#### HAPUS — Action handler POST `update_dynamic_pricing`:
```php
if ($action === 'update_dynamic_pricing') {
    $scenario = sanitize_input($_POST['scenario']);
    $data['dynamic_pricing']['wisuda_surge'] = ...
    // ... seluruh blok ini
}
```

#### HAPUS — Semua pemanggilan `recalculate_catalog_dynamic_prices(...)` di semua handler POST.

#### HAPUS — Refresh `$dp` di bagian "Refresh local copy":
```php
$dp = $data['dynamic_pricing']; // Hapus baris ini
```

#### HAPUS — Di sidebar admin, link tab Dynamic Pricing:
```html
<li class="admin-nav-item">
  <a href="admin.php?tab=dynamic_pricing" ...>
    🏷️ <span>Dynamic Pricing Engine</span>
  </a>
</li>
```

#### HAPUS — Di footer sidebar, teks referensi Dynamic Pricing:
```html
<div style="margin-top: auto; ...">
  Fitur Unggulan Admin:<br>
  <strong ...>🏷️ Dynamic Demand Pricing</strong>
</div>
```

#### UBAH — Judul & subtitle admin header:
```php
// Sebelum:
<h1>Pusat Kontrol Content & Dynamic Pricing</h1>
<p>...dan algoritma penetapan harga otomatis.</p>

// Sesudah:
<h1>Pusat Kontrol Admin Panel</h1>
<p>Kelola materi beranda, produk katalog, dan akun pengguna.</p>
```

#### HAPUS — Di TAB DASHBOARD, stat card `featured` "Dynamic Pricing Status":
```html
<div class="stat-card featured">
  <span class="stat-label">🏷️ Dynamic Pricing Status</span>
  ...<?php $sc = $dp['scenario']; if ($sc === 'wisuda') ...?>...
</div>
```
Ganti dengan stat card baru:
```html
<div class="stat-card">
  <span class="stat-label">Unit Dipinjam</span>
  <span class="stat-value"><?php echo count(array_filter($katalog, function($i){ return strtolower($i['status']) === 'dipinjam'; })); ?> Unit</span>
</div>
```

#### HAPUS — Seluruh blok "DYNAMIC PRICING DASHBOARD HIGHLIGHT BOX":
```html
<!-- DYNAMIC PRICING DASHBOARD HIGHLIGHT BOX -->
<div class="admin-table-card" style="border: 2px solid var(--gold-accent); ...">
  ... seluruh card berisi scenario-grid (4 tombol scenario) ...
</div>
```

#### UBAH — Tabel ringkasan katalog di dashboard:
- Ubah judul: `"Ringkasan Katalog & Live Dynamic Prices"` → `"Ringkasan Katalog"`
- Hapus kolom header: `<th>Status Demand & Price Surge</th>`
- Hapus kolom header: `<th>Harga Terkini Publik</th>`
- Hapus `<td>` yang menampilkan `$item['demand_level']`
- Hapus `<td>` yang menampilkan harga terkini (duplikat)
- Hapus `style="text-decoration: line-through"` dari kolom Harga Dasar

#### HAPUS — Seluruh TAB DYNAMIC PRICING ENGINE CONTROL:
```php
<?php if ($active_tab === 'dynamic_pricing'): ?>
  <!-- seluruh konten tab ini: form update_dynamic_pricing + Live Price Impact Simulator -->
<?php endif; ?>
```

#### UBAH — Di TAB KATALOG, tabel daftar produk:
- Hapus kolom header: `<th>Harga Dynamic</th>`
- Hapus kolom `<td>` yang menampilkan `$item['price']` berlabel "Harga Dynamic"
- Hanya tampilkan satu kolom harga: **Harga Dasar**

#### HAPUS — CSS khusus Dynamic Pricing di dalam blok `<style>` di `admin.php`:
```css
/* Dynamic Pricing Specific Styling */
.scenario-grid { ... }
.scenario-card { ... }
.scenario-card.active { ... }
.scenario-title { ... }
.scenario-badge { ... }
.surge-up { ... }
.surge-discount { ... }
.surge-normal { ... }
```

---

### 3. `katalog.php`

#### HAPUS — Variabel `$dp`:
```php
$dp = isset($data['dynamic_pricing']) ? $data['dynamic_pricing'] : [];
```

#### HAPUS — Banner alert Dynamic Pricing:
```php
<?php if (isset($dp['scenario']) && $dp['scenario'] !== 'normal'): ?>
  <div style="background: linear-gradient(90deg, var(--brand-primary) 0%, #7A1F2B 100%); ...">
    ... seluruh div banner ini ...
  </div>
<?php endif; ?>
```

#### HAPUS — Di loop kartu produk, blok demand_level:
```php
<?php if ($harga != $harga_dasar): ?>
  <div style="font-size: 11px; ...">
    <span>🏷️</span> <?php echo isset($item['demand_level']) ? ... ?>
  </div>
<?php endif; ?>
```

---

### 4. `db/data.json`

#### HAPUS — Key `dynamic_pricing` dari root JSON:
```json
// Sebelum:
{
  "beranda": { ... },
  "dynamic_pricing": {
    "enabled": true,
    "scenario": "wisuda",
    "wisuda_surge": 20,
    "wedding_surge": 15,
    "offseason_discount": 10,
    "last_updated": "..."
  },
  "katalog": [ ... ],
  "users": [ ... ]
}

// Sesudah:
{
  "beranda": { ... },
  "katalog": [ ... ],
  "users": [ ... ]
}
```

---

## ⚠️ ATURAN PENTING

1. Jangan hapus fitur lain: tab Dashboard, Kelola Katalog, Kelola Beranda, Kelola User — tetap dipertahankan.
2. Jangan hapus field `harga_dasar` / `harga_saat_ini` dari MySQL tabel `units` — tetap dibutuhkan.
3. Setelah penghapusan, harga di `katalog.php` cukup menampilkan `$item['harga_saat_ini']` (dari MySQL) tanpa surge/diskon.
4. Pastikan tidak ada PHP error akibat variabel `$dp` yang masih terpanggil di tempat lain.
5. Jangan gunakan framework PHP (Laravel, dll) atau Tailwind CSS.
