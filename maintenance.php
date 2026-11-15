<?php
header('HTTP/1.1 503 Service Unavailable');
header('Retry-After: 3600');
header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sedang Pemeliharaan</title>
<style>
  :root { color-scheme: dark; }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: grid; place-items: center;
    font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    background: radial-gradient(circle at 50% 0%, #2a1f4d 0%, #0d0b1a 60%);
    color: #e8e6f3; padding: 24px;
  }
  .card {
    max-width: 440px; text-align: center;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(168,140,255,.25);
    border-radius: 18px; padding: 40px 28px;
    backdrop-filter: blur(10px);
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
  }
  .icon { font-size: 44px; margin-bottom: 12px; }
  h1 { margin: 0 0 10px; font-size: 22px;
       background: linear-gradient(90deg,#c9a7ff,#ffd479);
       -webkit-background-clip: text; background-clip: text; color: transparent; }
  p { margin: 0; line-height: 1.6; color: #b8b4cf; font-size: 15px; }
</style>
</head>
<body>
  <div class="card">
    <div class="icon">🛠️</div>
    <h1>Sedang Dalam Pemeliharaan</h1>
    <p>Situs kami sedang diperbarui untuk pengalaman yang lebih baik.
       Silakan kembali beberapa saat lagi. Terima kasih atas kesabaran Anda.</p>
  </div>
</body>
</html>
