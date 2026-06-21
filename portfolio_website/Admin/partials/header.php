<?php
require_once dirname(__DIR__, 2) . '/app/config.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/csrf.php'; // for csrf_field() used by Logout


// Fallback: define APP_URL if config didn't set it (prevents "undefined constant" notices)
if (!defined('APP_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $base = preg_replace('#/Admin/.*$#', '', $scriptDir);
    define('APP_URL', $scheme . '://' . $host . $base);
}

require_admin();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../../styles.css">
</head>

<body>

  <header class="site-header" id="header">
    <div class="container header-inner">
      <a class="logo" href="<?= APP_URL ?>/Admin/dashboard.php">Admin</a>
      <nav class="nav">
        <ul>
          <li><a class="nav-link" href="<?= APP_URL ?>/Admin/projects/">Projects</a></li>
          <li><a class="nav-link" href="<?= APP_URL ?>/Admin/certificates/">Certificates</a></li>
          <li><a class="nav-link" href="<?= APP_URL ?>/Admin/contacts/">Contacts</a></li>
          <li>
            <form method="post" action="<?= APP_URL ?>/public/logout.php" class="inline">
              <?= csrf_field() ?>
              <button class="nav-link btn-link" type="submit">Logout</button>
            </form>
          </li>

        </ul>
      </nav>
    </div>
  </header>
  <main class="section container">