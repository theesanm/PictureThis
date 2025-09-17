<?php
// Database Connection Test for Development Environment
// Test different database connection methods

echo "<h1>Database Connection Test - Development</h1>";
echo "<p>Testing root user connections to Docker MySQL</p>";

// Method 1: Your Development User Connection
echo "<h2>Method 1: Development User (pt_user@127.0.0.1:3306)</h2>";
try {
    $pdo1 = new PDO("mysql:host=127.0.0.1;port=3306;dbname=picturethis_dev;charset=utf8mb4", "pt_user", "pt_pass");
    $pdo1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Development user connection successful!</p>";

    $stmt1 = $pdo1->query("SELECT COUNT(*) as count FROM users");
    $result1 = $stmt1->fetch();
    echo "<p>Users found: " . $result1['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Development user failed: " . $e->getMessage() . "</p>";
    echo "<p><small>Note: Create pt_user in Docker MySQL or use root user</small></p>";
}

// Method 2: Root User (Docker default)
echo "<h2>Method 2: Root User (root@127.0.0.1:3306)</h2>";
try {
    $pdo2 = new PDO("mysql:host=127.0.0.1;port=3306;dbname=picturethis_dev;charset=utf8mb4", "root", "");
    $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Root user connection successful!</p>";

    $stmt2 = $pdo2->query("SELECT COUNT(*) as count FROM users");
    $result2 = $stmt2->fetch();
    echo "<p>Users found: " . $result2['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Root user failed: " . $e->getMessage() . "</p>";
}

// Method 3: Production User (fallback test)
echo "<h2>Method 3: Production User (cfoxcozj_picThisdb@127.0.0.1:3306)</h2>";
try {
    $pdo3 = new PDO("mysql:host=127.0.0.1;port=3306;dbname=picturethis_dev;charset=utf8mb4", "cfoxcozj_picThisdb", "LfUYHI%]{sjb5A*u");
    $pdo3->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Production user connection successful!</p>";

    $stmt3 = $pdo3->query("SELECT COUNT(*) as count FROM users");
    $result3 = $stmt3->fetch();
    echo "<p>Users found: " . $result3['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Production user failed: " . $e->getMessage() . "</p>";
}

// Method 4: PHP Extensions Check
echo "<h2>Method 4: PHP Extensions</h2>";
echo "<p>PDO available: " . (extension_loaded('pdo') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>PDO MySQL available: " . (extension_loaded('pdo_mysql') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>MySQLi available: " . (extension_loaded('mysqli') ? '✅ Yes' : '❌ No') . "</p>";

// Method 5: PHP Info
echo "<h2>Method 5: PHP Info</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

echo "<p>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>

// Method 4: PHP Extensions Check
echo "<h2>Method 4: PHP Extensions</h2>";
echo "<p>PDO available: " . (extension_loaded('pdo') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>PDO MySQL available: " . (extension_loaded('pdo_mysql') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>MySQLi available: " . (extension_loaded('mysqli') ? '✅ Yes' : '❌ No') . "</p>";

// Method 5: PHP Info
echo "<h2>Method 5: PHP Info</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

echo "<p>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>