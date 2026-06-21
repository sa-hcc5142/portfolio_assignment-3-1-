<?php
require_once __DIR__.'/config.php';

function db() {
    static $conn = null;
    if ($conn === null) {
        $conn = mysqli_connect(
            $GLOBALS['DB_HOST'],
            $GLOBALS['DB_USER'],
            $GLOBALS['DB_PASS'],
            $GLOBALS['DB_NAME'],
            $GLOBALS['DB_PORT']   // ← important
        );
        if (!$conn) {
            die('Connection failed: ' . mysqli_connect_error());
        }
        mysqli_set_charset($conn, 'utf8mb4');
    }
    return $conn;
}
