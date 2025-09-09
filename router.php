<?php
// router.php - used with PHP built-in server for local preview
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files from public/ if they exist
$publicPath = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicPath) && is_file($publicPath)) {
    // Do not directly read out PHP files; let the built-in server interpret
    // them. Only serve static files (css/js/images/json/html, etc.) here.
    $ext = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
    if ($ext === 'php') {
        // If the requested path is a PHP file inside public/, execute it so
        // the output is rendered instead of returning the source.
        // This keeps the docroot flexible (we serve from project root) while
        // ensuring PHP files under public/ run correctly.
        require $publicPath;
        return true;
    }

    $mime = function_exists('mime_content_type') ? mime_content_type($publicPath) : null;
    if (!$mime) {
        // Basic fallback mapping
        $map = [
            'css' => 'text/css',
            'js'  => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg'=> 'image/jpeg',
            'svg' => 'image/svg+xml',
            'json'=> 'application/json',
            'html'=> 'text/html',
        ];
        $mime = $map[$ext] ?? 'application/octet-stream';
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($publicPath));
    readfile($publicPath);
    return true;
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
