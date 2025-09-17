<?php
// Simple deployment verification script with error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚀 Simple Deployment Check</h1>";
echo "<pre>";

// Step 1: Check basic file structure
echo "📁 Step 1: File Structure Check\n";
$files = [
    'index.php',
    'config/config.php',
    'config/production.php',
    'src/lib/db.php'
];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $file exists\n";
    } else {
        echo "❌ $file missing\n";
    }
}

echo "\n";

// Step 2: Test config loading
echo "🔧 Step 2: Config Loading Test\n";
try {
    require_once __DIR__ . '/config/config.php';
    echo "✅ Config loaded successfully\n";
    echo "   IS_PRODUCTION: " . (defined('IS_PRODUCTION') ? (IS_PRODUCTION ? 'true' : 'false') : 'undefined') . "\n";
    echo "   APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'undefined') . "\n";
    echo "   DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'undefined') . "\n";
} catch (Exception $e) {
    echo "❌ Config loading failed: " . $e->getMessage() . "\n";
    echo "   Error on line: " . $e->getLine() . "\n";
    echo "   File: " . $e->getFile() . "\n";
}

echo "\n";

// Step 3: Test database connection (optional)
echo "🗄️  Step 3: Database Connection Test\n";
if (defined('DB_HOST') && defined('DB_USER')) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✅ Database connection successful\n";
    } catch (PDOException $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️  Database constants not defined, skipping DB test\n";
}

echo "\n========================================\n";
echo "✅ Simple deployment check completed\n";
?>