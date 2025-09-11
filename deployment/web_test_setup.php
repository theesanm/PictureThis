<?php
/**
 * Web-Based Test Setup
 * Tests database connection and basic functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Test Setup - PictureThis</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".info { color: #007bff; background: #cce7ff; border: 1px solid #b3d7ff; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".warning { color: #856404; background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo "h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #1e7e34; }";
echo ".test-result { margin: 10px 0; padding: 10px; border-radius: 4px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background-color: #f8f9fa; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🧪 Test Setup - PictureThis</h1>";

// Include config
require_once __DIR__ . '/config/config.php';

$testResults = [];
$allTestsPassed = true;

echo "<h2>Running System Tests...</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Test query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $testResults['db_connection'] = [
        'status' => 'success',
        'message' => 'Database connection successful',
        'details' => 'Connected to ' . DB_HOST . '/' . DB_NAME
    ];
} catch (PDOException $e) {
    $testResults['db_connection'] = [
        'status' => 'error',
        'message' => 'Database connection failed',
        'details' => $e->getMessage()
    ];
    $allTestsPassed = false;
}

// Test 2: Required Tables
echo "<h3>2. Database Tables Test</h3>";
$requiredTables = ['users', 'images', 'credit_transactions', 'api_usage_logs', 'settings', 'user_permissions'];
$existingTables = [];

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            $existingTables[] = $table;
        }
    }

    if (count($existingTables) === count($requiredTables)) {
        $testResults['db_tables'] = [
            'status' => 'success',
            'message' => 'All required tables exist',
            'details' => 'Found ' . count($existingTables) . ' of ' . count($requiredTables) . ' tables'
        ];
    } else {
        $missingTables = array_diff($requiredTables, $existingTables);
        $testResults['db_tables'] = [
            'status' => 'warning',
            'message' => 'Some tables are missing',
            'details' => 'Missing: ' . implode(', ', $missingTables)
        ];
        $allTestsPassed = false;
    }
} catch (PDOException $e) {
    $testResults['db_tables'] = [
        'status' => 'error',
        'message' => 'Could not check tables',
        'details' => $e->getMessage()
    ];
    $allTestsPassed = false;
}

// Test 3: File Permissions
echo "<h3>3. File Permissions Test</h3>";
$permissionTests = [
    'uploads/' => ['path' => 'uploads/', 'required' => 0755],
    'tmp/' => ['path' => 'tmp/', 'required' => 0755],
    'config/config.php' => ['path' => 'config/config.php', 'required' => 0644],
    '.htaccess' => ['path' => '.htaccess', 'required' => 0644]
];

$permissionResults = [];
$permissionsOk = true;

foreach ($permissionTests as $name => $test) {
    if (file_exists($test['path'])) {
        $currentPerms = fileperms($test['path']) & 0777;
        $requiredPerms = $test['required'];

        if ($currentPerms === $requiredPerms) {
            $permissionResults[$name] = [
                'status' => 'success',
                'message' => sprintf('%04o', $currentPerms) . ' (correct)'
            ];
        } else {
            $permissionResults[$name] = [
                'status' => 'warning',
                'message' => sprintf('%04o', $currentPerms) . ' (should be ' . sprintf('%04o', $requiredPerms) . ')'
            ];
            $permissionsOk = false;
        }
    } else {
        $permissionResults[$name] = [
            'status' => 'error',
            'message' => 'File/directory does not exist'
        ];
        $permissionsOk = false;
    }
}

$testResults['file_permissions'] = [
    'status' => $permissionsOk ? 'success' : 'warning',
    'message' => $permissionsOk ? 'All permissions correct' : 'Some permissions need fixing',
    'details' => $permissionResults
];

// Test 4: PHP Extensions
echo "<h3>4. PHP Extensions Test</h3>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
$extensionResults = [];
$extensionsOk = true;

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $extensionResults[$ext] = [
            'status' => 'success',
            'message' => 'Loaded'
        ];
    } else {
        $extensionResults[$ext] = [
            'status' => 'error',
            'message' => 'Missing'
        ];
        $extensionsOk = false;
    }
}

$testResults['php_extensions'] = [
    'status' => $extensionsOk ? 'success' : 'error',
    'message' => $extensionsOk ? 'All required extensions loaded' : 'Some extensions missing',
    'details' => $extensionResults
];

if (!$extensionsOk) {
    $allTestsPassed = false;
}

// Test 5: Admin User
echo "<h3>5. Admin User Test</h3>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->execute(['admin@picturethis.app']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        $testResults['admin_user'] = [
            'status' => 'success',
            'message' => 'Admin user exists',
            'details' => 'admin@picturethis.app found in database'
        ];
    } else {
        $testResults['admin_user'] = [
            'status' => 'warning',
            'message' => 'Admin user not found',
            'details' => 'Run admin user creation script'
        ];
        $allTestsPassed = false;
    }
} catch (PDOException $e) {
    $testResults['admin_user'] = [
        'status' => 'error',
        'message' => 'Could not check admin user',
        'details' => $e->getMessage()
    ];
    $allTestsPassed = false;
}

// Display results
echo "<h2>Test Results Summary</h2>";
echo "<table>";
echo "<tr><th>Test</th><th>Status</th><th>Details</th></tr>";

foreach ($testResults as $testName => $result) {
    $statusClass = $result['status'];
    $statusIcon = '';
    switch ($result['status']) {
        case 'success': $statusIcon = '✅'; break;
        case 'warning': $statusIcon = '⚠️'; break;
        case 'error': $statusIcon = '❌'; break;
    }

    echo "<tr>";
    echo "<td>" . ucwords(str_replace('_', ' ', $testName)) . "</td>";
    echo "<td class='$statusClass'>$statusIcon " . ucfirst($result['status']) . "</td>";
    echo "<td>" . $result['message'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Detailed results
echo "<h2>Detailed Test Results</h2>";

foreach ($testResults as $testName => $result) {
    echo "<div class='test-result " . $result['status'] . "'>";
    echo "<h3>" . ucwords(str_replace('_', ' ', $testName)) . "</h3>";
    echo "<p><strong>Status:</strong> " . ucfirst($result['status']) . "</p>";
    echo "<p><strong>Message:</strong> " . $result['message'] . "</p>";

    if (isset($result['details'])) {
        if (is_array($result['details'])) {
            echo "<ul>";
            foreach ($result['details'] as $key => $detail) {
                $detailStatus = isset($detail['status']) ? $detail['status'] : 'info';
                echo "<li class='$detailStatus'>$key: " . (isset($detail['message']) ? $detail['message'] : $detail) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p><strong>Details:</strong> " . $result['details'] . "</p>";
        }
    }
    echo "</div>";
}

// Final status
if ($allTestsPassed) {
    echo "<div class='success'>";
    echo "<h2>🎉 All Tests Passed!</h2>";
    echo "<p>Your PictureThis installation is ready to use.</p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h2>⚠️ Some Tests Failed</h2>";
    echo "<p>Please address the issues above before proceeding.</p>";
    echo "</div>";
}

echo "<br>";
echo "<a href='web_setup.php' class='btn'>⬅️ Back to Setup</a>";
echo "<a href='/' class='btn btn-success'>🏠 Go to Home Page</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
