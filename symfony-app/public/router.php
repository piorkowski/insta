<?php

// Router for PHP built-in server - serves static files directly
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__.$path;

if ($path !== '/' && is_file($file)) {
    return false; // serve static file directly
}

require __DIR__.'/index.php';
