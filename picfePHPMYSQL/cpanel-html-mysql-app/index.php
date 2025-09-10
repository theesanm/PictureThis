<?php
// Simple front controller for dev server. Routes payfast return/cancel to views.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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

// Minimal API routes for PayFast integration and polling
if (strpos($path, '/api/') === 0) {
    // Lazy-load controller
    require_once __DIR__ . '/src/controllers/PricingController.php';
    $ctrl = new PricingController();

    if ($path === '/api/payments/status') {
        $ctrl->paymentStatus();
        exit;
    }

    if ($path === '/api/credits/payfast/notify') {
        $ctrl->notify();
        exit;
    }

    if ($path === '/api/credits/payfast/test') {
        $ctrl->testItn();
        exit;
    }

    if ($path === '/api/credits/initiate') {
        $ctrl->initiate();
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
