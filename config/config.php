<?php
// Simple Environment Configuration System
// Change IS_PRODUCTION to switch between development and production

// ==========================================
// ENVIRONMENT SETTING - CHANGE THIS ONLY
// ==========================================
// For local development: set to false
// For production server: set to true
if (!defined('IS_PRODUCTION')) {
    define('IS_PRODUCTION', true); // true = production, false = development
}

// ==========================================
// LOAD ENVIRONMENT VARIABLES FROM .env FILE
// ==========================================
// Load .env file from parent directory (outside web root for security)
$envFile = __DIR__ . '/../.env';
$envVars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }
}

// Function to get environment variable (from .env file or server env)
function getEnvVar($key, $default = null) {
    global $envVars;

    // First check .env file
    if (isset($envVars[$key])) {
        return $envVars[$key];
    }

    // Then check server environment variables
    $serverKey = 'PICTURETHIS_' . strtoupper($key);
    if (getenv($serverKey) !== false) {
        return getenv($serverKey);
    }

    return $default;
}

// ==========================================
// LOAD ENVIRONMENT CONFIGURATION
// ==========================================

$appEnv = IS_PRODUCTION ? 'production' : 'development';
$configFile = __DIR__ . "/{$appEnv}.php";

if (!file_exists($configFile)) {
    die("Configuration file for environment '{$appEnv}' not found: {$configFile}");
}

$config = require $configFile;

// ==========================================
// ENVIRONMENT VARIABLE OVERRIDES (Production Only)
// ==========================================
// Allow environment variables to override config values (useful for production)

function getConfigValue($section, $key, $default = null) {
    global $config;

    // Check config array
    return $config[$section][$key] ?? $default;
}

// ==========================================
// DEFINE CONSTANTS
// ==========================================

// Application settings
if (!defined('APP_ENV')) define('APP_ENV', $appEnv);
if (!defined('APP_NAME')) define('APP_NAME', getConfigValue('app', 'name', 'PictureThis'));
if (!defined('APP_URL')) define('APP_URL', getConfigValue('app', 'url', 'http://localhost:8000'));
if (!defined('APP_DEBUG')) define('APP_DEBUG', getConfigValue('app', 'debug', !IS_PRODUCTION));

// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', getConfigValue('database', 'host', '127.0.0.1'));
if (!defined('DB_USER')) define('DB_USER', getConfigValue('database', 'user', 'root'));
if (!defined('DB_PASS')) define('DB_PASS', getConfigValue('database', 'pass', ''));
if (!defined('DB_NAME')) define('DB_NAME', getConfigValue('database', 'name', 'picturethis'));

// PayFast configuration
define('PAYFAST_MERCHANT_ID', getConfigValue('payfast', 'merchant_id', ''));
define('PAYFAST_MERCHANT_KEY', getConfigValue('payfast', 'merchant_key', ''));
define('PAYFAST_PASSPHRASE', getConfigValue('payfast', 'passphrase', ''));
define('PAYFAST_ENV', getConfigValue('payfast', 'env', 'development'));

// OpenRouter configuration
define('OPENROUTER_API_KEY', getConfigValue('openrouter', 'api_key', ''));
define('OPENROUTER_APP_URL', getConfigValue('openrouter', 'app_url', ''));
define('OPENROUTER_GEMINI_MODEL', getConfigValue('openrouter', 'gemini_model', 'google/gemini-2.5-flash-image-preview'));
define('OPENROUTER_MODEL', getConfigValue('openrouter', 'model', 'openai/gpt-oss-20b:free'));
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');

// Email configuration
define('SMTP_HOST', getConfigValue('email', 'smtp_host', 'localhost'));
define('SMTP_USERNAME', getConfigValue('email', 'smtp_username', ''));
define('SMTP_PASSWORD', getConfigValue('email', 'smtp_password', ''));
define('SMTP_PORT', getConfigValue('email', 'smtp_port', '587'));
define('FROM_EMAIL', getConfigValue('email', 'from_email', 'noreply@localhost'));

// Image configuration
define('IMAGE_RETENTION_DAYS', getConfigValue('images', 'retention_days', 7));
define('MIN_IMAGES_PER_USER', getConfigValue('images', 'min_images_per_user', 3));

// OpenRouter App Title
define('OPENROUTER_APP_TITLE', 'PictureThis AI');

// Timezone configuration
define('SERVER_TIMEZONE', 'UTC');
define('DEFAULT_USER_TIMEZONE', 'UTC');
define('TOKEN_GRACE_PERIOD_MINUTES', 5);

// Note: Timezone is set in index.php to match system timezone for session calculations
// date_default_timezone_set(SERVER_TIMEZONE);

// ==========================================
// RUNTIME CONSTANTS (for backward compatibility)
// ==========================================

define('OPENROUTER_API_KEY_RUNTIME', OPENROUTER_API_KEY);
define('OPENROUTER_APP_URL_RUNTIME', OPENROUTER_APP_URL);
define('OPENROUTER_GEMINI_MODEL_RUNTIME', OPENROUTER_GEMINI_MODEL);
define('OPENROUTER_MODEL_RUNTIME', OPENROUTER_MODEL);

// ==========================================
// ENVIRONMENT VARIABLE CONSISTENCY
// ==========================================

// Ensure environment variables are set for consistency
putenv('APP_ENV=' . APP_ENV);
putenv('DB_HOST=' . DB_HOST);
putenv('DB_USER=' . DB_USER);
putenv('DB_PASS=' . DB_PASS);
putenv('DB_NAME=' . DB_NAME);
putenv('APP_URL=' . APP_URL);
putenv('PAYFAST_MERCHANT_ID=' . PAYFAST_MERCHANT_ID);
putenv('PAYFAST_MERCHANT_KEY=' . PAYFAST_MERCHANT_KEY);
putenv('PAYFAST_PASSPHRASE=' . PAYFAST_PASSPHRASE);
putenv('PAYFAST_ENV=' . PAYFAST_ENV);
putenv('OPENROUTER_API_KEY=' . OPENROUTER_API_KEY);
putenv('OPENROUTER_APP_URL=' . OPENROUTER_APP_URL);
putenv('SMTP_PASSWORD=' . SMTP_PASSWORD);
putenv('IMAGE_RETENTION_DAYS=' . IMAGE_RETENTION_DAYS);
putenv('MIN_IMAGES_PER_USER' . MIN_IMAGES_PER_USER);

// Set $_ENV for consistency
$_ENV['APP_ENV'] = APP_ENV;
$_ENV['DB_HOST'] = DB_HOST;
$_ENV['DB_USER'] = DB_USER;
$_ENV['DB_PASS'] = DB_PASS;
$_ENV['DB_NAME'] = DB_NAME;
$_ENV['APP_URL'] = APP_URL;
$_ENV['PAYFAST_MERCHANT_ID'] = PAYFAST_MERCHANT_ID;
$_ENV['PAYFAST_MERCHANT_KEY'] = PAYFAST_MERCHANT_KEY;
$_ENV['PAYFAST_PASSPHRASE'] = PAYFAST_PASSPHRASE;
$_ENV['PAYFAST_ENV'] = PAYFAST_ENV;
$_ENV['OPENROUTER_API_KEY'] = OPENROUTER_API_KEY;
$_ENV['OPENROUTER_APP_URL'] = OPENROUTER_APP_URL;
$_ENV['SMTP_PASSWORD'] = SMTP_PASSWORD;
$_ENV['IMAGE_RETENTION_DAYS'] = IMAGE_RETENTION_DAYS;
$_ENV['MIN_IMAGES_PER_USER'] = MIN_IMAGES_PER_USER;

?>
