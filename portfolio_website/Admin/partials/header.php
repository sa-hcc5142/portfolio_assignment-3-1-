<?php
require_once dirname(__DIR__, 2) . '/app/config.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_admin();
?>
<!doctype html><html><head>
<meta charset="utf-8"><title>Admin</title>
<link rel="stylesheet" href="../../styles.css">
</head><body>
<header class="site-header" id="header"><div class="container header-inner">
  <a class="logo" href="<?=APP_URL?>/Admin/dashboard.php">Admin</a>
  <nav class="nav"><ul>
    <li><a class="nav-link" href="<?=APP_URL?>/Admin/projects/">Projects</a></li>
    <li><a class="nav-link" href="<?=APP_URL?>/Admin/certificates/">Certificates</a></li>
    <li><a class="nav-link" href="<?=APP_URL?>/Admin/contacts/">Contacts</a></li>
    <li><a class="nav-link" href="<?=APP_URL?>/public/logout.php">Logout</a></li>
  </ul></nav>
</div></header>
<main class="section container">
