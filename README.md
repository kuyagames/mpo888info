# Landing Page Info Promo & Link Resmi — PHP/MySQL

Landing page informasi promo + dashboard admin untuk mengelola konten
(edit semua section, tambah/hapus bonus & section, upload banner), dibangun
**tanpa framework** (PHP native + PDO, MySQL, vanilla HTML/CSS/JS). Tema:
dark glassmorphism, aksen ungu–emas. Tiap section memiliki animasi interaktif
(aurora, tilt 3D, running neon border, scroll-reveal).

> File ini juga berfungsi sebagai **konteks untuk Claude di VS Code**. Lihat
> bagian [Untuk Claude (AI assistant)](#untuk-claude-ai-assistant) di bawah.

---

## 1. Struktur Folder

```
promo-landing/
├── index.php                 # Halaman publik (render dari database)
├── config.php                # Kredensial DB & pengaturan upload  ← EDIT INI
├── db.sql                    # Skema + data contoh (import sekali)
├── .htaccess                 # Proteksi file sensitif
├── includes/
│   ├── db.php                # Koneksi PDO (singleton)
│   └── functions.php         # Helper: escaping, CSRF, query, upload, render
├── assets/
│   ├── css/style.css         # Gaya landing page
│   └── js/app.js             # Animasi landing (reveal, tilt, beam, to-top)
├── admin/
│   ├── auth.php              # Guard sesi login
│   ├── setup.php             # Buat admin pertama (HAPUS setelah dipakai)
│   ├── login.php             # Form login
│   ├── logout.php            # Logout
│   ├── dashboard.php         # Panel CRUD semua konten + upload banner
│   └── assets/admin.css      # Gaya panel admin
└── uploads/                  # Gambar banner (harus writable)
    ├── .htaccess             # Nonaktifkan eksekusi skrip di sini
    └── .gitkeep
```

## 2. Arsitektur Singkat

- **Database** sumber kebenaran tunggal. Tabel: `settings` (key-value),
  `stats`, `bonus_groups`, `promos` (FK ke group, `ON DELETE CASCADE`),
  `links`, `admins`.
- **`index.php`** mengambil data via helper di `includes/functions.php`
  (`get_settings`, `get_groups`, `get_promos`, `get_links`) lalu merender HTML.
  Fungsi `render_promo / render_group / render_link` membangun markup.
- **Banner**: kolom `promos.banner` / `settings.about_banner`. Jika berisi
  URL gambar → dipakai sebagai background; jika kosong → gradien warna
  berdasarkan kolom `bnr` (a–f). Peta warna di `bnr_grad()` / `acc_grad()`.
- **Admin** memakai pola **Post-Redirect-Get**: setiap aksi (`*_add`,
  `*_save`, `*_delete`) diproses di atas `dashboard.php`, lalu redirect
  dengan pesan flash di session.
- **Keamanan**: PDO prepared statements, `password_hash`/`password_verify`,
  token CSRF per form, validasi upload (ekstensi + MIME + ukuran),
  `htmlspecialchars` di semua output.

## 3. Prasyarat Server

- Ubuntu + Apache + PHP 8.0+ (ekstensi `pdo_mysql`, `fileinfo`) + MySQL/MariaDB.
- Jika droplet baru, pasang LAMP:
  ```bash
  sudo apt update
  sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql
  sudo systemctl enable --now apache2 mysql
  ```

## 4. Langkah Deploy di DigitalOcean

**a. Upload file** ke web root (mis. `/var/www/html/` atau virtualhost sendiri),
via `git`, `scp`, atau panel. Contoh scp dari lokal:
```bash
scp -r promo-landing/ user@IP_DROPLET:/var/www/promo-landing
```

**b. Buat database & user MySQL:**
```bash
sudo mysql
```
```sql
CREATE DATABASE promo_landing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'promo_user'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT_ANDA';
GRANT ALL PRIVILEGES ON promo_landing.* TO 'promo_user'@'localhost';
FLUSH PRIVILEGES; EXIT;
```

**c. Import skema + data contoh:**
```bash
mysql -u promo_user -p promo_landing < db.sql
```

**d. Edit `config.php`** — isi `DB_NAME`, `DB_USER`, `DB_PASS` sesuai langkah b.

**e. Set izin folder upload** (agar Apache bisa menulis):
```bash
sudo chown -R www-data:www-data /var/www/promo-landing/uploads
sudo chmod -R 775 /var/www/promo-landing/uploads
```

**f. Buat akun admin:** buka `https://domain-anda/admin/setup.php`,
isi username & password, submit. **Lalu HAPUS `admin/setup.php`:**
```bash
rm /var/www/promo-landing/admin/setup.php
```

**g. Login admin:** `https://domain-anda/admin/login.php`
→ kelola konten. Situs publik ada di `https://domain-anda/` (atau `/index.php`).

**h. (Disarankan) HTTPS gratis via Let's Encrypt:**
```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache
```

> Pastikan `AllowOverride All` aktif di konfigurasi Apache agar `.htaccess`
> dibaca, dan modul `rewrite`/`headers` tersedia bila diperlukan.

## 5. Cara Pakai Dashboard Admin

- **Pengaturan Umum & About** — nama situs, label menu, judul/lead banner,
  artikel SEO, upload banner About.
- **Statistik** — tambah/edit/hapus kartu statistik.
- **Grup Bonus** — edit judul (H3), tag, ikon, warna ikon, efek koin;
  **Tambah Grup** = section bonus baru; **Tambah Bonus Baru** di tiap grup.
- Tiap promo: label, ribbon (mis. `HOT`), judul, deskripsi, tombol, URL,
  warna banner, dan **upload gambar banner** (menimpa gradien).
- **Info Link** — tambah/edit/hapus link resmi.

Upload: maks 2MB, format `jpg/jpeg/png/webp/gif` (atur di `config.php`).

## 6. Keamanan — Checklist

- [ ] Ganti `DB_PASS` dengan password kuat.
- [ ] **Hapus `admin/setup.php`** setelah membuat admin.
- [ ] Pastikan `uploads/.htaccess` ikut terupload (cegah eksekusi skrip).
- [ ] Aktifkan HTTPS (certbot).
- [ ] Jangan commit `config.php` berisi password asli ke repo publik.

---

## Untuk Claude (AI assistant)

Konteks singkat kalau kamu melanjutkan project ini di VS Code:

**Stack & aturan:** PHP native (TANPA framework, TANPA Composer), MySQL via
PDO, vanilla HTML/CSS/JS. Jangan tambahkan dependency/package manager. Target
deploy: droplet LAMP DigitalOcean (Ubuntu/Apache), timezone WIB.

**Konvensi kode:**
- Semua query pakai **prepared statements** (`db()->prepare(...)->execute([...])`).
- Semua output ke HTML dibungkus `e()` (htmlspecialchars).
- Setiap form admin wajib `csrf_field()` + handler memanggil `csrf_check()`.
- Aksi admin pakai pola Post-Redirect-Get via `switch($_POST['action'])` di
  atas `dashboard.php`, diakhiri `flash()` + `back()`.
- Upload lewat `handle_upload($field, $err)` di `functions.php`.
- Peta warna banner (`bnr_grad`) & aksen ikon (`acc_grad`) harus sinkron
  antara `functions.php` (render) dan opsi `<select>` di `dashboard.php`.
- Nama tabel `bonus_groups` (bukan `groups`, karena kata kunci SQL).

**Sudah selesai:** landing page dinamis dari DB, admin login + CRUD penuh
untuk settings/stats/groups/promos/links, upload banner server-side, seluruh
animasi section.

**Ide pengembangan lanjutan (kalau diminta user):**
- Reorder drag-and-drop (kolom `sort` sudah ada, tinggal UI + endpoint).
- Multi-admin + ganti password dari dashboard.
- Kompres/resize gambar saat upload (GD/Imagick).
- Cache render `index.php` ke HTML statis untuk performa.
- Halaman per-promo (detail) + slug SEO.
- CSRF token rotation & rate limit login.

**Uji cepat lokal (opsional):** `php -S localhost:8000` di root project
(butuh MySQL aktif + `config.php` terisi).
