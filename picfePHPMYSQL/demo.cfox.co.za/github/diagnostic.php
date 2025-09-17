<?php
/**
 * 404 Diagnostic Script
 * Run this to diagnose why you're getting 404 errors
 */

echo "<h1>404 Diagnostic - demo.cfox.co.za</h1>";
echo "<pre>";

// Check current directory
echo "📁 Current Directory: " . __DIR__ . "\n";
echo "🌐 Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "🔗 Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "📄 Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n\n";

// Check if essential files exist
$essentialFiles = [
    'index.php',
    'config/config.php',
    'src/views/home.php',
    'src/controllers/HomeController.php',
    '.htaccess'
];

echo "📋 Essential Files Check:\n";
foreach ($essentialFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "✅ $file exists\n";
        if (is_readable($fullPath)) {
            echo "   📖 Readable: Yes\n";
        } else {
            echo "   📖 Readable: No (Permission Issue!)\n";
        }
    } else {
        echo "❌ $file MISSING\n";
    }
}

echo "\n🔍 Directory Contents:\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $type = is_dir(__DIR__ . '/' . $file) ? '[DIR]' : '[FILE]';
        echo "   $type $file\n";
    }
}

echo "\n🌐 Server Information:\n";
echo "   Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
echo "   Server Port: " . $_SERVER['SERVER_PORT'] . "\n";

echo "\n💡 Troubleshooting Tips:\n";
echo "1. Ensure subdomain points to: " . __DIR__ . "\n";
echo "2. Check file permissions: index.php should be 644\n";
echo "3. Verify .htaccess is uploaded and correct\n";
echo "4. Make sure index.php is in the root directory\n";
echo "5. Check cPanel error logs for more details\n";

echo "\n🔧 Quick Fix Commands:\n";
echo "# Set correct permissions:\n";
echo "chmod 644 index.php\n";
echo "chmod 644 .htaccess\n";
echo "chmod 755 src/\n";
echo "chmod 755 config/\n";

echo "</pre>";
echo "<style>body{font-family:monospace;background:#f5f5f5;padding:20px;}h1{color:#333;margin-bottom:20px;}</style>";
?>
