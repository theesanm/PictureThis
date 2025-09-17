<?php
// Test different database connection methods

echo "<h1>Database Connection Test</h1>";

// Method 1: Using 127.0.0.1
echo "<h2>Method 1: 127.0.0.1</h2>";
try {
    $pdo1 = new PDO("mysql:host=127.0.0.1;dbname=cfoxcozj_PictureThis;charset=utf8mb4", "cfoxcozj_picThisdb", "LfUYHI%]{sjb5A*u");
    $pdo1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ 127.0.0.1 connection successful!</p>";

    $stmt1 = $pdo1->query("SELECT COUNT(*) as count FROM users");
    $result1 = $stmt1->fetch();
    echo "<p>Users found: " . $result1['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 127.0.0.1 failed: " . $e->getMessage() . "</p>";
}

// Method 2: Using localhost
echo "<h2>Method 2: localhost</h2>";
try {
    $pdo2 = new PDO("mysql:host=localhost;dbname=cfoxcozj_PictureThis;charset=utf8mb4", "cfoxcozj_picThisdb", "LfUYHI%]{sjb5A*u");
    $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ localhost connection successful!</p>";

    $stmt2 = $pdo2->query("SELECT COUNT(*) as count FROM users");
    $result2 = $stmt2->fetch();
    echo "<p>Users found: " . $result2['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ localhost failed: " . $e->getMessage() . "</p>";
}

// Method 3: Check PHP extensions
echo "<h2>Method 3: PHP Extensions</h2>";
echo "<p>PDO available: " . (extension_loaded('pdo') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>PDO MySQL available: " . (extension_loaded('pdo_mysql') ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>MySQLi available: " . (extension_loaded('mysqli') ? '✅ Yes' : '❌ No') . "</p>";

// Method 4: Check PHP info
echo "<h2>Method 4: PHP Info</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

echo "<p>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>