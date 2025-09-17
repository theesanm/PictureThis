<?php
/**
 * Post-Deployment Setup Script
 * Run this after uploading files to cPanel to finalize setup
 */

// Check if we're running on the server
echo "<h1>PictureThis Post-Deployment Setup</h1>";
echo "<pre>";

// Test database connection
echo "🔍 Testing database connection...\n";
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/src/lib/db.php';

    $pdo = get_db();
    echo "✅ Database connection successful!\n\n";

    // Check if tables exist
    $tables = ['users', 'images', 'credit_transactions', 'settings', 'payments'];
    echo "📋 Checking required tables:\n";

    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                echo "✅ Table '$table' exists\n";
            } else {
                echo "❌ Table '$table' missing - run setup_database.php\n";
            }
        } catch (Exception $e) {
            echo "❌ Error checking table '$table': " . $e->getMessage() . "\n";
        }
    }

    // Check settings
    echo "\n⚙️  Checking application settings:\n";
    try {
        $settings = $pdo->query("SELECT COUNT(*) as count FROM settings")->fetch()['count'];
        if ($settings > 0) {
            echo "✅ Settings configured ($settings records)\n";
        } else {
            echo "⚠️  No settings found - default settings will be used\n";
        }
    } catch (Exception $e) {
        echo "❌ Error checking settings: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "🔧 Check your config/config.php file\n";
}

// Check file permissions
echo "\n📁 Checking file permissions:\n";
$criticalFiles = [
    'config/config.php' => 'readable',
    'src/lib/db.php' => 'readable',
    'index.php' => 'readable',
    'public/uploads/' => 'writable'
];

foreach ($criticalFiles as $file => $requirement) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        if ($requirement === 'readable' && is_readable($fullPath)) {
            echo "✅ $file is readable\n";
        } elseif ($requirement === 'writable' && is_writable($fullPath)) {
            echo "✅ $file is writable\n";
        } else {
            echo "❌ $file is not $requirement\n";
        }
    } else {
        echo "❌ $file does not exist\n";
    }
}

// Check PHP version
echo "\n🐘 PHP Environment:\n";
echo "✅ PHP Version: " . phpversion() . "\n";
echo "✅ Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "✅ Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

// Check required PHP extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl'];
echo "\n📚 Required PHP Extensions:\n";
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext extension loaded\n";
    } else {
        echo "❌ $ext extension missing\n";
    }
}

// Recommendations
echo "\n💡 Recommendations:\n";
echo "1. Remove this file after deployment for security\n";
echo "2. Remove setup_database.php and test_database.php\n";
echo "3. Set up SSL certificate if not already done\n";
echo "4. Configure backup settings in cPanel\n";
echo "5. Monitor error logs regularly\n";

echo "\n🎉 Post-deployment check complete!\n";
echo "Visit your homepage to test the application.\n";

echo "</pre>";
echo "<style>body{font-family:monospace;background:#f5f5f5;padding:20px;}h1{color:#333;margin-bottom:20px;}</style>";
?>
