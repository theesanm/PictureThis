<?php
// Configuration settings for the application

// Database configuration - Docker MySQL for local development
define('DB_HOST', 'localhost:3306'); // Docker MySQL host with port
define('DB_USER', 'pt_user'); // Docker database username
define('DB_PASS', 'pt_pass'); // Docker database password
define('DB_NAME', 'picturethis_dev'); // Docker database name

// Application settings
define('APP_NAME', 'PictureThis PHP'); // Application name
// Allow overriding APP_URL via environment. If not set, default to http://localhost:8000
$envAppUrl = getenv('APP_URL');
if ($envAppUrl && $envAppUrl !== false) {
	define('APP_URL', $envAppUrl);
} else {
	define('APP_URL', 'http://localhost:8000'); // Local development URL
}

// Session configuration for better ngrok compatibility
ini_set('session.cookie_domain', ''); // Allow sessions to work across subdomains
ini_set('session.cookie_secure', 0); // Allow non-HTTPS for development
ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session cookie
ini_set('session.use_only_cookies', 1); // Use only cookies for sessions

// PayFast configuration (sandbox credentials for development)
// Replace with production credentials for live environment
define('PAYFAST_MERCHANT_ID', '10041798');
define('PAYFAST_MERCHANT_KEY', 'vlnqle74tnkl7');
define('PAYFAST_PASSPHRASE', 'ThisIsATestFromPictureThis');

// OpenRouter API configuration for image generation and prompt enhancement
define('OPENROUTER_API_KEY', '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');
define('OPENROUTER_APP_URL', 'https://demo.cfox.co.za'); // Update with your subdomain
define('OPENROUTER_GEMINI_MODEL', 'google/gemini-2.5-flash-image-preview');
define('OPENROUTER_MODEL', 'openai/gpt-oss-20b:free');
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');

// Environment variable fallbacks (for production deployment)
if (getenv('OPENROUTER_API_KEY')) {
    define('OPENROUTER_API_KEY_RUNTIME', getenv('OPENROUTER_API_KEY'));
} else {
    define('OPENROUTER_API_KEY_RUNTIME', OPENROUTER_API_KEY);
}

if (getenv('OPENROUTER_APP_URL')) {
    define('OPENROUTER_APP_URL_RUNTIME', getenv('OPENROUTER_APP_URL'));
} else {
    define('OPENROUTER_APP_URL_RUNTIME', OPENROUTER_APP_URL);
}

if (getenv('OPENROUTER_GEMINI_MODEL')) {
    define('OPENROUTER_GEMINI_MODEL_RUNTIME', getenv('OPENROUTER_GEMINI_MODEL'));
} else {
    define('OPENROUTER_GEMINI_MODEL_RUNTIME', OPENROUTER_GEMINI_MODEL);
}

if (getenv('OPENROUTER_MODEL')) {
    define('OPENROUTER_MODEL_RUNTIME', getenv('OPENROUTER_MODEL'));
} else {
    define('OPENROUTER_MODEL_RUNTIME', OPENROUTER_MODEL);
}

// Set environment variables for backward compatibility
putenv('PAYFAST_MERCHANT_ID=' . PAYFAST_MERCHANT_ID);
putenv('PAYFAST_MERCHANT_KEY=' . PAYFAST_MERCHANT_KEY);
putenv('PAYFAST_PASSPHRASE=' . PAYFAST_PASSPHRASE);
putenv('OPENROUTER_API_KEY=' . OPENROUTER_API_KEY_RUNTIME);
putenv('OPENROUTER_APP_URL=' . OPENROUTER_APP_URL_RUNTIME);
putenv('OPENROUTER_GEMINI_MODEL=' . OPENROUTER_GEMINI_MODEL_RUNTIME);
putenv('OPENROUTER_MODEL=' . OPENROUTER_MODEL_RUNTIME);
?>