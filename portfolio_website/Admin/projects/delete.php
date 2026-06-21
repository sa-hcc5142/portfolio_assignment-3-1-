<?php
// Admin/projects/delete.php — POST-only delete handler with CSRF

require_once __DIR__ . '/../../app/session.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/csrf.php';
require_once __DIR__ . '/../../app/auth.php';

require_admin(); // 🔒

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /portfolio/Admin/projects/index.php'); exit;
}
if (!csrf_verify_from_post()) {
  header('Location: /portfolio/Admin/projects/index.php?deleted=0'); exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  header('Location: /portfolio/Admin/projects/index.php?deleted=0'); exit;
}

$conn = db();
if ($stmt = mysqli_prepare($conn, "DELETE FROM projects WHERE id = ?")) {
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  header('Location: /portfolio/Admin/projects/index.php?deleted=1'); exit;
}

header('Location: /portfolio/Admin/projects/index.php?deleted=0'); exit;
