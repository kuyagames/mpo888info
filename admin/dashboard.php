<?php
require_once __DIR__ . '/auth.php';
require_login();

function flash(string $m): void { $_SESSION['flash'] = $m; }
function back(): void { header('Location: dashboard.php'); exit; }

/* ================= POST HANDLER (Pattern: Post-Redirect-Get) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    try {
        switch ($a) {

            /* ---- Pengaturan umum + banner About ---- */
            case 'settings_save':
                foreach (['site_name','site_accent','menu1','menu2','about_eyebrow',
                          'about_title','about_subtitle','about_lead','about_art_head','about_art_body',
                          'meta_title','meta_desc','google_tag','canonical','about_banner_full'] as $k) {
                    set_setting($k, $_POST[$k] ?? '');
                }
                $err = null; $url = handle_upload('about_banner_file', $err);
                if ($err)          flash('⚠ ' . $err);
                elseif ($url)      { set_setting('about_banner', $url); flash('Pengaturan & banner disimpan.'); }
                elseif (!empty($_POST['about_banner_clear'])) { set_setting('about_banner', ''); flash('Pengaturan disimpan, banner dihapus.'); }
                else               flash('Pengaturan disimpan.');
                back();

            /* ---- Statistik ---- */
            case 'stat_add':
                db()->prepare('INSERT INTO stats (num,label,sort) VALUES (?,?,?)')
                    ->execute([$_POST['num'] ?: '0', $_POST['label'] ?: 'Label', 99]);
                flash('Statistik ditambah.'); back();
            case 'stat_save':
                db()->prepare('UPDATE stats SET num=?,label=? WHERE id=?')
                    ->execute([$_POST['num'], $_POST['label'], (int)$_POST['id']]);
                flash('Statistik disimpan.'); back();
            case 'stat_delete':
                db()->prepare('DELETE FROM stats WHERE id=?')->execute([(int)$_POST['id']]);
                flash('Statistik dihapus.'); back();

            /* ---- Grup bonus ---- */
            case 'group_add':
                db()->prepare('INSERT INTO bonus_groups (title,tag,icon,accent,coin,sort) VALUES (?,?,?,?,?,?)')
                    ->execute(['GRUP BONUS BARU','TAG','🎁','purple',0,99]);
                flash('Grup ditambah.'); back();
            case 'group_save':
                db()->prepare('UPDATE bonus_groups SET title=?,tag=?,icon=?,accent=?,coin=? WHERE id=?')
                    ->execute([$_POST['title'],$_POST['tag'],$_POST['icon'],$_POST['accent'],
                               isset($_POST['coin'])?1:0,(int)$_POST['id']]);
                flash('Grup disimpan.'); back();
            case 'group_delete':
                db()->prepare('DELETE FROM bonus_groups WHERE id=?')->execute([(int)$_POST['id']]);
                flash('Grup dihapus (beserta promonya).'); back();

            /* ---- Promo / bonus ---- */
            case 'promo_add':
                db()->prepare('INSERT INTO promos (group_id,label,title,description,bnr,ribbon,btn,url,sort)
                               VALUES (?,?,?,?,?,?,?,?,?)')
                    ->execute([(int)$_POST['group_id'],'PROMO BARU','Judul Promo Baru',
                               'Deskripsi promo baru.','a','','Klaim','#',99]);
                flash('Bonus ditambah.'); back();
            case 'promo_save':
                $err = null; $url = handle_upload('banner_file', $err);
                if ($err) { flash('⚠ ' . $err); back(); }
                $base = [$_POST['label'],$_POST['title'],$_POST['description'],
                         $_POST['bnr'],$_POST['ribbon'],$_POST['btn'],$_POST['url']];
                if ($url !== null) {
                    db()->prepare('UPDATE promos SET label=?,title=?,description=?,bnr=?,ribbon=?,btn=?,url=?,banner=? WHERE id=?')
                        ->execute(array_merge($base, [$url, (int)$_POST['id']]));
                } elseif (!empty($_POST['banner_clear'])) {
                    db()->prepare('UPDATE promos SET label=?,title=?,description=?,bnr=?,ribbon=?,btn=?,url=?,banner=? WHERE id=?')
                        ->execute(array_merge($base, ['', (int)$_POST['id']]));
                } else {
                    db()->prepare('UPDATE promos SET label=?,title=?,description=?,bnr=?,ribbon=?,btn=?,url=? WHERE id=?')
                        ->execute(array_merge($base, [(int)$_POST['id']]));
                }
                flash('Bonus disimpan.'); back();
            case 'promo_delete':
                db()->prepare('DELETE FROM promos WHERE id=?')->execute([(int)$_POST['id']]);
                flash('Bonus dihapus.'); back();

            /* ---- Link ---- */
            case 'link_add':
                db()->prepare('INSERT INTO links (icon,title,status,url,sort) VALUES (?,?,?,?,?)')
                    ->execute(['🔗','Link Baru','Online','#',99]);
                flash('Link ditambah.'); back();
            case 'link_save':
                db()->prepare('UPDATE links SET icon=?,title=?,status=?,url=? WHERE id=?')
                    ->execute([$_POST['icon'],$_POST['title'],$_POST['status'],$_POST['url'],(int)$_POST['id']]);
                flash('Link disimpan.'); back();
            case 'link_delete':
                db()->prepare('DELETE FROM links WHERE id=?')->execute([(int)$_POST['id']]);
                flash('Link dihapus.'); back();
        }
    } catch (Throwable $ex) {
        flash('Error: ' . $ex->getMessage());
    }
    back();
}

/* ================= DATA UNTUK TAMPILAN ================= */
$flash  = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$s      = get_settings();
$stats  = get_stats();
$groups = get_groups();
$links  = get_links();

function sel($a, $b) { return $a === $b ? ' selected' : ''; }
$bnrOpts = function ($v) { $o=''; foreach (['a','b','c','d','e','f'] as $k) $o.='<option value="'.$k.'"'.sel($k,$v).'>Gradien '.strtoupper($k).'</option>'; return $o; };
$accOpts = function ($v) { $o=''; foreach (['purple','gold','pink'] as $k) $o.='<option value="'.$k.'"'.sel($k,$v).'>'.$k.'</option>'; return $o; };
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Admin</title><link rel="stylesheet" href="assets/admin.css"></head>
<body>
<header class="topbar">
  <div class="tb-brand"><span class="dot">⚙</span> Dashboard <b>Admin</b></div>
  <div class="tb-actions">
    <a class="btn sm" href="../index.php" target="_blank">👁 Lihat Situs</a>
    <span class="who">👤 <?= e($_SESSION['admin_user'] ?? '') ?></span>
    <a class="btn sm danger" href="logout.php">Keluar</a>
  </div>
</header>

<?php if ($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>

<main class="wrap">

  <!-- ========== PENGATURAN UMUM ========== -->
  <section class="panel">
    <h2>🏷️ Pengaturan Umum &amp; About</h2>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="settings_save">
      <div class="g2">
        <label>Nama (depan)<input name="site_name" value="<?= e(sv($s,'site_name')) ?>"></label>
        <label>Nama (aksen emas)<input name="site_accent" value="<?= e(sv($s,'site_accent')) ?>"></label>
      </div>
      <div class="g2">
        <label>Eyebrow Section Bonus (teks kecil di atas judul bonus)<input name="menu1" value="<?= e(sv($s,'menu1')) ?>"></label>
        <label>Label Menu Link (tombol emas di navbar)<input name="menu2" value="<?= e(sv($s,'menu2')) ?>"></label>
      </div>
      <p style="margin:-4px 0 12px;font-size:.82rem;opacity:.75">ℹ️ Teks <b>3 menu bonus</b> di navbar otomatis mengikuti <b>Judul Grup</b> — ubah di panel 🎁 Grup Bonus &amp; Promo di bawah.</p>
      <label>Eyebrow<input name="about_eyebrow" value="<?= e(sv($s,'about_eyebrow')) ?>"></label>
      <div class="g2">
        <label>Judul Banner<input name="about_title" value="<?= e(sv($s,'about_title')) ?>"></label>
        <label>Sub-judul Banner<input name="about_subtitle" value="<?= e(sv($s,'about_subtitle')) ?>"></label>
      </div>
      <label>Teks Lead<textarea name="about_lead"><?= e(sv($s,'about_lead')) ?></textarea></label>
      <div class="uploader">
        <div class="thumb"><?php if (sv($s,'about_banner')): ?><img src="../<?= e(sv($s,'about_banner')) ?>"><?php else: ?>Aurora (tanpa gambar)<?php endif; ?></div>
        <div class="uc"><label class="file">📁 Banner About<input type="file" name="about_banner_file" accept="image/*"></label>
        <label class="chk"><input type="checkbox" name="about_banner_full" value="1" <?= sv($s,'about_banner_full')==='1'?'checked':'' ?>> Gambar penuh (tanpa teks hero)</label>
        <?php if (sv($s,'about_banner')): ?><label class="chk"><input type="checkbox" name="about_banner_clear" value="1"> Hapus gambar</label><?php endif; ?></div>
      </div>
      <label>Judul Artikel SEO<input name="about_art_head" value="<?= e(sv($s,'about_art_head')) ?>"></label>
      <label>Isi Artikel SEO (satu baris = satu paragraf)<textarea name="about_art_body" rows="5"><?= e(sv($s,'about_art_body')) ?></textarea></label>

      <h3 style="margin:22px 0 2px">🔎 SEO &amp; Verifikasi</h3>
      <label>Meta Title (judul tab &amp; hasil Google — kosong = otomatis dari nama situs)<input name="meta_title" value="<?= e(sv($s,'meta_title')) ?>"></label>
      <label>Meta Description<textarea name="meta_desc" rows="2"><?= e(sv($s,'meta_desc')) ?></textarea></label>
      <label>Canonical URL (mis. https://mpo888info.com/resmi)<input name="canonical" value="<?= e(sv($s,'canonical')) ?>"></label>
      <label>Google Tag / Verifikasi (tempel tag lengkap: &lt;meta name="google-site-verification" ...&gt; atau script GA/GTM)<textarea name="google_tag" rows="3"><?= e(sv($s,'google_tag')) ?></textarea></label>

      <button class="btn gold" type="submit">💾 Simpan Pengaturan</button>
    </form>
  </section>

  <!-- ========== STATISTIK ========== -->
  <section class="panel">
    <h2>📊 Statistik Banner</h2>
    <?php foreach ($stats as $st): ?>
    <form method="post" class="rowcard">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$st['id'] ?>">
      <input name="num" value="<?= e($st['num']) ?>" placeholder="Angka">
      <input name="label" value="<?= e($st['label']) ?>" placeholder="Label">
      <button class="btn sm" name="action" value="stat_save">Simpan</button>
      <button class="btn sm danger" name="action" value="stat_delete" onclick="return confirm('Hapus?')">🗑</button>
    </form>
    <?php endforeach; ?>
    <form method="post"><?= csrf_field() ?><button class="btn pur" name="action" value="stat_add">➕ Tambah Statistik</button></form>
  </section>

  <!-- ========== GRUP BONUS ========== -->
  <section class="panel">
    <h2>🎁 Grup Bonus &amp; Promo</h2>
    <?php foreach ($groups as $g): $promos = get_promos((int)$g['id']); ?>
    <div class="group-box">
      <form method="post" class="group-head">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
        <div class="g2">
          <label>Judul Grup (H3)<input name="title" value="<?= e($g['title']) ?>"></label>
          <label>Tag<input name="tag" value="<?= e($g['tag']) ?>"></label>
        </div>
        <div class="g3">
          <label>Ikon<input name="icon" value="<?= e($g['icon']) ?>"></label>
          <label>Warna Ikon<select name="accent"><?= $accOpts($g['accent']) ?></select></label>
          <label class="chk2">Efek Koin<input type="checkbox" name="coin" value="1" <?= ((int)$g['coin']===1?'checked':'') ?>></label>
        </div>
        <div class="group-actions">
          <button class="btn sm" name="action" value="group_save">Simpan Grup</button>
          <button class="btn sm danger" name="action" value="group_delete" onclick="return confirm('Hapus grup + semua promonya?')">🗑 Hapus Grup</button>
        </div>
      </form>

      <?php foreach ($promos as $p): ?>
      <form method="post" enctype="multipart/form-data" class="promo-box">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <div class="pb-head"><b><?= e($p['label']) ?></b>
          <button class="btn sm danger" name="action" value="promo_delete" onclick="return confirm('Hapus bonus ini?')">🗑</button></div>
        <div class="g2">
          <label>Label Banner<input name="label" value="<?= e($p['label']) ?>"></label>
          <label>Ribbon (mis. HOT, kosong=tanpa)<input name="ribbon" value="<?= e($p['ribbon']) ?>"></label>
        </div>
        <label>Judul Promo<input name="title" value="<?= e($p['title']) ?>"></label>
        <label>Deskripsi<textarea name="description"><?= e($p['description']) ?></textarea></label>
        <div class="g3">
          <label>Teks Tombol<input name="btn" value="<?= e($p['btn']) ?>"></label>
          <label>URL Tombol<input name="url" value="<?= e($p['url']) ?>"></label>
          <label>Warna Banner<select name="bnr"><?= $bnrOpts($p['bnr']) ?></select></label>
        </div>
        <div class="uploader">
          <div class="thumb"><?php if ($p['banner']): ?><img src="../<?= e($p['banner']) ?>"><?php else: ?>Pakai gradien<?php endif; ?></div>
          <div class="uc"><label class="file">📁 Gambar Banner<input type="file" name="banner_file" accept="image/*"></label>
          <?php if ($p['banner']): ?><label class="chk"><input type="checkbox" name="banner_clear" value="1"> Hapus gambar</label><?php endif; ?></div>
        </div>
        <button class="btn gold sm" name="action" value="promo_save">💾 Simpan Bonus</button>
      </form>
      <?php endforeach; ?>

      <form method="post" class="addpromo">
        <?= csrf_field() ?><input type="hidden" name="group_id" value="<?= (int)$g['id'] ?>">
        <button class="btn pur sm" name="action" value="promo_add">➕ Tambah Bonus Baru</button>
      </form>
    </div>
    <?php endforeach; ?>

    <form method="post"><?= csrf_field() ?><button class="btn pur" name="action" value="group_add">➕ Tambah Grup Bonus (Section Baru)</button></form>
  </section>

  <!-- ========== LINK ========== -->
  <section class="panel">
    <h2>🔗 Info Link</h2>
    <?php foreach ($links as $l): ?>
    <form method="post" class="rowcard wide">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
      <input name="icon" value="<?= e($l['icon']) ?>" placeholder="🔗" style="max-width:60px">
      <input name="title" value="<?= e($l['title']) ?>" placeholder="Judul">
      <input name="status" value="<?= e($l['status']) ?>" placeholder="Status">
      <input name="url" value="<?= e($l['url']) ?>" placeholder="URL">
      <button class="btn sm" name="action" value="link_save">Simpan</button>
      <button class="btn sm danger" name="action" value="link_delete" onclick="return confirm('Hapus?')">🗑</button>
    </form>
    <?php endforeach; ?>
    <form method="post"><?= csrf_field() ?><button class="btn pur" name="action" value="link_add">➕ Tambah Link</button></form>
  </section>

</main>
</body></html>
