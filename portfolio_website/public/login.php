<?php
// public/login.php
// Login page using mysqli (procedural) + helpers.
// On success: redirect to /portfolio/#home for both USER and ADMIN.

require_once __DIR__ . '/../app/session.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/csrf.php';
require_once __DIR__ . '/../app/auth.php';

$error = '';

// If already logged in, go home
if (is_logged_in()) {
  auth_redirect('#home'); // /portfolio/#home
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF check
  if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])
  ) {
    $error = 'Security check failed. Please reload and try again.';
  } else {
    $email = trim($_POST['email'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');

    // Try to log in (redirects to #home on success)
    if (!login_user($email, $pass, '#home')) {
      $error = 'Invalid email or password.';
    }
  }
}

// Small helper for escaping
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign In — Portfolio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Minimal, self-contained styles (keeps page readable even without site CSS) */
    :root{
      --bg:#0f1220; --card:#13172a; --line:#20253e; --txt:#e7e9ee; --sub:#b7bfd6; --accent:#ffc466;
    }
    html,body{height:100%}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;background:var(--bg);color:var(--txt);display:grid;place-items:center}
    .card{width:min(540px,90vw);background:var(--card);border:1px solid var(--line);border-radius:14px;padding:20px 22px;box-shadow:0 20px 60px rgba(0,0,0,.35)}
    h1{margin:0 0 12px 0;font-size:22px}
    p.sub{margin:0 0 18px 0;color:var(--sub)}
    label{display:block;font-weight:700;margin:10px 0 6px}
    input[type=email],input[type=password]{width:100%;padding:11px 12px;border-radius:10px;border:1px solid var(--line);background:#0d1122;color:var(--txt)}
    .row{display:flex;gap:12px;align-items:center;margin:10px 0 6px}
    .actions{margin-top:16px;display:flex;gap:10px;align-items:center}
    button{padding:10px 14px;border-radius:10px;border:1px solid var(--line);background:#1a2342;color:#fff;font-weight:700;cursor:pointer}
    button:hover{background:#22305e}
    .muted{color:var(--sub);font-size:13px}
    .error{background:#3a1a1a;border:1px solid #6c2f2f;color:#ffdede;border-radius:10px;padding:10px 12px;margin:0 0 12px}
    .hint{font-size:12px;color:var(--sub)}
    .center{display:flex;justify-content:center}
    a.link{color:var(--accent);text-decoration:none}
  </style>
</head>
<body>
  <main class="card" role="main">
    <h1>Sign in</h1>
    <p class="sub">Use your email and password. On success you’ll be taken to the home section.</p>

    <?php if ($error): ?>
      <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <?= csrf_field() ?>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required placeholder="you@example.com" autofocus>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="••••••••">

      <!-- Optional: visual role toggle (does NOT change server role; DB decides). Keep or remove. -->
      <div class="row">
        <span class="hint">Role is determined from your account.</span>
      </div>

      <div class="actions">
        <button type="submit">Sign in</button>
        <span class="muted">You’ll be redirected to <code>/portfolio/#home</code>.</span>
      </div>
    </form>

    <p class="center" style="margin-top:14px">
      <a class="link" href="../index.php#home">← Back to site</a>
    </p>
  </main>
</body>
</html>
