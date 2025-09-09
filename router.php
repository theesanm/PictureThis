<?php
// router.php - used with PHP built-in server for local preview
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files from public/ if they exist
$publicPath = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicPath) && is_file($publicPath)) {
    return false; // let the server serve the file
}

// Route API requests to src/api
if (preg_match('#^/api/(.*)$#', $uri, $m)) {
    $file = __DIR__ . '/src/api/' . $m[1];
    if (file_exists($file)) {
        require $file;
        return true;
    }
}

// Fallback to public/index.php
require __DIR__ . '/public/index.php';
