<?php
// Configuration Test Script
// Run this to verify your environment configuration is working correctly

require_once __DIR__ . '/config/config.php';

echo "=== Environment Configuration Test ===\n\n";

echo "Environment: " . APP_ENV . "\n";
echo "Is Production: " . (IS_PRODUCTION ? 'Yes' : 'No') . "\n";
echo "Debug Mode: " . (APP_DEBUG ? 'Enabled' : 'Disabled') . "\n\n";

echo "=== Database Configuration ===\n";
echo "Host: " . DB_HOST . "\n";
echo "User: " . DB_USER . "\n";
echo "Database: " . DB_NAME . "\n";
echo "Password: " . (DB_PASS ? '[SET]' : '[NOT SET]') . "\n\n";

echo "=== Application Settings ===\n";
echo "Name: " . APP_NAME . "\n";
echo "URL: " . APP_URL . "\n\n";

echo "=== PayFast Configuration ===\n";
echo "Merchant ID: " . (PAYFAST_MERCHANT_ID ? '[SET]' : '[NOT SET]') . "\n";
echo "Merchant Key: " . (PAYFAST_MERCHANT_KEY ? '[SET]' : '[NOT SET]') . "\n";
echo "Passphrase: " . (PAYFAST_PASSPHRASE ? '[SET]' : '[NOT SET]') . "\n";
echo "Environment: " . PAYFAST_ENV . "\n\n";

echo "=== OpenRouter Configuration ===\n";
echo "API Key: " . (OPENROUTER_API_KEY ? '[SET]' : '[NOT SET]') . "\n";
echo "App URL: " . OPENROUTER_APP_URL . "\n";
echo "Gemini Model: " . OPENROUTER_GEMINI_MODEL . "\n\n";

echo "=== Email Configuration ===\n";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Username: " . SMTP_USERNAME . "\n";
echo "SMTP Password: " . (SMTP_PASSWORD ? '[SET]' : '[NOT SET]') . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "From Email: " . FROM_EMAIL . "\n\n";

echo "=== Image Configuration ===\n";
echo "Retention Days: " . IMAGE_RETENTION_DAYS . "\n";
echo "Min Images Per User: " . MIN_IMAGES_PER_USER . "\n\n";

echo "=== Configuration Sources ===\n";
echo "Config File: config/" . APP_ENV . ".php\n";
echo ".env File: " . (file_exists(__DIR__ . '/.env') ? 'Present' : 'Not present') . "\n";
echo "APP_ENV from .htaccess: " . getenv('APP_ENV') . "\n\n";

echo "Test completed successfully!\n";
?>