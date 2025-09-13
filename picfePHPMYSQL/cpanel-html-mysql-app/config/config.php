<?php
// Configuration settings for the application

// Set timezone to UTC for consistent server time
date_default_timezone_set('UTC');

// Database configuration - loaded from environment variables (.htaccess)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'cfoxcozj_picThisdb');
define('DB_PASS', getenv('DB_PASS') ?: ''); // Set via .htaccess
define('DB_NAME', getenv('DB_NAME') ?: 'cfoxcozj_PictureThis');

// Application settings
define('APP_NAME', 'PictureThis PHP');

// Environment detection - more reliable than getenv() for some setups
$appEnv = getenv('APP_ENV') ?: 'development';
define('APP_ENV', $appEnv);
define('IS_PRODUCTION', $appEnv === 'production');

// Application URL - loaded from environment
$appUrl = getenv('APP_URL') ?: 'https://demo.cfox.co.za';
define('APP_URL', $appUrl);

// PayFast configuration - loaded from environment variables
define('PAYFAST_MERCHANT_ID', getenv('PAYFAST_MERCHANT_ID') ?: '');
define('PAYFAST_MERCHANT_KEY', getenv('PAYFAST_MERCHANT_KEY') ?: '');
define('PAYFAST_PASSPHRASE', getenv('PAYFAST_PASSPHRASE') ?: '');

// OpenRouter API configuration - loaded from environment variables
define('OPENROUTER_API_KEY', getenv('OPENROUTER_API_KEY') ?: '');
define('OPENROUTER_APP_URL', getenv('OPENROUTER_APP_URL') ?: 'https://demo.cfox.co.za');
define('OPENROUTER_GEMINI_MODEL', getenv('OPENROUTER_GEMINI_MODEL') ?: 'google/gemini-2.5-flash-image-preview');
define('OPENROUTER_MODEL', getenv('OPENROUTER_MODEL') ?: 'openai/gpt-oss-20b:free');
define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');

// Runtime constants with environment variable support
define('OPENROUTER_API_KEY_RUNTIME', getenv('OPENROUTER_API_KEY') ?: OPENROUTER_API_KEY);
define('OPENROUTER_APP_URL_RUNTIME', getenv('OPENROUTER_APP_URL') ?: OPENROUTER_APP_URL);
define('OPENROUTER_GEMINI_MODEL_RUNTIME', getenv('OPENROUTER_GEMINI_MODEL') ?: OPENROUTER_GEMINI_MODEL);
define('OPENROUTER_MODEL_RUNTIME', getenv('OPENROUTER_MODEL') ?: OPENROUTER_MODEL);

// Timezone configuration
define('SERVER_TIMEZONE', 'UTC');
define('DEFAULT_USER_TIMEZONE', 'UTC');
define('TOKEN_GRACE_PERIOD_MINUTES', 5); // 5-minute grace period for token validation

// Set environment variables for consistency (will use values from .htaccess if set)
if (PAYFAST_MERCHANT_ID) putenv('PAYFAST_MERCHANT_ID=' . PAYFAST_MERCHANT_ID);
if (PAYFAST_MERCHANT_KEY) putenv('PAYFAST_MERCHANT_KEY=' . PAYFAST_MERCHANT_KEY);
if (PAYFAST_PASSPHRASE) putenv('PAYFAST_PASSPHRASE=' . PAYFAST_PASSPHRASE);
putenv('PAYFAST_ENV=' . (getenv('PAYFAST_ENV') ?: 'development'));

if (OPENROUTER_API_KEY_RUNTIME) putenv('OPENROUTER_API_KEY=' . OPENROUTER_API_KEY_RUNTIME);
if (OPENROUTER_APP_URL_RUNTIME) putenv('OPENROUTER_APP_URL=' . OPENROUTER_APP_URL_RUNTIME);
if (OPENROUTER_GEMINI_MODEL_RUNTIME) putenv('OPENROUTER_GEMINI_MODEL=' . OPENROUTER_GEMINI_MODEL_RUNTIME);
if (OPENROUTER_MODEL_RUNTIME) putenv('OPENROUTER_MODEL=' . OPENROUTER_MODEL_RUNTIME);
?>