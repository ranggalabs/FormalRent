# PANDUAN PROMPT GEMINI FOR PRESENTATION & DEFENSE
## Proyek: FormalWear (FormalRent) — Web Penyewaan Busana Formal Berbasis Dynamic Pricing

Dokumen ini dirancang sebagai **Master Prompt untuk Gemini** agar Anda dapat menggunakannya saat berlatih presentasi, menyusun slide, maupun menghadapi sesi tanya jawab (*Q&A defense*) di depan penguji/dosen.

---

## 📌 1. PETUNJUK PENGGUNAAN PROMPT UNTUK GEMINI

1. Buka [Gemini](https://gemini.google.com/).
2. Salin (**Copy**) seluruh teks pada kotak **PROMPT UTAMA FOR GEMINI** di bawah ini.
3. Tempel (**Paste**) ke kolom obrolan Gemini dan tekan **Enter**.
4. Gemini akan bertindak sebagai **Dosen Penguji Senior / Mentor Presentasi** yang akan memandu Anda melakukan simulasi presentasi dan latihan tanya jawab secara interaktif.

---

## 🚀 2. PROMPT UTAMA FOR GEMINI (SIAP COPY-PASTE)

```text
Halo Gemini! Saya ingin melakukan simulasi presentasi dan latihan tanya jawab (Q&A Defense) untuk proyek akhir web saya yang bernama "FormalWear (FormalRent)". 

Bertindaklah sebagai Dosen Penguji Senior yang kritis namun konstruktif. Bantu saya menyusun naskah presentasi yang memukau dan persiapkan saya menghadapi pertanyaan penguji.

Berikut adalah latar belakang dan rincian teknis proyek saya:

=== LATAR BELAKANG & ALASAN MEMBUAT PROYEK ===
1. Efisiensi Biaya & Keberlanjutan (Sustainability): Busana formal (jas wisuda, tuxedo pernikahan, kebaya, batik sutra) umumnya hanya digunakan 1-2 kali untuk acara khusus. Membeli busana formal baru membutuhkan biaya tinggi dan kurang ekonomis. Penyewaan busana adalah solusi konsumsi berkelanjutan (sharing economy) yang hemat biaya.
2. Inovasi Dynamic Pricing Engine: Bisnis persewaan busana sangat dipengaruhi oleh musim (wisuda, musim nikah / high season vs off-season). Aplikasi ini dilengkapi algoritma "Dynamic Pricing Engine" otomatis yang menyesuaikan harga sewa berdasarkan lonjakan permintaan musim tertentu (misal: wisuda surge).
3. Arsitektur Lightweight & Flexible (JSON Data Store): Aplikasi dibangun menggunakan PHP Native tanpa ketergantungan database SQL eksternal (MySQL/PostgreSQL). Seluruh data dikelola via JSON Data Store (db/data.json) sehingga aplikasi sangat ringan, portable, cepat di-deploy, dan ideal untuk UMKM penyewaan busana.
4. User Experience & Chatbot Assistant: Dilengkapi fitur Chatbot Modal interaktif untuk konsultasi ukuran (fitting) dan rekomendasi sewa secara otomatis bagi calon penyewa.

=== TUJUAN PROYEK ===
1. Menyediakan platform katalog penyewaan busana formal yang modern, transparan, dan mudah digunakan oleh calon penyewa.
2. Membantu pemilik bisnis persewaan mengoptimalkan pendapatan melalui penyesuaian harga sewa otomatis berbasis musim (Dynamic Pricing).
3. Membuktikan bahwa aplikasi web fungsional yang stabil dan secure dapat dibangun dengan arsitektur data JSON yang ringan tanpa overload infrastruktur database.

=== FITUR UTAMA APLIKASI ===
- Landing Page (index.php): Hero section, keunggulan layanan, kategori busana (Jas, Tuxedo, Kebaya, Batik, Toga).
- Katalog & Filter Dynamic Pricing (katalog.php): Tampilan produk sewa, filter kategori, sorting harga terendah/tertinggi, indikator harga musim (wisuda/wedding surge).
- Dashboard Management (admin.php): Kelola produk sewa, atur rasio Surge Dynamic Pricing, manajemen user & pesanan.
- Authentication & Security (login.php, register.php, forgot-password.php): Sanitasi input data, manajemen sesi PHP aman, reset kredensial.
- Interactive Fitting Chatbot (chatbot_modal.php): Asisten panduan ukuran dan prosedur sewa.

=== TUGAS UNTUK GEMINI ===
Berdasarkan data di atas, bantu saya menyediakan 4 hal berikut:
1. Naskah Presentasi 5 Menit (Struktur: Pembuka, Latar Belakang & Masalah, Solusi & Inovasi Dynamic Pricing, Demo Singkat Fitur, Kesimpulan).
2. Poin-poin Key Takeaways untuk Slide Presentasi (maksimal 5 slide utama).
3. 5 Pertanyaan Tersulit dari Penguji beserta Jawaban Jawaban Terbaik (meliputi aspek arsitektur data JSON vs SQL, keamanan, dan cara kerja algoritma Dynamic Pricing).
4. Panduan Sikap & Penyampaian saat Mengadapi Pertanyaan Penguji.

Gunakan bahasa Indonesia yang profesional, tegas, dan akademis!
```

---

## 📂 3. LOKASI PENYIMPANAN DOKUMEN PROMPT

Dokumen prompt ini tersimpan secara permanen di dalam folder proyek FormalWear pada path berikut:

📍 **Path Berkas:**
```text
C:\Users\Rangga Prasetya\Documents\FormalWear\PROMPT_PRESENTASI_GEMINI.md
```

---

## 🎯 4. RINGKASAN CEKLIS PERSIAPAN PRESENTASI

| No | Komponen Presentasi | Status | Catatan Persiapan |
|---|---|---|---|
| 1 | **Tujuan Proyek** | ✅ Siap | Menjelaskan platform sewa busana formal + *Dynamic Pricing Engine*. |
| 2 | **Alasan Proyek** | ✅ Siap | *Sharing economy*, hemat biaya, *surge pricing* musiman, & *lightweight JSON architecture*. |
| 3 | **Struktur Berkas & Git** | ✅ Terhubung | Repositori GitHub: [`https://github.com/ranggalabs/FormalRent`](https://github.com/ranggalabs/FormalRent) |
| 4 | **Server Demo Lokal** | ✅ Siap | Dapat dijalankan via `php -S localhost:8000` dari folder proyek. |
