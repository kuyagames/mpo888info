<?php
require_once __DIR__ . '/../includes/functions.php';

function admin_exists(): bool
{
    return (int) db()->query('SELECT COUNT(*) AS c FROM admins')->fetch()['c'] > 0;
}
function require_login(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
