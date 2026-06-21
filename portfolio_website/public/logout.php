<?php
// public/logout.php
// Destroys the session (and last_login_at cookie) then redirects to /portfolio/#home

require_once __DIR__ . '/../app/session.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/auth.php';

logout_user('#home'); // sends you to /portfolio/#home
