<?php
// Simple Environment Configuration System
// Change IS_PRODUCTION to switch between development and production

// ==========================================
// ENVIRONMENT SETTING - CHANGE THIS ONLY
// ==========================================
// For local development: set to false
// For production server: set to true
if (!defined('IS_PRODUCTION')) {
    // Auto-detect production environment based on multiple indicators
    $isProductionServer = false;

    // Check server hostname/domain
    if (isset($_SERVER['HTTP_HOST']) &&
        (strpos($_SERVER['HTTP_HOST'], 'demo.cfox.co.za') !== false ||
         strpos($_SERVER['HTTP_HOST'], 'cfox.co.za') !== false)) {
        $isProductionServer = true;
    }

    if (isset($_SERVER['SERVER_NAME']) &&
        (strpos($_SERVER['SERVER_NAME'], 'demo.cfox.co.za') !== false ||
         strpos($_SERVER['SERVER_NAME'], 'cfox.co.za') !== false)) {
        $isProductionServer = true;
    }

    // Check document root for production indicators
    if (isset($_SERVER['DOCUMENT_ROOT']) &&
        (strpos($_SERVER['DOCUMENT_ROOT'], 'cfoxcozj') !== false ||
         strpos($_SERVER['DOCUMENT_ROOT'], 'demo.cfox.co.za') !== false)) {
        $isProductionServer = true;
    }

    // Check if we're in a cPanel environment (common for production)
    if (isset($_SERVER['SCRIPT_NAME']) &&
        (strpos($_SERVER['SCRIPT_NAME'], '/home/cfoxcozj/') !== false ||
         getenv('HOME') && strpos(getenv('HOME'), 'cfoxcozj') !== false)) {
        $isProductionServer = true;
    }

    // Check current working directory for cPanel username
    if (strpos(getcwd(), 'cfoxcozj') !== false) {
        $isProductionServer = true;
    }

    // Check if we're running in a web server context (not CLI)
    if (PHP_SAPI !== 'cli') {
        // Additional web server checks can go here
        if (isset($_SERVER['REQUEST_URI']) || isset($_SERVER['QUERY_STRING'])) {
            // We're definitely in a web context
        }
    }

    // Database-based production detection as fallback
    if (!$isProductionServer) {
        try {
            // Try to connect with production database credentials
            $prodConfig = require __DIR__ . '/production.php';
            $pdo = new PDO(
                "mysql:host={$prodConfig['database']['host']};dbname={$prodConfig['database']['name']}",
                $prodConfig['database']['user'],
                $prodConfig['database']['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // If we can connect to production DB, we're likely in production
            $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && $result['count'] > 0) {
                $isProductionServer = true;
            }
            $pdo = null; // Close connection
        } catch (Exception $e) {
            // Can't connect to production DB, stay with current detection
        }
    }

    // Check for force production file
    if (file_exists(__DIR__ . '/../.force_production')) {
        $isProductionServer = true;
    }

    // Manual override via environment variable
    if (getenv('FORCE_PRODUCTION') === 'true') {
        $isProductionServer = true;
    }

    define('IS_PRODUCTION', $isProductionServer);
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
// DEFINE CONSTANTS - Load directly from config
// ==========================================

// Application settings
if (!defined('APP_ENV')) define('APP_ENV', $appEnv);
if (!defined('APP_NAME')) define('APP_NAME', $config['app']['name'] ?? 'PictureThis');
if (!defined('APP_URL')) define('APP_URL', $config['app']['url'] ?? 'http://localhost:8000');
if (!defined('APP_DEBUG')) define('APP_DEBUG', $config['app']['debug'] ?? !IS_PRODUCTION);

// Database configuration - Load directly from config
if (!defined('DB_HOST')) define('DB_HOST', $config['database']['host'] ?? '127.0.0.1');
if (!defined('DB_USER')) define('DB_USER', $config['database']['user'] ?? 'root');
if (!defined('DB_PASS')) define('DB_PASS', $config['database']['pass'] ?? '');
if (!defined('DB_NAME')) define('DB_NAME', $config['database']['name'] ?? 'picturethis');

// PayFast configuration
define('PAYFAST_MERCHANT_ID', $config['payfast']['merchant_id'] ?? '');
define('PAYFAST_MERCHANT_KEY', $config['payfast']['merchant_key'] ?? '');
define('PAYFAST_PASSPHRASE', $config['payfast']['passphrase'] ?? '');
define('PAYFAST_ENV', $config['payfast']['env'] ?? 'development');

// OpenRouter configuration
define('OPENROUTER_API_KEY', $config['openrouter']['api_key'] ?? '');
define('OPENROUTER_APP_URL', $config['openrouter']['app_url'] ?? '');
define('OPENROUTER_GEMINI_MODEL', $config['openrouter']['gemini_model'] ?? 'google/gemini-2.5-flash-image-preview');
define('OPENROUTER_MODEL', $config['openrouter']['model'] ?? 'openai/gpt-oss-20b:free');
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');

// Email configuration
define('SMTP_HOST', $config['email']['smtp_host'] ?? 'localhost');
define('SMTP_USERNAME', $config['email']['smtp_username'] ?? '');
define('SMTP_PASSWORD', $config['email']['smtp_password'] ?? '');
define('SMTP_PORT', $config['email']['smtp_port'] ?? '587');
define('FROM_EMAIL', $config['email']['from_email'] ?? 'noreply@localhost');

// Image configuration
define('IMAGE_RETENTION_DAYS', $config['images']['retention_days'] ?? 7);
define('MIN_IMAGES_PER_USER', $config['images']['min_images_per_user'] ?? 3);

// Agent configuration
if (!defined('AGENT_SESSION_TIMEOUT_MINUTES')) {
    define('AGENT_SESSION_TIMEOUT_MINUTES', $config['agent']['session_timeout_minutes'] ?? 30);
}

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
putenv('MIN_IMAGES_PER_USER=' . MIN_IMAGES_PER_USER);

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
