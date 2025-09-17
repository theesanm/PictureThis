<?php
/**
 * Server Configuration Test
 * Tests various server and PHP configurations
 */

header('Content-Type: text/plain');

echo "=== SERVER CONFIGURATION TEST ===\n\n";

// Test 1: Basic PHP functionality
echo "1. PHP Version: " . PHP_VERSION . "\n";
echo "2. PHP SAPI: " . PHP_SAPI . "\n";
echo "3. Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "4. Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "5. Current Directory: " . __DIR__ . "\n";
echo "6. Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n\n";

// Test 2: File permissions
echo "=== FILE PERMISSIONS TEST ===\n";
$testFile = __DIR__ . '/test_write.txt';
$writeTest = @file_put_contents($testFile, 'test');
if ($writeTest !== false) {
    echo "✓ Can write to tests directory\n";
    unlink($testFile);
} else {
    echo "✗ Cannot write to tests directory\n";
}

// Test 3: Required extensions
echo "\n=== PHP EXTENSIONS TEST ===\n";
$required = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
foreach ($required as $ext) {
    echo (extension_loaded($ext) ? "✓" : "✗") . " $ext\n";
}

// Test 4: Memory and limits
echo "\n=== PHP LIMITS TEST ===\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";

// Test 5: Error reporting
echo "\n=== ERROR REPORTING TEST ===\n";
echo "Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "\n";
echo "Error Reporting: " . ini_get('error_reporting') . "\n";

echo "\n=== TEST COMPLETE ===";
?>