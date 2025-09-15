<?php
// Simple debug script to identify 500 error causes
echo "<h1>🔍 Debug - 500 Error Investigation</h1>";
echo "<pre>";

// Check PHP version
echo "🐘 PHP Version: " . php_version() . "\n";
echo "📍 Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "📁 Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "🔗 Current URL: " . $_SERVER['REQUEST_URI'] . "\n\n";

// Check file existence
$files_to_check = [
    'config/config.php',
    'src/lib/db.php',
    'index.php'
];

echo "📋 File Existence Check:\n";
foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ $file exists\n";
        if (is_readable($full_path)) {
            echo "   📖 Readable: Yes\n";
        } else {
            echo "   📖 Readable: No (Permission Issue!)\n";
        }
    } else {
        echo "❌ $file MISSING\n";
    }
}

echo "\n";

// Check config file
$config_file = __DIR__ . '/config/config.php';
if (file_exists($config_file)) {
    echo "🔧 Testing Config File:\n";
    $config_content = file_get_contents($config_file);
    if ($config_content !== false) {
        if (strpos($config_content, 'IS_PRODUCTION') !== false) {
            echo "✅ IS_PRODUCTION found in config\n";
        } else {
            echo "❌ IS_PRODUCTION not found in config\n";
        }
    } else {
        echo "❌ Cannot read config file\n";
    }
} else {
    echo "❌ Config file does not exist\n";
}

echo "\n";

// Test basic PHP functionality
echo "🧪 Testing PHP Functions:\n";
try {
    $test = "Hello World";
    echo "✅ String operations: OK\n";
} catch (Exception $e) {
    echo "❌ String operations failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Check error reporting
echo "⚙️  Error Reporting: " . (error_reporting() ? 'ON' : 'OFF') . "\n";
echo "📝 Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "\n";

echo "\n========================================\n";
echo "🔍 Debug completed at " . date('Y-m-d H:i:s') . "\n";
?>