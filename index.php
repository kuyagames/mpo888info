<?php
require_once __DIR__ . '/includes/functions.php';

$s      = get_settings();
$stats  = get_stats();
$groups = get_groups();
$links  = get_links();

$heroImg  = sv($s, 'about_banner');
$heroCls  = $heroImg !== '' ? ' hasimg' : '';
$heroStyle= $heroImg !== '' ? ' style="background-image:url(' . e($heroImg) . ')"' : '';
$artParas = array_filter(array_map('trim', explode("\n", sv($s, 'about_art_body'))));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
$metaTitle = sv($s, 'meta_title', sv($s,'site_name','NAMA') . sv($s,'site_accent','SITUS') . ' — Info Promo & Link Resmi');
$metaDesc  = sv($s, 'meta_desc', 'Info promo, bonus member baru, bonus harian, extra bonus, dan link resmi terbaru. Update setiap hari.');
$siteName  = sv($s,'site_name','NAMA') . sv($s,'site_accent','SITUS');
$ogImgRel  = sv($s, 'about_banner');
$ogImgAbs  = ''; $ogW = 0; $ogH = 0; $ogType = '';
if ($ogImgRel !== '') {
    $ogImgAbs = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'mpo888info.com') . '/' . ltrim($ogImgRel, '/');
    $ogPath   = __DIR__ . '/' . ltrim($ogImgRel, '/');
    if (is_file($ogPath) && ($dim = @getimagesize($ogPath))) { $ogW = (int)$dim[0]; $ogH = (int)$dim[1]; $ogType = $dim['mime'] ?? ''; }
}
$fav     = sv($s,'favicon');
$favType = $fav !== '' ? ((strtolower(pathinfo($fav, PATHINFO_EXTENSION)) === 'png') ? 'image/png'
         : (preg_match('/jpe?g$/i', $fav) ? 'image/jpeg' : '')) : '';
$favW = 0; $favH = 0;
if ($fav !== '') { $fp = __DIR__ . '/' . ltrim($fav, '/'); if (is_file($fp) && ($fd = @getimagesize($fp))) { $favW = (int)$fd[0]; $favH = (int)$fd[1]; } }
?>
<title><?= e($metaTitle) ?></title>
<meta name="description" content="<?= e($metaDesc) ?>">
<?php if ($fav !== ''): ?><link rel="icon"<?= $favType ? ' type="' . $favType . '"' : '' ?> href="<?= e($fav) ?>"<?= ($favW && $favH) ? ' sizes="' . $favW . 'x' . $favH . '"' : '' ?>>
<link rel="shortcut icon" href="/favicon.ico">
<link rel="apple-touch-icon" href="<?= e($fav) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="<?= e($metaTitle) ?>">
<meta property="og:description" content="<?= e($metaDesc) ?>">
<?php if (sv($s,'canonical') !== ''): ?><meta property="og:url" content="<?= e(sv($s,'canonical')) ?>">
<?php endif; ?>
<?php if ($ogImgAbs !== ''): ?><meta property="og:image" content="<?= e($ogImgAbs) ?>">
<meta property="og:image:secure_url" content="<?= e($ogImgAbs) ?>">
<?php if ($ogType !== ''): ?><meta property="og:image:type" content="<?= e($ogType) ?>">
<?php endif; ?>
<?php if ($ogW && $ogH): ?><meta property="og:image:width" content="<?= $ogW ?>">
<meta property="og:image:height" content="<?= $ogH ?>">
<?php endif; ?>
<meta property="og:image:alt" content="<?= e(sv($s,'og_alt')) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($metaTitle) ?>">
<meta name="twitter:description" content="<?= e($metaDesc) ?>">
<meta name="twitter:image" content="<?= e($ogImgAbs) ?>">
<meta name="twitter:image:alt" content="<?= e(sv($s,'og_alt')) ?>">
<?php endif; ?>
<?php if (sv($s,'canonical') !== ''): ?><link rel="canonical" href="<?= e(sv($s,'canonical')) ?>">
<?php endif; ?>
<?php if (sv($s,'google_tag') !== ''): ?><?= sv($s,'google_tag') ?>

<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="bg-fx"><span class="orb o1"></span><span class="orb o2"></span><span class="orb o3"></span></div>
<div class="grid-fx"></div>

<nav>
  <div class="nav-inner">
    <div class="brand"><?php if (sv($s,'logo') !== ''): ?><img class="brand-logo" src="<?= e(sv($s,'logo')) ?>" alt="<?= e(sv($s,'site_name','NAMA') . sv($s,'site_accent','SITUS')) ?>"><?php else: ?><span class="dot">★</span><span><?= e(sv($s,'site_name','NAMA')) ?><b><?= e(sv($s,'site_accent','SITUS')) ?></b></span><?php endif; ?></div>
    <div class="menu">
      <a href="#promo" class="bonus">INFO BONUS</a>
      <a href="#link" class="gold">INFO LINK</a>
    </div>
  </div>
</nav>

<!-- SECTION 1 — ABOUT -->
<section class="hero" id="about">
  <?php if ($heroImg !== '' && sv($s,'about_banner_full') === '1'): ?>
  <div class="hero-banner-full reveal"><img src="<?= e($heroImg) ?>" alt="<?= e(sv($s,'site_name','NAMA') . sv($s,'site_accent','SITUS')) ?>"></div>
  <div class="stats">
    <?php foreach ($stats as $st): ?>
      <div class="stat"><div class="num"><?= e($st['num']) ?></div><div class="lbl"><?= e($st['label']) ?></div></div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="hero-banner<?= $heroCls ?>"<?= $heroStyle ?>>
    <div class="aurora"></div>
    <div class="shine"></div>
    <span class="eyebrow"><?= e(sv($s,'about_eyebrow','Selamat Datang')) ?></span>
    <h1 style="margin-top:18px"><?= e(sv($s,'about_title')) ?> <span class="grad"><?= e(sv($s,'about_subtitle')) ?></span></h1>
    <p class="lead"><?= e(sv($s,'about_lead')) ?></p>
    <div class="hero-cta">
      <a href="#promo" class="btn btn-gold">🎁 Lihat Semua Promo</a>
      <a href="#link" class="btn btn-ghost">🔗 Link Resmi</a>
    </div>
    <div class="stats">
      <?php foreach ($stats as $st): ?>
        <div class="stat"><div class="num"><?= e($st['num']) ?></div><div class="lbl"><?= e($st['label']) ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <article class="article reveal d1">
    <h2><?= e(sv($s,'about_art_head','Tentang Situs Ini')) ?></h2>
    <?php foreach ($artParas as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
  </article>
</section>

<!-- SECTION 2 — BONUS -->
<section id="promo">
  <div class="sec-head reveal">
    <span class="eyebrow"><?= e(sv($s,'menu1','INFO PROMO')) ?></span>
    <h2>Promo Bonus <span class="grad">Pilihan Terbaik</span></h2>
    <p>Mpo888 menyediakan macam promo bonus terbaik seperti bonus member baru, bonus harian, bonus mingguan, hingga extra bonus. Di bawah ini merupakan daftar promo bonus pilihan terbaik yang wajib di coba saat anda bermain di situs mpo888.</p>
  </div>
  <?php foreach ($groups as $g) echo render_group($g); ?>
</section>

<!-- SECTION 3 — INFO LINK -->
<section id="link">
  <div class="sec-head reveal">
    <span class="eyebrow"><?= e(sv($s,'menu2','INFO LINK')) ?></span>
    <h2>Daftar <span class="grad">Link Resmi</span> &amp; Alternatif</h2>
    <p>Gunakan salah satu link resmi di bawah ini. Semua link dipantau dan diperbarui agar selalu dapat diakses.</p>
  </div>
  <div class="links-wrap reveal d1">
    <div class="aurora"></div>
    <div class="link-grid">
      <?php foreach ($links as $l) echo render_link($l); ?>
    </div>
  </div>
</section>

<footer>
  <div class="fbrand"><?= e(sv($s,'site_name','NAMA')) ?><span style="color:var(--gold-soft)"><?= e(sv($s,'site_accent','SITUS')) ?></span></div>
  <p>© <span id="yr"></span> <?= e(sv($s,'site_name','NAMA') . sv($s,'site_accent','SITUS')) ?>.COM — Situs ini berisikan informasi resmi bonus member baru, harian, extra bonus hingga link login terbaru situs mpo888.</p>
</footer>

<button class="toTop" id="toTop" aria-label="Kembali ke atas">↑</button>

<script src="assets/js/app.js"></script>
</body>
</html>
