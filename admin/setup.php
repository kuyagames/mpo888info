<?php
/* Buat akun admin pertama. Jalankan SEKALI, lalu HAPUS file ini. */
require_once __DIR__ . '/auth.php';

if (admin_exists()) {
    exit('Setup sudah pernah dilakukan. Demi keamanan, HAPUS file admin/setup.php ini sekarang.');
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (strlen($u) < 3 || strlen($p) < 6) {
        $msg = 'Username minimal 3 karakter & password minimal 6 karakter.';
    } else {
        $st = db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
        $st->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
        exit('<p style="font-family:sans-serif;color:#0a0">Akun admin berhasil dibuat. '
            . '<a href="login.php">Login di sini</a>.</p>'
            . '<p style="font-family:sans-serif;color:#c00"><b>PENTING:</b> hapus file admin/setup.php sekarang juga.</p>');
    }
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Setup Admin</title><link rel="stylesheet" href="assets/admin.css"></head>
<body class="auth">
<form method="post" class="auth-card">
  <h1>⚙ Buat Admin Pertama</h1>
  <p class="hint">Hanya bisa dijalankan sekali. Setelah selesai, hapus file ini.</p>
  <?php if ($msg): ?><div class="err"><?= e($msg) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username<input name="username" required></label>
  <label>Password<input type="password" name="password" required></label>
  <button class="btn gold" type="submit">Buat Akun</button>
</form>
</body></html>
