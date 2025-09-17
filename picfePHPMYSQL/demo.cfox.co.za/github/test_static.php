<?php
// Simple test to verify static file serving
echo "<h1>Static File Test</h1>";
echo "<p>If you can see this, PHP is working.</p>";

// Test if we can access the CSS file
$cssPath = __DIR__ . '/public/css/style.css';
if (file_exists($cssPath)) {
    echo "<p style='color: green;'>✅ CSS file exists at: $cssPath</p>";
    echo "<p style='color: green;'>✅ CSS file size: " . filesize($cssPath) . " bytes</p>";
    echo "<p style='color: green;'>✅ CSS file readable: " . (is_readable($cssPath) ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p style='color: red;'>❌ CSS file NOT found at: $cssPath</p>";
}

// Test directory permissions
$publicDir = __DIR__ . '/public';
$cssDir = __DIR__ . '/public/css';

echo "<h2>Directory Permissions:</h2>";
echo "<p>Public directory: " . (is_readable($publicDir) ? '✅ Readable' : '❌ Not readable') . "</p>";
echo "<p>CSS directory: " . (is_readable($cssDir) ? '✅ Readable' : '❌ Not readable') . "</p>";

// Show current directory structure
echo "<h2>Directory Structure:</h2>";
echo "<pre>";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $type = is_dir(__DIR__ . '/' . $file) ? '[DIR]' : '[FILE]';
        echo "$type $file\n";
    }
}
echo "</pre>";

// Test direct CSS access
echo "<h2>Test Direct CSS Access:</h2>";
echo "<p><a href='/public/css/style.css' target='_blank'>Click here to test CSS file directly</a></p>";

// Show server information
echo "<h2>Server Information:</h2>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
?>
