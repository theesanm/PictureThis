<?php
/**
 * Simple Deployment Script for PictureThis
 * Run this to deploy from GitHub folder to production
 */

// Start output buffering for clean display
ob_start();

echo "<h1>🚀 PictureThis Deployment Script</h1>";
echo "<pre>";

// Step 1: Switch to production mode
echo "📝 Step 1: Switching to production configuration...\n";

$configFile = __DIR__ . '/config/config.php';

// Check if config file exists and is readable
if (!file_exists($configFile)) {
    echo "❌ Config file not found: {$configFile}\n";
    exit;
}

if (!is_readable($configFile)) {
    echo "❌ Config file not readable: {$configFile}\n";
    exit;
}

$configContent = file_get_contents($configFile);

if ($configContent === false) {
    echo "❌ Could not read config file\n";
    exit;
}

// Check current mode with more flexible pattern matching
if (preg_match('/define\s*\(\s*[\'"]IS_PRODUCTION[\'"]\s*,\s*false\s*\)/i', $configContent)) {
    // Switch to production
    $configContent = preg_replace('/define\s*\(\s*[\'"]IS_PRODUCTION[\'"]\s*,\s*false\s*\)/i', "define('IS_PRODUCTION', true)", $configContent);
    $result = file_put_contents($configFile, $configContent);
    if ($result !== false) {
        echo "✅ Switched to PRODUCTION mode\n";
    } else {
        echo "❌ Failed to write config file\n";
    }
} else if (preg_match('/define\s*\(\s*[\'"]IS_PRODUCTION[\'"]\s*,\s*true\s*\)/i', $configContent)) {
    echo "✅ Already in PRODUCTION mode\n";
} else {
    echo "❌ Could not determine current mode in config file\n";
    echo "   Config file content around IS_PRODUCTION:\n";
    if (preg_match('/IS_PRODUCTION.*$/m', $configContent, $matches)) {
        echo "   Found: " . htmlspecialchars($matches[0]) . "\n";
    } else {
        echo "   No IS_PRODUCTION line found\n";
    }
}

echo "\n";

// Step 2: Load configuration and test
echo "🔧 Step 2: Testing configuration...\n";
try {
    require_once __DIR__ . '/config/config.php';
    echo "✅ Configuration loaded successfully\n";
    echo "   Environment: " . APP_ENV . "\n";
    echo "   App URL: " . APP_URL . "\n";
    echo "   Debug Mode: " . (APP_DEBUG ? 'ON' : 'OFF') . "\n";
} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 3: Test database connection
echo "🗄️  Step 3: Testing database connection...\n";
try {
    require_once __DIR__ . '/src/lib/db.php';
    $pdo = get_db();
    echo "✅ Database connection successful!\n";

    // Check tables
    $tables = ['users', 'images', 'credit_transactions', 'settings', 'payments'];
    echo "📋 Checking tables:\n";

    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                echo "   ✅ $table exists\n";
            } else {
                echo "   ❌ $table missing\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Error checking $table: " . $e->getMessage() . "\n";
        }
    }

    // Check user count
    try {
        $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
        echo "👥 Users in database: $userCount\n";
    } catch (Exception $e) {
        echo "❌ Error checking users: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "💡 Make sure database credentials are set in production config\n";
}

echo "\n";

// Step 4: Check essential files
echo "📁 Step 4: Checking essential files...\n";
$essentialFiles = [
    'index.php',
    'config/config.php',
    'config/production.php',
    'src/controllers/HomeController.php',
    'src/views/home.php',
    '.htaccess'
];

foreach ($essentialFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $file exists\n";
    } else {
        echo "❌ $file MISSING\n";
    }
}

echo "\n";

// Step 5: Check directories
echo "📂 Step 5: Checking directories...\n";
$essentialDirs = [
    'uploads',
    'logs',
    'config'
];

foreach ($essentialDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        echo "✅ $dir/ exists\n";
        if (is_writable($fullPath)) {
            echo "   ✏️  Writable: Yes\n";
        } else {
            echo "   ✏️  Writable: No (check permissions)\n";
        }
    } else {
        echo "❌ $dir/ MISSING\n";
    }
}

echo "\n";

// Step 6: Test web access
echo "🌐 Step 6: Testing web access...\n";
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo "📍 Current URL: $currentUrl\n";

if (strpos($currentUrl, 'localhost') !== false) {
    echo "⚠️  WARNING: Running on localhost - this should be run on production server\n";
} else {
    echo "✅ Running on production server\n";
}

echo "\n";

// Step 7: Deployment summary
echo "📋 Step 7: Deployment Summary\n";
echo "========================================\n";

$issues = [];

// Check for common issues
if (!defined('DB_HOST') || DB_HOST === '') {
    $issues[] = "Database host not configured";
}
if (!defined('OPENROUTER_API_KEY') || OPENROUTER_API_KEY === '') {
    $issues[] = "OpenRouter API key not configured";
}
if (!is_writable(__DIR__ . '/uploads')) {
    $issues[] = "Uploads directory not writable";
}

if (empty($issues)) {
    echo "✅ DEPLOYMENT SUCCESSFUL!\n";
    echo "🎉 Your app should be working at: " . APP_URL . "\n";
    echo "\n📝 Next steps:\n";
    echo "   1. Test the homepage: " . APP_URL . "\n";
    echo "   2. Run diagnostics: " . APP_URL . "/diagnostic.php\n";
    echo "   3. Check database: " . APP_URL . "/test_database.php\n";
    echo "   4. Remove this deploy.php file for security\n";
} else {
    echo "⚠️  DEPLOYMENT ISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
    echo "\n🔧 Fix these issues before proceeding\n";
}

echo "\n========================================\n";
echo "🚀 Deployment script completed at " . date('Y-m-d H:i:s') . "\n";

?>