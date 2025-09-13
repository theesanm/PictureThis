<?php
// Test file to verify environment detection
require_once __DIR__ . '/config/config.php';

echo "Environment Test Results:\n";
echo "APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NOT_DEFINED') . "\n";
echo "IS_PRODUCTION: " . (defined('IS_PRODUCTION') ? (IS_PRODUCTION ? 'true' : 'false') : 'NOT_DEFINED') . "\n";
echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT_DEFINED') . "\n";
echo "getenv('APP_ENV'): " . getenv('APP_ENV') . "\n";
echo "getenv('TEST_VAR'): " . getenv('TEST_VAR') . "\n";
?>