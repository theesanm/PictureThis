<?php
// Ultra simple config test
echo "<h1>Simple Config Test</h1>";
echo "<pre>";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

echo "Environment Variables:\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'NOT SET') . "\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'NOT SET') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? 'SET' : 'NOT SET') . "\n";

echo "\nFile Check:\n";
$files = ['index.php', '.htaccess', 'config/config.php'];
foreach ($files as $file) {
    echo "$file: " . (file_exists($file) ? 'EXISTS' : 'MISSING') . "\n";
}

echo "</pre>";
?>