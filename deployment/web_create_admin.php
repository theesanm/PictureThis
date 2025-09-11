<?php
/**
 * Web-Based Admin User Creation
 * Creates an admin user with full permissions
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Create Admin User - PictureThis</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".info { color: #007bff; background: #cce7ff; border: 1px solid #b3d7ff; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo "h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #1e7e34; }";
echo ".form-group { margin: 15px 0; }";
echo "label { display: block; margin-bottom: 5px; font-weight: bold; }";
echo "input[type='text'], input[type='email'], input[type='password'] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>👤 Create Admin User - PictureThis</h1>";

// Include config
require_once __DIR__ . '/config/config.php';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<div class='success'>✅ Connected to database successfully!</div>";

    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->execute(['admin@picturethis.app']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        echo "<div class='info'>";
        echo "<h2>ℹ️ Admin User Already Exists</h2>";
        echo "<p>An admin user with email 'admin@picturethis.app' already exists.</p>";
        echo "<p>You can proceed to the next step or reset the admin password if needed.</p>";
        echo "</div>";
    } else {
        // Create admin user
        $adminEmail = 'admin@picturethis.app';
        $adminPassword = 'Admin123!'; // Default password
        $adminName = 'PictureThis Admin';

        // Hash password
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);

        // Insert admin user
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, credits) VALUES (?, ?, ?, ?)");
        $stmt->execute([$adminName, $adminEmail, $passwordHash, 1000]);

        $userId = $pdo->lastInsertId();

        // Add admin permissions
        $permissions = ['admin', 'manage_users', 'view_reports', 'manage_settings'];
        foreach ($permissions as $permission) {
            $stmt = $pdo->prepare("INSERT INTO user_permissions (user_id, permission) VALUES (?, ?)");
            $stmt->execute([$userId, $permission]);
        }

        echo "<div class='success'>";
        echo "<h2>✅ Admin User Created Successfully!</h2>";
        echo "<p><strong>Email:</strong> $adminEmail</p>";
        echo "<p><strong>Password:</strong> $adminPassword</p>";
        echo "<p><strong>Credits:</strong> 1000</p>";
        echo "<p><strong>Permissions:</strong> " . implode(', ', $permissions) . "</p>";
        echo "<div class='info'>";
        echo "<strong>⚠️ Important:</strong> Please change the default password after first login!";
        echo "</div>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Admin User Creation Failed</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please ensure the database tables exist first by running the database setup.</p>";
    echo "</div>";
}

echo "<br>";
echo "<a href='web_setup.php' class='btn'>⬅️ Back to Setup</a>";
echo "<a href='web_setup_database.php' class='btn'>📊 Setup Database</a>";
echo "<a href='/' class='btn btn-success'>🏠 Go to Home Page</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
