<?php
// Application base URL. Change '/portfolio2' to your folder name if different.
$APP_URL = (isset($_SERVER['HTTP_HOST'])
    ? ((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $_SERVER['HTTP_HOST'])
    : 'http://localhost') . '/portfolio';

// Database credentials (XAMPP MySQL on custom port 4307)
$DB_HOST = '127.0.0.1';  // use 127.0.0.1 to respect custom port
$DB_PORT = 3306;
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'portfolio2';
