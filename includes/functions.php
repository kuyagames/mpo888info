<?php
require_once __DIR__ . '/db.php';

/* ---------- Helper umum ---------- */
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(): void
{
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(400);
        exit('CSRF token tidak valid. Muat ulang halaman.');
    }
}

/* ---------- Data access ---------- */
function get_settings(): array
{
    $rows = db()->query('SELECT skey, svalue FROM settings')->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[$r['skey']] = $r['svalue'];
    return $out;
}
function set_setting(string $k, string $v): void
{
    $st = db()->prepare(
        'INSERT INTO settings (skey, svalue) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)'
    );
    $st->execute([$k, $v]);
}
function sv(array $s, string $k, string $default = ''): string
{
    return (isset($s[$k]) && $s[$k] !== '') ? $s[$k] : $default;
}

function get_stats(): array   { return db()->query('SELECT * FROM stats ORDER BY sort, id')->fetchAll(); }
function get_groups(): array  { return db()->query('SELECT * FROM bonus_groups ORDER BY sort, id')->fetchAll(); }
function get_links(): array   { return db()->query('SELECT * FROM links ORDER BY sort, id')->fetchAll(); }
function get_promos(int $gid): array
{
    $st = db()->prepare('SELECT * FROM promos WHERE group_id = ? ORDER BY sort, id');
    $st->execute([$gid]);
    return $st->fetchAll();
}

/* ---------- Peta warna (samakan dengan admin) ---------- */
function bnr_grad(string $k): string
{
    $m = [
        'a' => '#7c3aed,#4c1d95', 'b' => '#a855f7,#6d28d9', 'c' => '#f59e0b,#b45309',
        'd' => '#9333ea,#db2777', 'e' => '#0ea5e9,#4338ca', 'f' => '#f43f5e,#7c3aed',
    ];
    return $m[$k] ?? $m['a'];
}
function acc_grad(string $k): string
{
    $m = ['purple' => '#7c3aed,#a855f7', 'gold' => '#f59e0b,#ffd76a', 'pink' => '#db2777,#7c3aed'];
    return $m[$k] ?? $m['purple'];
}

/* ---------- Upload gambar ----------
   Return: URL relatif gambar baru, atau null bila tidak ada file.
   $err diisi pesan error bila gagal. */
function handle_upload(string $field, ?string &$err): ?string
{
    $err = null;
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;

    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $err = 'Upload gagal (kode ' . $f['error'] . ').'; return null; }
    if ($f['size'] > MAX_UPLOAD)       { $err = 'Ukuran gambar melebihi 2MB.';          return null; }

    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) { $err = 'Format tidak didukung (' . $ext . ').'; return null; }

    $fi   = new finfo(FILEINFO_MIME_TYPE);
    $mime = $fi->file($f['tmp_name']);
    if (strpos((string)$mime, 'image/') !== 0) { $err = 'File bukan gambar valid.'; return null; }

    if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0775, true);
    $name = 'banner_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        $err = 'Gagal menyimpan gambar (cek izin folder uploads/).';
        return null;
    }
    return UPLOAD_URL . '/' . $name;
}

/* ---------- Render landing page ---------- */
function render_promo(array $p, bool $coin): string
{
    $hasImg = !empty($p['banner']);
    $bg = $hasImg
        ? 'style="background-image:url(' . e($p['banner']) . ')"'
        : 'style="background:linear-gradient(135deg,' . bnr_grad($p['bnr']) . ')"';
    $ribbon = !empty($p['ribbon']) ? '<span class="ribbon">' . e($p['ribbon']) . '</span>' : '';
    $coinEl = $coin ? '<span class="coin"></span>' : '';
    $label  = $hasImg ? '' : '<div><div class="label">' . e($p['label']) . '</div><div class="sub">Banner</div></div>';
    $url    = $p['url'] !== '' ? $p['url'] : '#';
    $btn    = $p['btn'] !== '' ? $p['btn'] : 'Detail';

    return '<div class="promo reveal">' . $ribbon
        . '<div class="pb" ' . $bg . '><span class="bnr-glow"></span>' . $coinEl . '<span class="shine2"></span>' . $label . '</div>'
        . '<div class="pbody"><h4>' . e($p['title']) . '</h4><p>' . e($p['description']) . '</p>'
        . '<a href="' . e($url) . '" class="pbtn">' . e($btn) . ' &rarr;</a></div></div>';
}
function render_group(array $g): string
{
    $promos = get_promos((int)$g['id']);
    $cols   = count($promos) >= 4 ? 'cols-4' : 'cols-2';
    $icbg   = 'background:linear-gradient(135deg,' . acc_grad($g['accent']) . ')';
    $inner  = '';
    foreach ($promos as $p) $inner .= render_promo($p, (int)$g['coin'] === 1);

    $desc   = trim((string)($g['description'] ?? ''));
    $descEl = $desc !== '' ? '<p class="group-desc reveal">' . nl2br(e($desc)) . '</p>' : '';

    return '<div class="group" id="group-' . (int)$g['id'] . '"><div class="group-label reveal">'
        . '<h3><span class="ic" style="' . $icbg . '">' . e($g['icon']) . '</span> ' . e($g['title']) . '</h3>'
        . '<span class="line"></span><span class="tag">' . e($g['tag']) . '</span></div>'
        . $descEl
        . '<div class="promos ' . $cols . '">' . $inner . '</div></div>';
}
function render_link(array $l): string
{
    $url = $l['url'] !== '' ? $l['url'] : '#';
    return '<a href="' . e($url) . '" class="link-card"><div class="lc-left"><span class="lc-ic">' . e($l['icon']) . '</span>'
        . '<div><div class="lc-t">' . e($l['title']) . '</div>'
        . '<div class="live"><span class="pdot"></span>' . e($l['status']) . '</div></div></div>'
        . '<span class="go">&#8599;</span></a>';
}
