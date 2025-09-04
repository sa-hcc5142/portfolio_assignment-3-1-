<?php
// Admin/contacts/index.php — List + Delete (POST) contact messages

require_once __DIR__ . '/../../app/session.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/csrf.php';
require_once __DIR__ . '/../../app/auth.php';

require_admin(); // 🔒 ADMIN only

$conn = db();

// Fetch newest first
$sql = "SELECT id, name, email, message, created_at
        FROM contact_messages
        ORDER BY created_at DESC, id DESC";
$res = mysqli_query($conn, $sql);

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// flash
$flash = '';
if (isset($_GET['deleted'])) {
  $flash = $_GET['deleted'] ? 'Message deleted.' : 'Delete failed.';
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin – Contact Messages</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;background:#0f1220;color:#e7e9ee;margin:0}
    .wrap{max-width:1080px;margin:32px auto;padding:0 16px}
    h1{margin:0 0 16px}
    .bar{display:flex;justify-content:space-between;align-items:center;margin:14px 0}
    a.btn,button.btn{display:inline-block;padding:10px 14px;border-radius:10px;border:1px solid #32406d;background:#1a2342;color:#fff;text-decoration:none;font-weight:700;cursor:pointer}
    a.btn:hover,button.btn:hover{background:#22305e}
    table{width:100%;border-collapse:collapse;background:#13172a;border:1px solid #20253e;border-radius:12px;overflow:hidden}
    th,td{padding:12px 14px;border-bottom:1px solid #20253e;text-align:left;vertical-align:top}
    th{font-size:13px;color:#b7bfd6;font-weight:700;background:#12162a}
    tr:last-child td{border-bottom:none}
    .muted{color:#b7bfd6;font-size:13px}
    .flash{background:#12321a;border:1px solid #2f6c3a;color:#d7ffde;border-radius:12px;padding:10px 12px;margin:12px 0}
    .danger{background:#2a1212;border-color:#6c2f2f;color:#ffdede}
    details summary{cursor:pointer;color:#d9def0}
    details[open] summary{opacity:.9}
    form.inline{display:inline}
    .email{color:#ffc466;text-decoration:none}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Contact Messages (Admin)</h1>

    <?php if ($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>

    <div class="bar">
      <div class="muted">View & delete submissions from the site contact form</div>
      <a class="btn" href="/portfolio/#contact">← Back to site</a>
    </div>

    <table role="table" aria-label="Contact messages">
      <thead>
        <tr>
          <th style="width:6rem">ID</th>
          <th style="width:18rem">From</th>
          <th>Message</th>
          <th style="width:13rem">Received</th>
          <th style="width:10rem">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($res && mysqli_num_rows($res)): ?>
          <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
              <td>#<?= (int)$row['id'] ?></td>
              <td>
                <div><strong><?= e($row['name']) ?></strong></div>
                <a class="email" href="mailto:<?= e($row['email']) ?>"><?= e($row['email']) ?></a>
              </td>
              <td>
                <?php
                  $msg = (string)($row['message'] ?? '');
                  $short = mb_strlen($msg) > 160 ? mb_substr($msg, 0, 160).'…' : $msg;
                ?>
                <details>
                  <summary><?= nl2br(e($short)) ?></summary>
                  <div style="margin-top:6px"><?= nl2br(e($msg)) ?></div>
                </details>
              </td>
              <td><?= e(date('Y-m-d H:i', strtotime($row['created_at'] ?? 'now'))) ?></td>
              <td>
                <form class="inline" method="post" action="/portfolio/Admin/contacts/delete.php" onsubmit="return confirm('Delete this message?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <button type="submit" class="btn danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; mysqli_free_result($res); ?>
        <?php else: ?>
          <tr><td colspan="5" class="muted">No messages yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
