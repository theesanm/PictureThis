<?php
// Simple diagnostic - no config dependencies
echo "<h1>🔧 Simple PictureThis Diagnostic</h1>";
echo "<pre>";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Directory: " . __DIR__ . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

echo "File Check:\n";
$files = ['index.php', 'config/config.php', '.htaccess'];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "- $file: " . (file_exists($path) ? "✅ EXISTS" : "❌ MISSING") . "\n";
}

echo "\nDirectory Check:\n";
$dirs = ['config', 'src', 'uploads', 'logs'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    echo "- $dir/: " . (is_dir($path) ? "✅ EXISTS" : "❌ MISSING");
    if (is_dir($path)) {
        echo " (" . (is_writable($path) ? "WRITABLE" : "NOT WRITABLE") . ")";
    }
    echo "\n";
}

echo "\nEnvironment Variables:\n";
$env_vars = ['APP_ENV', 'DB_HOST', 'DB_USER', 'DB_NAME'];
foreach ($env_vars as $var) {
    $value = getenv($var);
    echo "- $var: " . ($value ? $value : "NOT SET") . "\n";
}

echo "\n</pre>";
echo "<p><strong>Next:</strong> If files exist, check <a href='test.php'>test.php</a> for PHP info</p>";
?>