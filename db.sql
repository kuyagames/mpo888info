-- =====================================================================
--  SKEMA DATABASE + DATA CONTOH
--  Import: mysql -u USER -p promo_landing < db.sql
--  (buat dulu database-nya, lihat README.md)
-- =====================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ---------- settings (key-value) ----------
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
  skey   VARCHAR(64)  NOT NULL PRIMARY KEY,
  svalue TEXT         NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (skey, svalue) VALUES
('site_name',       'NAMA'),
('site_accent',     'SITUS'),
('menu1',           'INFO PROMO'),
('menu2',           'INFO LINK'),
('about_eyebrow',   'Selamat Datang'),
('about_title',     'Pusat Info Promo & Link Resmi'),
('about_subtitle',  'Terupdate Setiap Hari'),
('about_lead',      'Satu tempat untuk semua informasi promo, bonus, dan link alternatif resmi. Dapatkan update tercepat, akses aman, dan daftar bonus terlengkap.'),
('about_banner',    ''),
('about_art_head',  'Tentang Situs Ini'),
('about_art_body',  'Halaman ini adalah pusat informasi resmi yang menyajikan daftar promo dan link alternatif terbaru secara transparan. Ganti teks ini dengan artikel SEO Anda.\nSetiap promo telah diperbarui dan diverifikasi: bonus member baru, bonus harian, hingga extra bonus bernilai terbesar.');

-- ---------- stats ----------
DROP TABLE IF EXISTS stats;
CREATE TABLE stats (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  num   VARCHAR(40) NOT NULL,
  label VARCHAR(80) NOT NULL,
  sort  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO stats (num, label, sort) VALUES
('24 Jam', 'Update Non-Stop', 1),
('8+',     'Jenis Bonus',     2),
('100%',   'Link Resmi',      3);

-- ---------- bonus_groups ----------
DROP TABLE IF EXISTS bonus_groups;
CREATE TABLE bonus_groups (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  title  VARCHAR(120) NOT NULL,
  tag    VARCHAR(60)  NOT NULL DEFAULT '',
  icon   VARCHAR(16)  NOT NULL DEFAULT '🎁',
  accent VARCHAR(16)  NOT NULL DEFAULT 'purple',  -- purple | gold | pink
  coin   TINYINT(1)   NOT NULL DEFAULT 0,
  sort   INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO bonus_groups (id, title, tag, icon, accent, coin, sort) VALUES
(1, 'BONUS MEMBER BARU', 'NEW MEMBER',    '🎉', 'purple', 0, 1),
(2, 'BONUS HARIAN',      'SETIAP HARI',   '📅', 'gold',   1, 2),
(3, 'EXTRA BONUS',       'NILAI TERBESAR','💎', 'pink',   0, 3);

-- ---------- promos ----------
DROP TABLE IF EXISTS promos;
CREATE TABLE promos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  group_id    INT NOT NULL,
  label       VARCHAR(60)  NOT NULL DEFAULT 'PROMO',
  title       VARCHAR(160) NOT NULL DEFAULT '',
  description TEXT         NOT NULL,
  bnr         CHAR(1)      NOT NULL DEFAULT 'a',   -- a..f (warna gradien)
  banner      VARCHAR(255) NOT NULL DEFAULT '',    -- URL gambar (menimpa gradien)
  ribbon      VARCHAR(30)  NOT NULL DEFAULT '',    -- mis. HOT / BIG
  btn         VARCHAR(60)  NOT NULL DEFAULT 'Klaim',
  url         VARCHAR(255) NOT NULL DEFAULT '#',
  sort        INT NOT NULL DEFAULT 0,
  FOREIGN KEY (group_id) REFERENCES bonus_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO promos (group_id, label, title, description, bnr, ribbon, btn, url, sort) VALUES
(1, 'PROMO A', 'Promo A — Member Baru', 'Tulis deskripsi promo di sini. Jelaskan nilai bonus dan syarat klaim untuk member baru.', 'a', 'HOT', 'Klaim Sekarang', '#', 1),
(1, 'PROMO B', 'Promo B — Member Baru', 'Tulis deskripsi promo di sini. Cantumkan keunggulan penawaran dan periode berlaku.', 'b', '', 'Klaim Sekarang', '#', 2),
(2, 'PROMO A', 'Promo A — Harian', 'Bonus harian yang bisa diklaim berulang. Jelaskan batas klaim per hari.', 'c', '', 'Klaim Hari Ini', '#', 1),
(2, 'PROMO B', 'Promo B — Harian', 'Sebutkan nilai cashback/rebate harian dan jadwal pembagiannya.', 'e', 'HOT', 'Klaim Hari Ini', '#', 2),
(3, 'PROMO A', 'Extra Promo A', 'Deskripsi singkat promo extra bonus A.', 'd', 'BIG', 'Detail', '#', 1),
(3, 'PROMO B', 'Extra Promo B', 'Deskripsi singkat promo extra bonus B.', 'f', '', 'Detail', '#', 2),
(3, 'PROMO C', 'Extra Promo C', 'Deskripsi singkat promo extra bonus C.', 'b', '', 'Detail', '#', 3),
(3, 'PROMO D', 'Extra Promo D', 'Deskripsi singkat promo extra bonus D.', 'c', '', 'Detail', '#', 4);

-- ---------- links ----------
DROP TABLE IF EXISTS links;
CREATE TABLE links (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  icon   VARCHAR(16)  NOT NULL DEFAULT '🔗',
  title  VARCHAR(120) NOT NULL,
  status VARCHAR(60)  NOT NULL DEFAULT 'Online',
  url    VARCHAR(255) NOT NULL DEFAULT '#',
  sort   INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO links (icon, title, status, url, sort) VALUES
('🔗', 'Link Resmi 1',      'Online',         '#', 1),
('🔗', 'Link Resmi 2',      'Online',         '#', 2),
('🔗', 'Link Alternatif 1', 'Online',         '#', 3),
('🔗', 'Link Alternatif 2', 'Online',         '#', 4),
('📱', 'Mobile / APK',      'Akses lewat HP', '#', 5),
('💬', 'Livechat / CS',     'Bantuan 24 jam', '#', 6);

-- ---------- admins ----------
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(60)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Akun admin dibuat lewat admin/setup.php (sekali jalan). Lihat README.md

SET foreign_key_checks = 1;
