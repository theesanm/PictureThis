<?php
/**
 * PictureThis Environment Setup Script
 * Run this script to set up your environment configuration
 */

// Check if we're in the right directory
if (!file_exists('config/config.php')) {
    die("Error: Please run this script from the root directory of your PictureThis installation.\n");
}

echo "🔧 PictureThis Environment Setup\n";
echo "================================\n\n";

// Check current environment
require_once 'config/config.php';
$currentEnv = IS_PRODUCTION ? 'production' : 'development';
echo "Current environment: $currentEnv\n\n";

// Check if .env file exists
$envFile = __DIR__ . '/.env';
$envExists = file_exists($envFile);

if ($envExists) {
    echo "✅ .env file found\n";
} else {
    echo "⚠️  .env file not found\n";
    echo "   Creating .env file from example...\n";

    $exampleFile = __DIR__ . '/.env.example';
    if (file_exists($exampleFile)) {
        copy($exampleFile, $envFile);
        echo "   ✅ .env file created from .env.example\n";
        echo "   ⚠️  IMPORTANT: Edit .env file with your actual credentials!\n";
    } else {
        echo "   ❌ .env.example not found. Please create .env manually.\n";
    }
}

echo "\n📋 Next Steps:\n";
echo "==============\n";

if (!$envExists) {
    echo "1. Edit the .env file with your actual credentials\n";
}

echo "2. For cPanel deployment:\n";
echo "   a) Upload your project files\n";
echo "   b) Create .env file in /home/username/ (outside public_html)\n";
echo "   c) Or set environment variables in cPanel:\n";
echo "      - Go to cPanel → Software → MultiPHP INI Editor\n";
echo "      - Add environment variables with PICTURETHIS_ prefix\n";
echo "      - Example: PICTURETHIS_DB_PASS=your_actual_password\n";

echo "\n3. Test your configuration:\n";
echo "   - Visit your site\n";
echo "   - Check if database connection works\n";
echo "   - Test PayFast integration\n";

echo "\n🔒 Security Notes:\n";
echo "=================\n";
echo "- .env file should be outside your web root\n";
echo "- Never commit .env file to version control\n";
echo "- Use strong, unique passwords for all services\n";
echo "- Regularly rotate API keys and passwords\n";

echo "\n✅ Setup complete!\n";
?>