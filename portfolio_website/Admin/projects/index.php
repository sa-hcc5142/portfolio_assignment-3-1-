<?php
// Admin/projects/index.php — List + Delete (via POST) + link to Create/Edit

require_once __DIR__ . '/../../app/session.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/csrf.php';
require_once __DIR__ . '/../../app/auth.php';

require_admin(); // 🔒 only ADMINs

$conn = db();

// Fetch projects newest first
$sql = "SELECT id, title, slug, category, created_at
        FROM projects
        ORDER BY COALESCE(created_at, NOW()) DESC, id DESC";
$res = mysqli_query($conn, $sql);

// helpers
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// flashes
$flash = '';
if (isset($_GET['created'])) $flash = 'Project created successfully.';
if (isset($_GET['updated'])) $flash = 'Project updated successfully.';
if (isset($_GET['deleted'])) $flash = 'Project deleted successfully.';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin – Projects</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;background:#0f1220;color:#e7e9ee;margin:0}
    .wrap{max-width:1080px;margin:32px auto;padding:0 16px}
    h1{margin:0 0 16px}
    .bar{display:flex;justify-content:space-between;align-items:center;margin:14px 0}
    a.btn,button.btn{display:inline-block;padding:10px 14px;border-radius:10px;border:1px solid #32406d;background:#1a2342;color:#fff;text-decoration:none;font-weight:700;cursor:pointer}
    a.btn:hover,button.btn:hover{background:#22305e}
    table{width:100%;border-collapse:collapse;background:#13172a;border:1px solid #20253e;border-radius:12px;overflow:hidden}
    th,td{padding:12px 14px;border-bottom:1px solid #20253e;text-align:left}
    th{font-size:13px;color:#b7bfd6;font-weight:700;background:#12162a}
    tr:last-child td{border-bottom:none}
    .muted{color:#b7bfd6;font-size:13px}
    .flash{background:#12321a;border:1px solid #2f6c3a;color:#d7ffde;border-radius:12px;padding:10px 12px;margin:12px 0}
    .danger{background:#2a1212;border-color:#6c2f2f;color:#ffdede}
    form.inline{display:inline}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Projects (Admin)</h1>

    <?php if ($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>

    <div class="bar">
      <div class="muted">Manage your portfolio projects</div>
      <a class="btn" href="/portfolio/Admin/projects/create.php">+ Create Project</a>
    </div>

    <table role="table" aria-label="Projects">
      <thead>
        <tr>
          <th style="width:6rem">ID</th>
          <th>Title</th>
          <th>Category</th>
          <th style="width:12rem">Created</th>
          <th style="width:14rem">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($res && mysqli_num_rows($res)): ?>
          <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
              <td>#<?= (int)$row['id'] ?></td>
              <td><?= e($row['title']) ?><br><span class="muted"><?= e($row['slug']) ?></span></td>
              <td><?= e($row['category']) ?></td>
              <td><?= e(date('Y-m-d H:i', strtotime($row['created_at'] ?? 'now'))) ?></td>
              <td>
                <a class="btn" href="/portfolio/Admin/projects/edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
                <form class="inline" method="post" action="/portfolio/Admin/projects/delete.php" onsubmit="return confirm('Delete this project?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <button type="submit" class="btn danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; mysqli_free_result($res); ?>
        <?php else: ?>
          <tr><td colspan="5" class="muted">No projects yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <p style="margin-top:16px"><a class="btn" href="/portfolio/#projects">← Back to site</a></p>
  </div>
</body>
</html>
