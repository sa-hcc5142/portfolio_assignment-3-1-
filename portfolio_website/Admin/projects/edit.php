<?php
// Admin/projects/edit.php — Edit existing project (GET show, POST update)

require_once __DIR__ . '/../../app/session.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/csrf.php';
require_once __DIR__ . '/../../app/auth.php';

require_admin(); // 🔒

$conn   = db();
$errors = [];
$id     = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) { header('Location: /portfolio/Admin/projects/index.php'); exit; }

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Load row
$row = null;
if ($stmt = mysqli_prepare($conn, "SELECT id, title, slug, summary, github_url, image_path, category, started_on, ended_on FROM projects WHERE id = ? LIMIT 1")) {
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = $res ? mysqli_fetch_assoc($res) : null;
  mysqli_stmt_close($stmt);
}
if (!$row) { header('Location: /portfolio/Admin/projects/index.php'); exit; }

// POST: update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_verify_from_post()) {
    $errors[] = 'Security check failed.';
  } else {
    $title      = trim($_POST['title'] ?? '');
    $slug       = trim($_POST['slug'] ?? '');
    $summary    = trim($_POST['summary'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $image_path = trim($_POST['image_path'] ?? '');
    $category   = trim($_POST['category'] ?? '');
    $started_on = trim($_POST['started_on'] ?? '');
    $ended_on   = trim($_POST['ended_on'] ?? '');

    if ($title === '' || mb_strlen($title) > 150) $errors[] = 'Title required (max 150).';
    if ($slug === ''  || !preg_match('~^[a-z0-9-]+$~', $slug)) $errors[] = 'Slug must be lowercase letters, numbers, hyphens.';
    if ($github_url !== '' && !filter_var($github_url, FILTER_VALIDATE_URL)) $errors[] = 'GitHub URL invalid.';
    if ($image_path === '') $errors[] = 'Image filename (in /Projects) required.';

    // enforce unique slug (except current row)
    if (empty($errors)) {
      if ($st = mysqli_prepare($conn, "SELECT id FROM projects WHERE slug = ? AND id <> ? LIMIT 1")) {
        mysqli_stmt_bind_param($st, "si", $slug, $id);
        mysqli_stmt_execute($st);
        $r = mysqli_stmt_get_result($st);
        if ($r && mysqli_fetch_assoc($r)) $errors[] = 'Slug already in use.';
        mysqli_stmt_close($st);
      } else {
        $errors[] = 'Could not validate slug.';
      }
    }

    if (empty($errors)) {
      $sql = "UPDATE projects
              SET title=?, slug=?, summary=?, github_url=?, image_path=?, category=?, 
                  started_on = NULLIF(?, ''), ended_on = NULLIF(?, '')
              WHERE id = ?";
      if ($st = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($st, "ssssssssi",
          $title, $slug, $summary, $github_url, $image_path, $category,
          $started_on, $ended_on, $id
        );
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        if ($ok) { header('Location: /portfolio/Admin/projects/index.php?updated=1'); exit; }
        else { $errors[] = 'Database error while updating.'; }
      } else {
        $errors[] = 'Prepare failed.';
      }
    }

    // keep form filled with attempted values if error
    $row = array_merge($row, [
      'title'=>$title, 'slug'=>$slug, 'summary'=>$summary, 'github_url'=>$github_url,
      'image_path'=>$image_path, 'category'=>$category, 'started_on'=>$started_on, 'ended_on'=>$ended_on
    ]);
  }
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin – Edit Project</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;background:#0f1220;color:#e7e9ee;margin:0}
    .wrap{max-width:920px;margin:32px auto;padding:0 16px}
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
  <h1>Edit Project</h1>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <strong>Please fix the following:</strong>
      <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <div class="card">
    <form method="post" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

      <div class="row">
        <div class="col">
          <label for="title">Title *</label>
          <input type="text" id="title" name="title" required maxlength="150" value="<?= e($row['title']) ?>">
        </div>
        <div class="col">
          <label for="slug">Slug (unique, lowercase-hyphen) *</label>
          <input type="text" id="slug" name="slug" required pattern="[a-z0-9\-]+" value="<?= e($row['slug']) ?>">
          <div class="hint">e.g., <code>mental-health-application-using-java</code></div>
        </div>
      </div>

      <div class="row" style="margin-top:12px">
        <div class="col">
          <label for="github_url">GitHub URL</label>
          <input type="url" id="github_url" name="github_url" placeholder="https://github.com/user/repo" value="<?= e($row['github_url']) ?>">
        </div>
        <div class="col">
          <label for="image_path">Image filename in <code>/Projects</code> *</label>
          <input type="text" id="image_path" name="image_path" required placeholder="image.png" value="<?= e($row['image_path']) ?>">
          <div class="hint">Store only the filename. Homepage renders <code>Projects/<?= '<?= $image_path ?>' ?></code>.</div>
        </div>
      </div>

      <div class="row" style="margin-top:12px">
        <div class="col">
          <label for="category">Category (shows on overlay)</label>
          <input type="text" id="category" name="category" placeholder="Desktop Application • MySQL • JSON Parsing • JavaFX" value="<?= e($row['category']) ?>">
        </div>
        <div class="col">
          <label for="started_on">Started On (YYYY-MM-DD)</label>
          <input type="date" id="started_on" name="started_on" value="<?= e($row['started_on']) ?>">
        </div>
      </div>

      <div class="row" style="margin-top:12px">
        <div class="col">
          <label for="ended_on">Ended On (YYYY-MM-DD)</label>
          <input type="date" id="ended_on" name="ended_on" value="<?= e($row['ended_on']) ?>">
        </div>
        <div class="col">
          <label for="summary">Summary</label>
          <textarea id="summary" name="summary" placeholder="One or two lines describing the project"><?= e($row['summary']) ?></textarea>
        </div>
      </div>

      <div class="actions">
        <button type="submit" class="btn">Update</button>
        <a href="/portfolio/Admin/projects/index.php" class="btn secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
