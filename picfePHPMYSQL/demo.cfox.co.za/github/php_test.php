<?php
// Simple test to check if PHP is working and content is loading
echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>PHP Test</title>";
echo "<style>body { background: yellow; color: black; padding: 20px; }</style>";
echo "</head>";
echo "<body>";
echo "<h1 style='color: red;'>PHP is working!</h1>";
echo "<p>If you can see this, PHP is executing properly.</p>";

// Test database
try {
    require_once __DIR__ . '/src/lib/db.php';
    $pdo = get_db();
    echo "<p style='color: green;'>✅ Database connection works!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test file includes
echo "<h2>Testing File Includes:</h2>";
if (file_exists(__DIR__ . '/src/views/header.php')) {
    echo "<p style='color: green;'>✅ Header file exists</p>";
} else {
    echo "<p style='color: red;'>❌ Header file missing</p>";
}

if (file_exists(__DIR__ . '/src/views/home.php')) {
    echo "<p style='color: green;'>✅ Home file exists</p>";
} else {
    echo "<p style='color: red;'>❌ Home file missing</p>";
}

if (file_exists(__DIR__ . '/src/views/footer.php')) {
    echo "<p style='color: green;'>✅ Footer file exists</p>";
} else {
    echo "<p style='color: red;'>❌ Footer file missing</p>";
}

// Test HomeController
echo "<h2>Testing HomeController:</h2>";
try {
    require_once __DIR__ . '/src/controllers/HomeController.php';
    $controller = new HomeController();
    echo "<p style='color: green;'>✅ HomeController loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ HomeController error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Current Directory:</h2>";
echo "<p>" . __DIR__ . "</p>";

echo "<h2>Request Info:</h2>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "</p>";

echo "</body>";
echo "</html>";
?>
