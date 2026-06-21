<?php
// Admin/projects/create.php

// --- Bootstrap & auth guards ---
require_once __DIR__ . '/../../app/session.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/csrf.php';
require_once __DIR__ . '/../../app/auth.php';       // must provide require_admin()

require_admin(); // redirect away if not ADMIN

$conn   = db();
$errors = [];
$old    = [
  'title'       => '',
  'slug'        => '',
  'summary'     => '',
  'github_url'  => '',
  'image_path'  => '',
  'category'    => '',
  'started_on'  => '',
  'ended_on'    => '',
];

// --- Helpers ---
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// --- Handle POST (create) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // CSRF check
  if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])
  ) {
    $errors[] = 'Security check failed. Please retry.';
  }

  // Collect + trim
  $old['title']      = trim($_POST['title'] ?? '');
  $old['slug']       = trim($_POST['slug'] ?? '');
  $old['summary']    = trim($_POST['summary'] ?? '');
  $old['github_url'] = trim($_POST['github_url'] ?? '');
  $old['image_path'] = trim($_POST['image_path'] ?? '');
  $old['category']   = trim($_POST['category'] ?? '');
  $old['started_on'] = trim($_POST['started_on'] ?? ''); // YYYY-MM-DD or empty
  $old['ended_on']   = trim($_POST['ended_on'] ?? '');

  // Basic validation
  if ($old['title'] === '' || mb_strlen($old['title']) > 150) {
    $errors[] = 'Title is required (max 150 chars).';
  }
  if ($old['slug'] === '' || !preg_match('~^[a-z0-9-]+$~', $old['slug'])) {
    $errors[] = 'Slug is required (lowercase letters, numbers and hyphens only).';
  }
  if ($old['github_url'] !== '' && !filter_var($old['github_url'], FILTER_VALIDATE_URL)) {
    $errors[] = 'GitHub URL is invalid.';
  }
  if ($old['image_path'] === '') {
    $errors[] = 'Image path (filename in /Projects) is required.';
  }

  // Slug uniqueness check
  if (empty($errors)) {
    if ($stmt = mysqli_prepare($conn, "SELECT id FROM projects WHERE slug = ? LIMIT 1")) {
      mysqli_stmt_bind_param($stmt, "s", $old['slug']);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      if ($res && mysqli_fetch_assoc($res)) {
        $errors[] = 'Slug already exists. Choose a different slug.';
      }
      mysqli_stmt_close($stmt);
    } else {
      $errors[] = 'Could not validate slug (prepare failed).';
    }
  }

  // Insert if valid
  if (empty($errors)) {
    $sql = "INSERT INTO projects
              (title, slug, summary, github_url, image_path, category, started_on, ended_on, created_at)
            VALUES
              (?, ?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NOW())";
    if ($stmt = mysqli_prepare($conn, $sql)) {
      mysqli_stmt_bind_param(
        $stmt, "ssssssss",
        $old['title'],
        $old['slug'],
        $old['summary'],
        $old['github_url'],
        $old['image_path'],
        $old['category'],
        $old['started_on'],   // NULLIF turns '' into NULL for DATE fields
        $old['ended_on']
      );
      $ok = mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);

      if ($ok) {
        // Success → go back to admin list
        header('Location: /portfolio/Admin/projects/index.php?created=1');
        exit;
      } else {
        $errors[] = 'Database error while inserting project.';
      }
    } else {
      $errors[] = 'Prepare failed.';
    }
  }
}

// --- View (simple, self-contained). If you have Admin/partials/header.php, include it. ---
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin – Create Project</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;background:#0f1220;color:#e7e9ee;margin:0;padding:24px}
    .wrap{max-width:920px;margin:0 auto}
    h1{margin:0 0 16px}
    .card{background:#13172a;border:1px solid #20253e;border-radius:12px;padding:20px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .row .col{display:flex;flex-direction:column}
    label{font-weight:600;margin-bottom:6px}
    input[type=text],input[type=url],input[type=date],textarea{
      background:#0d1122;border:1px solid #20253e;border-radius:8px;color:#e7e9ee;padding:10px 12px
    }
    textarea{min-height:120px;resize:vertical}
    .actions{margin-top:16px;display:flex;gap:10px}
    .btn{display:inline-block;padding:10px 14px;border-radius:8px;border:1px solid #32406d;background:#1a2342;color:#fff;text-decoration:none;font-weight:700}
    .btn:hover{background:#22305e}
    .btn.secondary{background:transparent}
    .errors{background:#3a1a1a;border:1px solid #6c2f2f;color:#ffdede;border-radius:10px;padding:10px 12px;margin-bottom:12px}
    .hint{font-size:12px;color:#b7bfd6;margin-top:4px}
  </style>
</head>
<body>
<div class="wrap">
  <h1>Create Project</h1>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <strong>Please fix the following:</strong>
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card">
    <form method="post" action="">
      <?= csrf_field() ?>

      <div class="row">
        <div class="col">
          <label for="title">Title *</label>
          <input type="text" id="title" name="title" required maxlength="150" value="<?= e($old['title']) ?>">
        </div>
        <div class="col">
          <label for="slug">Slug (unique, lowercase-hyphen) *</label>
          <input type="text" id="slug" name="slug" required pattern="[a-z0-9\-]+" value="<?= e($old['slug']) ?>">
          <div class="hint">e.g., <code>mental-health-helper</code></div>
        </div>
      </div>

      <div class="row" style="margin-top:12px">
        <div class="col">
          <label for="github_url">GitHub URL</label>
          <input type="url" id="github_url" name="github_url" placeholder="https://github.com/user/repo" value="<?= e($old['github_url']) ?>">
        </div>
        <div class="col">
          <label for="image_path">Image filename in <code>/Projects</code> *</label>
          <input type="text" id="image_path" name="image_path" required placeholder="mental_health_helper.png" value="<?= e($old['image_path']) ?>">
          <div class="hint">Do NOT include <code>Projects/</code>. Your homepage uses <code>Projects/<?= '<?= $image_path ?>' ?></code>.</div>
        </div>
      </div>

      <div class="row" style="margin-top:12px">
        <div class="col">
          <label for="category">Category</label>
          <input type="text" id="category" name="category" placeholder="desktop, console, hardware, ..." value="<?= e($old['category']) ?>">
        </div>
        <div class="col">
          <label for="started_on">Started On (YYYY-MM-DD)</label>
          <input type="date" id="started_on" name="started_on" value="<?= e($old['started_on']) ?>">
        </div>
      </div>

      <div class="row" style="margin-top:12px">
        <div class="col">
          <label for="ended_on">Ended On (YYYY-MM-DD)</label>
          <input type="date" id="ended_on" name="ended_on" value="<?= e($old['ended_on']) ?>">
        </div>
        <div class="col">
          <label for="summary">Summary</label>
          <textarea id="summary" name="summary" placeholder="One or two lines describing the project"><?= e($old['summary']) ?></textarea>
        </div>
      </div>

      <div class="actions">
        <button type="submit" class="btn">Save</button>
        <a href="/portfolio/Admin/projects/index.php" class="btn secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
