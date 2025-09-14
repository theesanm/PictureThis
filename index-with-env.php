<?php
// Set environment variables directly in PHP (cPanel workaround)
putenv('APP_ENV=production');
putenv('DB_HOST=127.0.0.1');
putenv('DB_USER=cfoxcozj_picThisdb');
putenv('DB_PASS=LfUYHI%]{sjb5A*u');
putenv('DB_NAME=cfoxcozj_PictureThis');
putenv('PAYFAST_MERCHANT_ID=10041798');
putenv('PAYFAST_MERCHANT_KEY=vlnqle74tnkl7');
putenv('PAYFAST_PASSPHRASE=ThisIsATestFromPictureThis');
putenv('OPENROUTER_API_KEY=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');
putenv('OPENROUTER_APP_URL=https://demo.cfox.co.za');
putenv('APP_URL=https://demo.cfox.co.za');

// Simple front controller for dev server. Routes payfast return/cancel to views.

// Load app config (defines APP_NAME, DB_* and other settings). Use a safe fallback if missing.
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
} else {
    if (!defined('APP_NAME')) {
        define('APP_NAME', 'PictureThis');
    }
}

// Session configuration for better compatibility
ini_set('session.cookie_domain', ''); // Allow sessions to work across subdomains
ini_set('session.cookie_secure', 1); // Require HTTPS for sessions
ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session cookie
ini_set('session.use_only_cookies', 1); // Use only cookies for sessions

// Start session early (if possible) so views can rely on it. Guard with headers_sent().
if (!headers_sent()) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Opt-in debug flag: visit any URL with ?__debug=1 to enable full error display temporarily.
if (isset($_GET['__debug']) && $_GET['__debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // Helpful header for debugging via browser
    header('X-PictureThis-Debug: enabled');
}
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle home page
if ($path === '/' || $path === '') {
    require_once __DIR__ . '/src/controllers/HomeController.php';
    $ctrl = new HomeController();
    $ctrl->index();
    exit;
}

if ($path === '/payment/success') {
    include __DIR__ . '/src/views/payment_success.php';
    exit;
}
if ($path === '/payment/cancelled') {
    include __DIR__ . '/src/views/payment_cancelled.php';
    exit;
}