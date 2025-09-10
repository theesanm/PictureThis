<?php
// Configuration settings for the application

// Database configuration
define('DB_HOST', '127.0.0.1'); // Database host - use TCP to reach Docker-published MySQL on macOS
define('DB_USER', 'pt_user'); // Database username (matches docker .env.docker)
define('DB_PASS', 'pt_pass'); // Database password
define('DB_NAME', 'picturethis_dev'); // Database name

// Application settings
define('APP_NAME', 'PictureThis PHP'); // Application name
// Allow overriding APP_URL via environment. If not set, default to http://localhost:8000
$envAppUrl = getenv('APP_URL');
if ($envAppUrl && $envAppUrl !== false) {
	define('APP_URL', $envAppUrl);
} else {
	define('APP_URL', 'http://localhost:8000'); // Application URL
}

// Other configurations can be added here as needed

// PayFast sandbox credentials (development only) — provided by user
// Replace or move to environment variables for production
putenv('PAYFAST_MERCHANT_ID=10041798');
putenv('PAYFAST_MERCHANT_KEY=vlnqle74tnkl7');
putenv('PAYFAST_PASSPHRASE=ThisIsATestFromPictureThis');
?>