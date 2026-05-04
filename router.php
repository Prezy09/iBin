<?php
/**
 * router.php — PHP built-in server router for Railway deployment.
 *
 * Usage (via Procfile):  php -S 0.0.0.0:$PORT router.php
 *
 * The built-in server calls this file for every request.
 * - Real static files (css, js, images, fonts…) are served directly.
 * - Everything else is funnelled through the MVC bootstrap (index.php).
 */

$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// Let the built-in server handle existing static files as-is.
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    return false;
}

// All other requests (clean URLs like /login, /bins, etc.) go through MVC.
require __DIR__ . '/index.php';
