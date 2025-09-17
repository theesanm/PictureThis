<?php
// Test environment variables
echo "<h1>Environment Variable Test</h1>";
echo "<pre>";

echo "All Environment Variables:\n";
foreach ($_ENV as $key => $value) {
    echo "$key = $value\n";
}

echo "\nSpecific Tests:\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'NOT SET') . "\n";
echo "TEST_VAR: " . (getenv('TEST_VAR') ?: 'NOT SET') . "\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "\n";

echo "\nServer Variables:\n";
echo "SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'NOT SET') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";

echo "</pre>";
?>