<?php
/**
 * Debug Page - Test basic functionality
 */

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Debug - PictureThis</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; color: #333; }";
echo ".success { color: green; }";
echo ".error { color: red; }";
echo ".info { color: blue; }";
echo "h1 { color: #333; }";
echo "h2 { color: #555; margin-top: 30px; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<h1>🔧 PictureThis Debug Page</h1>";

// Test PHP
echo "<h2>PHP Test</h2>";
echo "<p class='success'>✅ PHP is working! Version: " . phpversion() . "</p>";

// Test config
echo "<h2>Configuration Test</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    echo "<p class='success'>✅ Config loaded successfully</p>";
    echo "<p><strong>App Name:</strong> " . APP_NAME . "</p>";
    echo "<p><strong>App URL:</strong> " . APP_URL . "</p>";
    echo "<p><strong>DB Host:</strong> " . DB_HOST . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Config error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test database
echo "<h2>Database Test</h2>";
try {
    require_once __DIR__ . '/src/lib/db.php';
    $pdo = get_db();
    echo "<p class='success'>✅ Database connected successfully</p>";

    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p><strong>Users in database:</strong> " . $result['count'] . "</p>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test file includes
echo "<h2>File Include Test</h2>";
$testFiles = [
    'src/controllers/HomeController.php',
    'src/views/header.php',
    'src/views/home.php',
    'src/views/footer.php'
];

foreach ($testFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p class='success'>✅ $file exists</p>";
    } else {
        echo "<p class='error'>❌ $file missing</p>";
    }
}

// Test home controller
echo "<h2>Home Controller Test</h2>";
try {
    require_once __DIR__ . '/src/controllers/HomeController.php';
    echo "<p class='success'>✅ HomeController loaded successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ HomeController error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<br><br>";
echo "<a href='/'>← Back to Home</a>";
echo " | ";
echo "<a href='web_setup.php'>Setup Page</a>";

echo "</body>";
echo "</html>";
?>
