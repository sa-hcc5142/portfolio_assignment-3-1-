<?php
// Admin/contacts/delete.php — POST-only delete handler with CSRF

require_once __DIR__ . '/../../app/session.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/csrf.php';
require_once __DIR__ . '/../../app/auth.php';

require_admin(); // 🔒 ADMIN only

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /portfolio/Admin/contacts/index.php');
  exit;
}
// CSRF check — same convention as public/contact_store.php
if (
  !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])
) {
  header('Location: /portfolio/Admin/contacts/index.php?deleted=0');
  exit;
}


$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  header('Location: /portfolio/Admin/contacts/index.php?deleted=0');
  exit;
}

$conn = db();
$ok = false;
if ($stmt = mysqli_prepare($conn, "DELETE FROM contact_messages WHERE id = ?")) {
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $ok = (mysqli_stmt_affected_rows($stmt) > 0);
  mysqli_stmt_close($stmt);
}
header('Location: /portfolio/Admin/contacts/index.php?deleted=' . ($ok ? '1' : '0'));
exit;
