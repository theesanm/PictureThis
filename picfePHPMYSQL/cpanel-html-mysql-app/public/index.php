<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/lib/db.php';

session_start();

// Simple router: map path to controller
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routes = [
	'/' => 'HomeController@index',
	'/home' => 'HomeController@index',
	'/login' => 'LoginController@index',
	'/register' => 'RegisterController@index',
	'/generate' => 'GenerateController@index',
	'/dashboard' => 'DashboardController@index',
	'/gallery' => 'GalleryController@index',
	'/profile' => 'ProfileController@index',
	'/pricing' => 'PricingController@index',
	'/credits/packages' => 'PricingController@packages',
	'/credits/initiate' => 'PricingController@initiate',
	'/api/credits/payfast/notify' => 'PricingController@notify',
	'/api/credits/payfast/test-itn' => 'PricingController@testItn',
	'/payment/success' => 'PricingController@success',
	'/payment/cancelled' => 'PricingController@cancelled',
	'/api/payments/status' => 'PricingController@paymentStatus',
	'/admin' => 'AdminController@index',
	'/admin/users' => 'AdminController@users',
	'/admin/credits' => 'AdminController@credits',
	'/admin/settings' => 'AdminController@settings',
	'/admin/analytics' => 'AdminController@analytics',
];

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