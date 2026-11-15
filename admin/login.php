<?php
require_once __DIR__ . '/auth.php';

if (!admin_exists()) { header('Location: setup.php'); exit; }
if (!empty($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $st = db()->prepare('SELECT * FROM admins WHERE username = ?');
    $st->execute([$u]);
    $a = $st->fetch();
    if ($a && password_verify($p, $a['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $a['id'];
        $_SESSION['admin_user'] = $a['username'];
        header('Location: dashboard.php');
        exit;
    }
    $msg = 'Username atau password salah.';
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login Admin</title><link rel="stylesheet" href="assets/admin.css"></head>
<body class="auth">
<form method="post" class="auth-card">
  <h1>🔐 Login Admin</h1>
  <?php if ($msg): ?><div class="err"><?= e($msg) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username<input name="username" required autofocus></label>
  <label>Password<input type="password" name="password" required></label>
  <button class="btn gold" type="submit">Masuk</button>
</form>
</body></html>
