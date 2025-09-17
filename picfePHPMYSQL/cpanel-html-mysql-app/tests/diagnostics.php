<?php
/**
 * PictureThis Simple Diagnostics
 * Basic health check for the application
 */

// Simple error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>PictureThis - Simple Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .test { margin: 10px 0; padding: 10px; border-radius: 4px; border-left: 4px solid; }
        .pass { background: #d4edda; border-left-color: #28a745; }
        .fail { background: #f8d7da; border-left-color: #dc3545; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; }
        .summary { display: flex; justify-content: space-around; margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .metric { text-align: center; }
        .metric h3 { margin: 0; font-size: 2em; }
        .metric p { margin: 5px 0; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔍 PictureThis Simple Diagnostics</h1>
            <p>Basic health check - " . date('Y-m-d H:i:s') . "</p>
        </div>";

$results = [];
$passed = 0;
$failed = 0;

function addResult($test, $status, $message, $details = '') {
    global $results, $passed, $failed;
    $results[] = ['test' => $test, 'status' => $status, 'message' => $message, 'details' => $details];
    if ($status === 'PASS') $passed++;
    else $failed++;
}

// Test 1: PHP Version
$phpVersion = PHP_VERSION;
$minVersion = '8.0.0';
if (version_compare($phpVersion, $minVersion, '>=')) {
    addResult('PHP Version', 'PASS', "PHP $phpVersion (meets minimum $minVersion)");
} else {
    addResult('PHP Version', 'FAIL', "PHP $phpVersion (requires $minVersion+)");
}

// Test 2: Config File
if (file_exists('../config/config.php')) {
    addResult('Config File', 'PASS', 'config.php exists');
} else {
    addResult('Config File', 'FAIL', 'config.php not found');
}

// Test 3: Config Loading
try {
    if (file_exists('../config/config.php')) {
        require_once '../config/config.php';
        addResult('Config Loading', 'PASS', 'Configuration loaded successfully');
    } else {
        addResult('Config Loading', 'FAIL', 'Config file not found');
    }
} catch (Exception $e) {
    addResult('Config Loading', 'FAIL', 'Failed to load configuration: ' . $e->getMessage());
}

// Test 4: Database Connection (if config loaded)
if (isset($db) || function_exists('get_db')) {
    try {
        require_once '../src/lib/db.php';
        $db_connection = get_db();
        if ($db_connection) {
            addResult('Database Connection', 'PASS', 'Connected to database successfully');
        } else {
            addResult('Database Connection', 'FAIL', 'Failed to connect to database');
        }
    } catch (Exception $e) {
        addResult('Database Connection', 'FAIL', 'Database error: ' . $e->getMessage());
    }
} else {
    addResult('Database Connection', 'INFO', 'Database test skipped - config not loaded');
}

// Test 5: Required Files
$requiredFiles = [
    '../index.php' => 'Main application file',
    '../src/lib/db.php' => 'Database library',
    '../config/production.php' => 'Production config'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        addResult("File: $description", 'PASS', basename($file) . ' exists');
    } else {
        addResult("File: $description", 'FAIL', basename($file) . ' missing');
    }
}

// Test 6: Environment Mode
if (defined('IS_PRODUCTION')) {
    $mode = IS_PRODUCTION ? 'Production' : 'Development';
    addResult('Environment Mode', 'INFO', "Running in $mode mode");
} else {
    addResult('Environment Mode', 'INFO', 'Environment mode not set');
}

// Display results
echo "<div class='summary'>";
echo "<div class='metric'><h3 style='color: #28a745'>$passed</h3><p>Tests Passed</p></div>";
echo "<div class='metric'><h3 style='color: #dc3545'>$failed</h3><p>Tests Failed</p></div>";
echo "<div class='metric'><h3>" . ($passed + $failed) . "</h3><p>Total Tests</p></div>";
echo "</div>";

echo "<h3>Test Results:</h3>";
foreach ($results as $result) {
    $cssClass = strtolower($result['status']);
    $icon = $result['status'] === 'PASS' ? '✅' : ($result['status'] === 'FAIL' ? '❌' : 'ℹ️');

    echo "<div class='test $cssClass'>";
    echo "<strong>$icon {$result['test']}</strong>: {$result['message']}";
    if (!empty($result['details'])) {
        echo "<div style='font-size: 0.9em; color: #666; margin-top: 5px;'>{$result['details']}</div>";
    }
    echo "</div>";
}

echo "<hr><h3>System Information:</h3>";
echo "<ul>";
echo "<li><strong>Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Current Directory:</strong> " . __DIR__ . "</li>";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</li>";
echo "<li><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</li>";
echo "<li><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " seconds</li>";
echo "</ul>";

echo "</div></body></html>";
?>