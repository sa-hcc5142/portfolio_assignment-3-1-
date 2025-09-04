<?php
// XAMPP/htdocs/portfolio2/Admin/certificates/delete.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../public/login.php");
    exit;
}
require_once dirname(__DIR__, 2) . '/app/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;
