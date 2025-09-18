<?php
// Simple front controller for PictureThis application

// Load app config (defines APP_NAME, DB_* and other settings)
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
} else {
    // Fallback if config not found
    if (!defined('APP_NAME')) {
        define('APP_NAME', 'PictureThis');
    }
    if (!defined('SERVER_TIMEZONE')) {
        define('SERVER_TIMEZONE', 'UTC');
    }
}

// Set timezone
date_default_timezone_set('Africa/Johannesburg');

// Session configuration
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Start session
if (!headers_sent()) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Debug flag
if (isset($_GET['__debug']) && $_GET['__debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    header('X-PictureThis-Debug: enabled');
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Allow direct access to /tests/ directory (for diagnostics and debugging)
if (strpos($path, '/tests/') === 0) {
    $requestedFile = __DIR__ . $path;
    if (file_exists($requestedFile) && !is_dir($requestedFile)) {
        $extension = strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION));
        $contentTypes = [
            'html' => 'text/html',
            'php' => 'text/html',
            'txt' => 'text/plain',
            'json' => 'application/json',
            'css' => 'text/css',
            'js' => 'application/javascript'
        ];

        if (isset($contentTypes[$extension])) {
            header('Content-Type: ' . $contentTypes[$extension]);
        }

        if ($extension === 'php') {
            include $requestedFile;
        } else {
            readfile($requestedFile);
        }
        exit;
    }
}

// Allow direct access to root test files
$testFiles = ['diagnostics.php', 'minimal_test.php', 'routing_test.php', 'server_test.php', 'phpinfo.php', 'html_test.html'];
if (in_array(basename($path), $testFiles)) {
    $requestedFile = __DIR__ . '/' . basename($path);
    if (file_exists($requestedFile)) {
        $extension = strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION));
        $contentTypes = [
            'html' => 'text/html',
            'php' => 'text/html',
            'txt' => 'text/plain',
            'json' => 'application/json'
        ];

        if (isset($contentTypes[$extension])) {
            header('Content-Type: ' . $contentTypes[$extension]);
        }

        if ($extension === 'php') {
            include $requestedFile;
        } else {
            readfile($requestedFile);
        }
        exit;
    }
}

// Route to appropriate controllers
try {
    // Home page
    if ($path === '/' || $path === '') {
        require_once __DIR__ . '/src/controllers/HomeController.php';
        $ctrl = new HomeController();
        $ctrl->index();
        exit;
    }

    // Other pages
    if ($path === '/about') {
        require_once __DIR__ . '/src/controllers/HomeController.php';
        $ctrl = new HomeController();
        $ctrl->about();
        exit;
    }

    if ($path === '/privacy') {
        require_once __DIR__ . '/src/controllers/HomeController.php';
        $ctrl = new HomeController();
        $ctrl->privacy();
        exit;
    }

    if ($path === '/terms') {
        require_once __DIR__ . '/src/controllers/HomeController.php';
        $ctrl = new HomeController();
        $ctrl->terms();
        exit;
    }

    // Authentication pages
    if ($path === '/login') {
        require_once __DIR__ . '/src/controllers/LoginController.php';
        $ctrl = new LoginController();
        $ctrl->index();
        exit;
    }

    if ($path === '/register') {
        require_once __DIR__ . '/src/controllers/RegisterController.php';
        $ctrl = new RegisterController();
        $ctrl->index();
        exit;
    }

    if ($path === '/logout') {
        require_once __DIR__ . '/src/controllers/LoginController.php';
        $ctrl = new LoginController();
        $ctrl->logout();
        exit;
    }

    if ($path === '/dashboard') {
        require_once __DIR__ . '/src/controllers/DashboardController.php';
        $ctrl = new DashboardController();
        $ctrl->index();
        exit;
    }

    if ($path === '/generate') {
        require_once __DIR__ . '/src/controllers/GenerateController.php';
        $ctrl = new GenerateController();
        $ctrl->index();
        exit;
    }

    if ($path === '/gallery') {
        require_once __DIR__ . '/src/controllers/GalleryController.php';
        $ctrl = new GalleryController();
        $ctrl->index();
        exit;
    }

    if ($path === '/profile') {
        require_once __DIR__ . '/src/controllers/ProfileController.php';
        $ctrl = new ProfileController();
        $ctrl->index();
        exit;
    }

    if ($path === '/check-email') {
        require_once __DIR__ . '/src/controllers/CheckEmailController.php';
        $ctrl = new CheckEmailController();
        $ctrl->index();
        exit;
    }

    if ($path === '/verify-email') {
        require_once __DIR__ . '/src/controllers/EmailVerificationController.php';
        $ctrl = new EmailVerificationController();
        $ctrl->verify();
        exit;
    }

    if ($path === '/forgot-password') {
        require_once __DIR__ . '/src/controllers/PasswordResetController.php';
        $ctrl = new PasswordResetController();
        $ctrl->forgot();
        exit;
    }

    if ($path === '/reset-password') {
        require_once __DIR__ . '/src/controllers/PasswordResetController.php';
        $ctrl = new PasswordResetController();
        $ctrl->reset();
        exit;
    }

    if ($path === '/pricing') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->index();
        exit;
    }

    if ($path === '/pricing/packages') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->packages();
        exit;
    }

    if ($path === '/pricing/initiate') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->initiate();
        exit;
    }

    if ($path === '/pricing/notify') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->notify();
        exit;
    }

    if ($path === '/pricing/test-itn') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->testItn();
        exit;
    }

    if ($path === '/pricing/success') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->success();
        exit;
    }

    if ($path === '/pricing/cancelled') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->cancelled();
        exit;
    }

    if ($path === '/pricing/iframe-success') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->iframeSuccess();
        exit;
    }

    if ($path === '/pricing/iframe-cancel') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->iframeCancel();
        exit;
    }

    if ($path === '/pricing/popup-success') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->popupSuccess();
        exit;
    }

    if ($path === '/pricing/popup-cancel') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->popupCancel();
        exit;
    }

    if ($path === '/pricing/status') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->paymentStatus();
        exit;
    }

    // Also support legacy/public-facing /payment/* routes so popup/return URLs work when this index.php is used
    if ($path === '/payment/popup/success') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->popupSuccess();
        exit;
    }

    if ($path === '/payment/popup/cancel') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->popupCancel();
        exit;
    }

    if ($path === '/payment/success') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->success();
        exit;
    }

    if ($path === '/payment/cancelled') {
        require_once __DIR__ . '/src/controllers/PricingController.php';
        $ctrl = new PricingController();
        $ctrl->cancelled();
        exit;
    }

    if ($path === '/admin') {
        require_once __DIR__ . '/src/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->index();
        exit;
    }

    if ($path === '/admin/users') {
        require_once __DIR__ . '/src/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->users();
        exit;
    }

    if ($path === '/admin/credits') {
        require_once __DIR__ . '/src/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->credits();
        exit;
    }

    if ($path === '/admin/settings') {
        require_once __DIR__ . '/src/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->settings();
        exit;
    }

    if ($path === '/admin/analytics') {
        require_once __DIR__ . '/src/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->analytics();
        exit;
    }

    // API routes
    if (strpos($path, '/api/') === 0) {
        require_once __DIR__ . '/api/index.php';
        exit;
    }

    // 404 for unknown routes
    http_response_code(404);
    echo "<!DOCTYPE html>
    <html>
    <head><title>404 Not Found</title></head>
    <body>
        <h1>404 Not Found</h1>
        <p>The page you're looking for doesn't exist.</p>
        <a href='/'>Go Home</a>
    </body>
    </html>";

} catch (Exception $e) {
    http_response_code(500);
    echo "<!DOCTYPE html>
    <html>
    <head><title>Server Error</title></head>
    <body>
        <h1>Server Error</h1>
        <p>Something went wrong. Please try again later.</p>
        <a href='/'>Go Home</a>
    </body>
    </html>";
}
?>