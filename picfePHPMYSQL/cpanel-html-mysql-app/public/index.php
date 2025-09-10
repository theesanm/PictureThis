<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/lib/db.php';

session_start();

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json');
    exit;
}

// Simple router: map path to controller
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routes = [
	'/' => 'HomeController@index',
	'/home' => 'HomeController@index',
	'/login' => 'LoginController@index',
	'/register' => 'RegisterController@index',
	'/logout' => 'LoginController@logout',
	'/generate' => 'GenerateController@index',
	'/api/generate' => 'GenerateController@generate',
	'/api/enhance' => 'GenerateController@enhance',
	'/api/user/credits' => 'GenerateController@getUserCredits',
	'/dashboard' => 'DashboardController@index',
	'/gallery' => 'GalleryController@index',
	'/profile' => 'ProfileController@index',
	'/pricing' => 'PricingController@index',
	'/about' => 'HomeController@about',
	'/privacy' => 'HomeController@privacy',
	'/terms' => 'HomeController@terms',
	'/credits/packages' => 'PricingController@packages',
	'/credits/initiate' => 'PricingController@initiate',
	'/api/credits/initiate' => 'PricingController@initiate',
	'/api/credits/payfast/notify' => 'PricingController@notify',
	'/api/credits/payfast/test' => 'PricingController@testItn',
	'/api/credits/payfast/test-itn' => 'PricingController@testItn',
	'/api/payments/status' => 'PricingController@paymentStatus',
	'/payment/success' => 'PricingController@success',
	'/payment/cancelled' => 'PricingController@cancelled',
	'/payment/success/iframe/success' => 'PricingController@iframeSuccess',
	'/payment/cancelled/iframe/cancel' => 'PricingController@iframeCancel',
	'/payment/popup/success' => 'PricingController@popupSuccess',
	'/payment/popup/cancel' => 'PricingController@popupCancel',
	'/api/payments/status' => 'PricingController@paymentStatus',
	'/admin' => 'AdminController@index',
	'/admin/users' => 'AdminController@users',
	'/admin/credits' => 'AdminController@credits',
	'/admin/settings' => 'AdminController@settings',
	'/admin/analytics' => 'AdminController@analytics',
];

// Handle uploads serving
if (strpos($path, '/uploads/') === 0) {
    $filename = basename($path);
    $filepath = __DIR__ . '/../uploads/' . $filename;
    
    if (file_exists($filepath)) {
        $mime = mime_content_type($filepath);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
        readfile($filepath);
        exit;
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>Image not found</p>";
        exit;
    }
}

if (!isset($routes[$path])) {
	// fallback: try trimming trailing slash
	$trimmed = rtrim($path, '/');
	if ($trimmed === '') $trimmed = '/';
	if (isset($routes[$trimmed])) $path = $trimmed;
}

if (!isset($routes[$path])) {
	http_response_code(404);
	echo "<h1>404 Not Found</h1>";
	exit;
}

list($controllerName, $method) = explode('@', $routes[$path]);
$controllerFile = __DIR__ . '/../src/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
	http_response_code(500);
	echo "<h1>500 Server Error</h1><p>Missing controller: {$controllerName}</p>";
	exit;
}

require_once $controllerFile;

$controller = new $controllerName();
if (!method_exists($controller, $method)) {
	http_response_code(500);
	echo "<h1>500 Server Error</h1><p>Controller method not found</p>";
	exit;
}

$controller->{$method}();
?>