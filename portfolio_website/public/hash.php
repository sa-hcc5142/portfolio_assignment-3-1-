<?php
$pw = $_GET['pw'] ?? '';
header('Content-Type: text/plain; charset=utf-8');
if ($pw === '') { echo "Usage: hash.php?pw=abcd12345"; exit; }
echo password_hash($pw, PASSWORD_DEFAULT);