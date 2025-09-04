<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// cookie helper (example)
function set_cookie($name, $value, $days = 365) {
    setcookie($name, $value, time() + 60*60*24*$days, '/');
}
