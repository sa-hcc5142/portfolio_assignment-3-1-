<?php
function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function redirect($path) { header('Location: ' . $path); exit; }
