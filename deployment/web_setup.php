<?php
/**
 * Web-Based Database Setup
 * Run this via browser: https://demo.cfox.co.za/web_setup.php
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>PictureThis - Web Setup</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".info { color: #007bff; background: #cce7ff; border: 1px solid #b3d7ff; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo "h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }";
echo "h2 { color: #555; margin-top: 30px; }";
echo ".step { background: #e9ecef; padding: 15px; border-radius: 4px; margin: 10px 0; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #1e7e34; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🚀 PictureThis Web Setup</h1>";
echo "<p class='info'>This setup will configure your PictureThis application for production use.</p>";

// Check if config file exists
$configPath = __DIR__ . '/config/config.php';
if (!file_exists($configPath)) {
    echo "<div class='error'>❌ Configuration file not found at: $configPath</div>";
    echo "<p>Please ensure the config.php file exists in the config directory.</p>";
    echo "</div></body></html>";
    exit;
}

echo "<div class='step'>";
echo "<h2>📋 Setup Steps</h2>";
echo "<ol>";
echo "<li>✅ <strong>Files Uploaded</strong> - Deployment package extracted</li>";
echo "<li>🔄 <strong>Database Setup</strong> - Create tables and structure</li>";
echo "<li>🔄 <strong>Admin User</strong> - Create administrator account</li>";
echo "<li>🔄 <strong>Permissions</strong> - Set proper file permissions</li>";
echo "<li>🔄 <strong>Test Installation</strong> - Verify everything works</li>";
echo "</ol>";
echo "</div>";

// Include config to get database settings
require_once $configPath;

echo "<div class='step'>";
echo "<h2>🔧 Database Configuration</h2>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr><td><strong>Host:</strong></td><td>" . DB_HOST . "</td></tr>";
echo "<tr><td><strong>Database:</strong></td><td>" . DB_NAME . "</td></tr>";
echo "<tr><td><strong>User:</strong></td><td>" . DB_USER . "</td></tr>";
echo "<tr><td><strong>App URL:</strong></td><td>" . APP_URL . "</td></tr>";
echo "</table>";
echo "</div>";

// Test database connection
echo "<div class='step'>";
echo "<h2>🧪 Database Connection Test</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ Database connection successful!</div>";

    // Check existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Existing Tables:</h3>";
    if (empty($tables)) {
        echo "<p class='info'>No tables found. Database setup needed.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }

} catch (PDOException $e) {
    echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p>Please check your database credentials in config/config.php</p>";
}
echo "</div>";

// Setup actions
echo "<div class='step'>";
echo "<h2>⚙️ Setup Actions</h2>";

echo "<a href='web_setup_database.php' class='btn'>📊 Setup Database Tables</a>";
echo "<a href='web_create_admin.php' class='btn'>👤 Create Admin User</a>";
echo "<a href='web_setup_permissions.php' class='btn'>🔐 Setup Permissions</a>";
echo "<a href='web_test_setup.php' class='btn btn-success'>🧪 Test Installation</a>";
echo "<br><br>";
echo "<a href='/' class='btn'>🏠 Go to Home Page</a>";
echo "<a href='login' class='btn'>🔐 Go to Login</a>";

echo "</div>";

echo "<div class='step'>";
echo "<h2>📝 Manual Setup (if needed)</h2>";
echo "<p>If the automated setup doesn't work, you can run these files manually:</p>";
echo "<ul>";
echo "<li><a href='web_setup_database.php'>web_setup_database.php</a> - Create database tables</li>";
echo "<li><a href='web_create_admin.php'>web_create_admin.php</a> - Create admin user</li>";
echo "<li><a href='web_setup_permissions.php'>web_setup_permissions.php</a> - Setup permissions</li>";
echo "<li><a href='web_test_setup.php'>web_test_setup.php</a> - Test installation</li>";
echo "</ul>";
echo "</div>";

echo "<div class='step'>";
echo "<h2>🔍 Troubleshooting</h2>";
echo "<ul>";
echo "<li>Check file permissions (755 for directories, 644 for files)</li>";
echo "<li>Ensure config/config.php has correct database credentials</li>";
echo "<li>Verify .htaccess file exists and is properly configured</li>";
echo "<li>Check PHP version (7.4+ required)</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
