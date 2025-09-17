<?php
/**
 * PictureThis Diagnostic Script
 * Run this to identify configuration issues
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 PictureThis Diagnostic Report</h1>";
echo "<pre>";

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

// Check environment
$appEnv = getenv('APP_ENV') ?: 'development';
echo "APP_ENV: {$appEnv}\n\n";

// Check config file
$configFile = __DIR__ . "/config/{$appEnv}.php";
echo "Config File: {$configFile}\n";
if (file_exists($configFile)) {
    echo "✅ Config file exists\n";
    $config = require $configFile;
    echo "Config loaded successfully\n\n";
} else {
    echo "❌ Config file missing!\n\n";
    exit;
}

// Check database connection
echo "Database Configuration:\n";
$dbConfig = $config['database'] ?? [];
echo "- Host: " . ($dbConfig['host'] ?? 'NOT SET') . "\n";
echo "- User: " . ($dbConfig['user'] ?? 'NOT SET') . "\n";
echo "- Database: " . ($dbConfig['name'] ?? 'NOT SET') . "\n";
echo "- Password: " . (isset($dbConfig['pass']) ? (empty($dbConfig['pass']) ? 'EMPTY' : 'SET') : 'NOT SET') . "\n";

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Database connection successful\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Check required directories
$requiredDirs = ['uploads', 'logs', 'tmp', 'cache', 'config', 'src'];
echo "Directory Check:\n";
foreach ($requiredDirs as $dir) {
    $dirPath = __DIR__ . '/' . $dir;
    if (is_dir($dirPath)) {
        echo "✅ {$dir}/ exists\n";
        $writable = is_writable($dirPath);
        echo "   - Writable: " . ($writable ? '✅' : '❌') . "\n";
    } else {
        echo "❌ {$dir}/ missing\n";
    }
}
echo "\n";

// Check critical files
$criticalFiles = [
    'index.php',
    'config/config.php',
    'src/controllers/HomeController.php',
    'src/views/generate.php'
];
echo "Critical Files Check:\n";
foreach ($criticalFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo "✅ {$file} exists\n";
    } else {
        echo "❌ {$file} missing\n";
    }
}
echo "\n";

// Check API keys
echo "API Configuration:\n";
$payfast = $config['payfast'] ?? [];
echo "- PayFast Merchant ID: " . (isset($payfast['merchant_id']) ? (empty($payfast['merchant_id']) ? 'EMPTY' : 'SET') : 'NOT SET') . "\n";
echo "- PayFast Merchant Key: " . (isset($payfast['merchant_key']) ? (empty($payfast['merchant_key']) ? 'EMPTY' : 'SET') : 'NOT SET') . "\n";

$openrouter = $config['openrouter'] ?? [];
echo "- OpenRouter API Key: " . (isset($openrouter['api_key']) ? (empty($openrouter['api_key']) ? 'EMPTY' : 'SET') : 'NOT SET') . "\n";

echo "\n</pre>";
echo "<h2>📋 Next Steps</h2>";
echo "<ol>";
echo "<li>Fix any ❌ issues shown above</li>";
echo "<li>Ensure database credentials are correct</li>";
echo "<li>Set API keys in production config or .htaccess</li>";
echo "<li>Check file permissions (755 for dirs, 644 for files)</li>";
echo "<li>Verify .htaccess APP_ENV setting</li>";
echo "</ol>";
?>