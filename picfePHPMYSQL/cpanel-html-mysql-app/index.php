<?php
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
if ($path === '/payment/success/iframe/success') {
    include __DIR__ . '/src/views/payment_iframe_success.php';
    exit;
}
if ($path === '/payment/cancelled/iframe/cancel') {
    include __DIR__ . '/src/views/payment_iframe_cancel.php';
    exit;
}
if ($path === '/pricing') {
    require_once __DIR__ . '/src/controllers/PricingController.php';
    $ctrl = new PricingController();
    $ctrl->index();
    exit;
}

// Handle other pages
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

// Handle authentication pages
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

// Handle main app pages (require authentication in controllers)
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

// Handle PayFast popup success (with query params for payment_id, user_id, package_id)
if ($path === '/payment/popup/success') {
    session_start(); // Ensure session for flash messages

    $successMessage = 'Payment processed successfully!';
    $errorMessage = null;

    // NOTE: Credits are added by the ITN handler, not here
    // This route is only for displaying the success page

    // Try to include success view, fallback to simple HTML if it fails
    if (file_exists(__DIR__ . '/src/views/payment_popup_success.php')) {
        include __DIR__ . '/src/views/payment_popup_success.php';
    } else {
        // Fallback success page
        echo '<!DOCTYPE html>
        <html><head><title>Payment Success</title>
        <link href="https://cdn.tailwindcss.com" rel="stylesheet"></head>
        <body class="bg-gray-900 text-white p-8">
            <div class="max-w-md mx-auto bg-gray-800 p-6 rounded-lg">
                <h1 class="text-2xl font-bold mb-4">Payment Successful!</h1>';
        if ($errorMessage) {
            echo '<p class="text-red-400 mb-4">' . htmlspecialchars($errorMessage) . '</p>';
        } else {
            echo '<p class="text-green-400 mb-4">Your payment has been processed successfully.</p>
            <script>
                // Send message to parent window
                if (window.opener) {
                    window.opener.postMessage({
                        type: "payment_success",
                        payment_id: "' . htmlspecialchars($_GET['payment_id'] ?? '') . '",
                        user_id: "' . htmlspecialchars($_GET['user_id'] ?? '') . '",
                        package_id: "' . htmlspecialchars($_GET['package_id'] ?? '') . '"
                    }, "*");
                }
                // Auto-close after 2 seconds
                setTimeout(() => { window.close(); }, 2000);
            </script>';
        }
        echo '</div></body></html>';
    }
    exit;
}

// Minimal API routes for PayFast integration and polling
if (strpos($path, '/api/') === 0) {
    // Lazy-load controllers
    require_once __DIR__ . '/src/controllers/PricingController.php';
    require_once __DIR__ . '/src/controllers/GenerateController.php';
    $pricingCtrl = new PricingController();
    $generateCtrl = new GenerateController();

    if ($path === '/api/payments/status') {
        $pricingCtrl->paymentStatus();
        exit;
    }

    if ($path === '/api/credits/payfast/notify') {
        $pricingCtrl->notify();
        exit;
    }

    if ($path === '/api/credits/payfast/test') {
        $pricingCtrl->testItn();
        exit;
    }

    if ($path === '/api/credits/initiate') {
        $pricingCtrl->initiate();
        exit;
    }

    if ($path === '/api/enhance') {
        $generateCtrl->enhance();
        exit;
    }
}

// Fall back to existing server files if present (index.php in src or public)
if (file_exists(__DIR__ . '/src/server.php')) {
    include __DIR__ . '/src/server.php';
    exit;
}

// If nothing else, let PHP built-in server handle static files; otherwise show 404
$requested = __DIR__ . preg_replace('#\?.*$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && file_exists($requested) && !is_dir($requested)) {
    return false; // serve the requested resource as-is
}

http_response_code(404);
echo "404 Not Found";
