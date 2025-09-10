<?php
/**
 * Database Connection Test Script
 * Run this to verify your database connection is working
 */

// Database configuration
$host = 'localhost:3306';
$dbname = 'cfoxcozj_PictureThis';
$username = 'cfoxcozj_picThisdb';
$password = 'LfUYHI%]{sjb5A*u';

echo "<h1>Database Connection Test</h1>";
echo "<pre>";

try {
    // Test connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✅ Database connection successful!\n\n";

    // Test if tables exist
    $tables = ['users', 'images', 'credit_transactions', 'settings', 'payments'];
    echo "📋 Checking tables:\n";

    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                echo "✅ Table '$table' exists\n";
            } else {
                echo "❌ Table '$table' missing\n";
            }
        } catch (Exception $e) {
            echo "❌ Error checking table '$table': " . $e->getMessage() . "\n";
        }
    }

    // Test settings
    echo "\n⚙️  Checking settings:\n";
    try {
        $settings = $pdo->query("SELECT k, v FROM settings LIMIT 5")->fetchAll();
        if (count($settings) > 0) {
            foreach ($settings as $setting) {
                echo "✅ Setting: {$setting['k']} = {$setting['v']}\n";
            }
        } else {
            echo "⚠️  No settings found - you may need to run the setup script\n";
        }
    } catch (Exception $e) {
        echo "❌ Error checking settings: " . $e->getMessage() . "\n";
    }

    // Test user count
    echo "\n👥 User statistics:\n";
    try {
        $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
        echo "✅ Total users: $userCount\n";

        if ($userCount > 0) {
            $imageCount = $pdo->query("SELECT COUNT(*) as count FROM images")->fetch()['count'];
            echo "✅ Total images: $imageCount\n";

            $creditSum = $pdo->query("SELECT SUM(amount) as total FROM credit_transactions WHERE transaction_type = 'purchase'")->fetch()['total'] ?? 0;
            echo "✅ Total credits purchased: $creditSum\n";
        }
    } catch (Exception $e) {
        echo "❌ Error getting statistics: " . $e->getMessage() . "\n";
    }

    echo "\n🎉 Database test completed!\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
    echo "🔧 Troubleshooting tips:\n";
    echo "1. Check your database credentials in this file\n";
    echo "2. Make sure the database exists in cPanel\n";
    echo "3. Verify the user has proper permissions\n";
    echo "4. Check if MySQL server is running\n";
}

echo "</pre>";
echo "<style>body{font-family:monospace;background:#f5f5f5;padding:20px;}h1{color:#333;margin-bottom:20px;}</style>";
?>
