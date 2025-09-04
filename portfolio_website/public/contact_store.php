<?php
// public/contact_store.php
// Handles contact form submission using mysqli (procedural) + prepared statements

// --- Bootstrap ---
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/session.php'; // ensures session_start()
require_once __DIR__ . '/../app/csrf.php';
require_once __DIR__ . '/../app/auth.php';     // for is_admin()


 // Admins cannot submit the public contact form
if (is_admin()) {
       header('Location: /portfolio/Admin/contacts/index.php?no_form_for_admin=1'); exit;
}
// Quick helper to redirect back to the Contact section with flags
 function back_to_contact($params = []) {
     // Convert ?sent= to a one-time session flash
     if (isset($params['sent'])) {
         $ok  = (string)$params['sent'] === '1' || $params['sent'] === 1 || $params['sent'] === true;
         $_SESSION['contact_flash'] = [
             'ok'  => $ok,
             'msg' => $ok ? 'Thanks! Your message was sent.' : 'Sorry—something went wrong. Please try again.',
         ];
         unset($params['sent'], $params['error']);
     }
     // Always return to the contact section, without query string (prevents re-show on reload)
     $base = '../index.php';
     if (!empty($params)) {
         $qs = http_build_query($params);
         $base .= (strpos($base, '?') === false ? '?' : '&') . $qs;
     }
     header('Location: ' . $base . '#contact'); exit;
 }


// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    back_to_contact(['sent' => 0, 'error' => 'method']);
}

// --- CSRF check ---
// Expect a hidden input named "csrf_token" in the form
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])
) {
    back_to_contact(['sent' => 0, 'error' => 'csrf']);
}

// --- Basic validation ---
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    back_to_contact(['sent' => 0, 'error' => 'name']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    back_to_contact(['sent' => 0, 'error' => 'email']);
}
if (mb_strlen($message) < 5 || mb_strlen($message) > 5000) {
    back_to_contact(['sent' => 0, 'error' => 'message']);
}

// --- Insert (mysqli prepared statement) ---
$conn = db(); // from app/db.php (mysqli_connect + charset set)

$sql = "INSERT INTO contact_messages (name, email, message, created_at)
        VALUES (?, ?, ?, NOW())";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $message);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        // (optional) rotate CSRF to reduce replay chances
        unset($_SESSION['csrf_token']);
        back_to_contact(['sent' => 1]);
    } else {
        // DB error
        back_to_contact(['sent' => 0, 'error' => 'dberr']);
    }
} else {
    // Prepare failed
    back_to_contact(['sent' => 0, 'error' => 'prepare']);
}
