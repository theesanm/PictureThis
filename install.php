<?php
/**
 * PictureThis Installation Helper
 * This script helps you set up the .env file for your installation
 */

echo "<h1>🚀 PictureThis Installation Helper</h1>";
echo "<pre>";

// Check if .env file exists
$envFile = __DIR__ . '/.env';
$envExample = __DIR__ . '/.env.example';

if (file_exists($envFile)) {
    echo "✅ .env file already exists\n";
    echo "If you need to update it, edit: config/.env\n\n";

    // Load and display current values (masking sensitive data)
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    echo "Current configuration:\n";
    foreach ($lines as $line) {
        if (empty(trim($line)) || strpos($line, '#') === 0) {
            echo $line . "\n";
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            // Mask sensitive values
            if (in_array($key, ['DB_PASS', 'PAYFAST_MERCHANT_KEY', 'PAYFAST_PASSPHRASE', 'OPENROUTER_API_KEY', 'SMTP_PASS'])) {
                $value = str_repeat('*', min(strlen($value), 10));
            }
            echo $key . "=" . $value . "\n";
        }
    }
} elseif (file_exists($envExample)) {
    echo "ℹ️  .env file not found, but .env.example exists\n";
    echo "You need to create config/.env with your actual values\n\n";

    echo "📋 Copy this content to config/.env and update with your values:\n";
    echo "================================================================\n";
    echo file_get_contents($envExample);
    echo "================================================================\n\n";

    echo "✏️  Edit config/.env with your actual credentials:\n";
    echo "- Database password\n";
    echo "- PayFast credentials\n";
    echo "- OpenRouter API key\n";
    echo "- SMTP password\n\n";

} else {
    echo "❌ Neither .env nor .env.example found in config directory\n";
    echo "Please ensure you have copied the config files correctly\n\n";
}

// Test database connection if .env exists
if (file_exists($envFile)) {
    echo "🔍 Testing Database Connection:\n";

    // Load .env file
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envVars = [];
    foreach ($envLines as $line) {
        if (strpos($line, '#') === 0 || empty(trim($line))) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }

    try {
        $dsn = "mysql:host={$envVars['DB_HOST']};dbname={$envVars['DB_NAME']};charset=utf8mb4";
        $pdo = new PDO($dsn, $envVars['DB_USER'], $envVars['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "✅ Database connection successful\n";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
        echo "💡 Check your database credentials in config/.env\n";
    }
}

echo "\n📁 File Structure Check:\n";
$requiredFiles = [
    'index.php',
    'config/config.php',
    'config/production.php',
    'src/controllers/HomeController.php'
];

foreach ($requiredFiles as $file) {
    echo "- $file: " . (file_exists(__DIR__ . '/../' . $file) ? '✅' : '❌') . "\n";
}

echo "\n📁 Directory Structure Check:\n";
$requiredDirs = ['src', 'config', 'uploads', 'logs'];
foreach ($requiredDirs as $dir) {
    echo "- $dir/: " . (is_dir(__DIR__ . '/../' . $dir) ? '✅' : '❌') . "\n";
}

echo "</pre>";

if (!file_exists($envFile)) {
    echo "<h2>⚠️ Action Required</h2>";
    echo "<p>Create <code>config/.env</code> file with your actual credentials to continue.</p>";
} else {
    echo "<h2>✅ Next Steps</h2>";
    echo "<ol>";
    echo "<li>Visit your application: <a href='/'>https://demo.cfox.co.za/</a></li>";
    echo "<li>Test image generation functionality</li>";
    echo "<li>Check PayFast integration</li>";
    echo "</ol>";
}
?>