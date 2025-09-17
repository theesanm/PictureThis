<?php
/**
 * Environment Configuration Test
 * This script tests if your environment variables are loaded correctly
 */

require_once __DIR__ . '/config/config.php';

echo "🔍 PictureThis Environment Configuration Test\n";
echo "=============================================\n\n";

$tests = [
    'Database' => [
        'DB_HOST' => getConfigValue('database', 'host'),
        'DB_USER' => getConfigValue('database', 'user'),
        'DB_PASS' => getConfigValue('database', 'pass') ? '***SET***' : 'NOT SET',
        'DB_NAME' => getConfigValue('database', 'name'),
    ],
    'PayFast' => [
        'MERCHANT_ID' => getConfigValue('payfast', 'merchant_id'),
        'MERCHANT_KEY' => getConfigValue('payfast', 'merchant_key') ? '***SET***' : 'NOT SET',
        'PASSPHRASE' => getConfigValue('payfast', 'passphrase') ? '***SET***' : 'NOT SET',
    ],
    'OpenRouter' => [
        'API_KEY' => getConfigValue('openrouter', 'api_key') ? '***SET***' : 'NOT SET',
    ],
    'Email/SMTP' => [
        'SMTP_HOST' => getConfigValue('email', 'smtp_host'),
        'SMTP_USER' => getConfigValue('email', 'smtp_username'),
        'SMTP_PASS' => getConfigValue('email', 'smtp_password') ? '***SET***' : 'NOT SET',
    ],
];

$allGood = true;

foreach ($tests as $section => $values) {
    echo "📋 $section Configuration:\n";
    foreach ($values as $key => $value) {
        $status = ($value === 'NOT SET' || $value === null) ? '❌' : '✅';
        if ($value === 'NOT SET' || $value === null) {
            $allGood = false;
        }
        echo "   $key: $status $value\n";
    }
    echo "\n";
}

echo "🎯 Test Results:\n";
if ($allGood) {
    echo "✅ All configuration values are set correctly!\n";
    echo "✅ Your environment is ready for deployment.\n";
} else {
    echo "⚠️  Some configuration values are missing.\n";
    echo "⚠️  Please check your .env file or cPanel environment variables.\n";
}

echo "\n🔧 Current Environment: " . (IS_PRODUCTION ? 'Production' : 'Development') . "\n";
echo "📁 Config File: " . (IS_PRODUCTION ? 'production.php' : 'development.php') . "\n";

if (file_exists(__DIR__ . '/.env')) {
    echo "📄 .env file: Found\n";
} else {
    echo "📄 .env file: Not found (using defaults)\n";
}

echo "\n💡 Tips:\n";
echo "- For production, ensure .env is outside web root\n";
echo "- Use cPanel environment variables for maximum security\n";
echo "- Test database connection after configuration\n";
?>