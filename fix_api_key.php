<?php
/**
 * Quick API Key Configuration Fix
 * Temporarily sets a dummy API key to prevent 500 errors
 * Replace with real API key from OpenRouter.ai
 */

// Check if we're running from the correct directory
if (!file_exists('config/config.php')) {
    die("Error: This script must be run from the PictureThis root directory\n");
}

echo "PictureThis API Key Configuration Fix\n";
echo "======================================\n\n";

require_once 'config/config.php';

echo "Current Status:\n";
$apiKey = defined('OPENROUTER_API_KEY_RUNTIME') ? OPENROUTER_API_KEY_RUNTIME : '';
$keyLength = strlen(trim($apiKey));

if ($keyLength >= 10) {
    echo "✅ API key appears to be configured correctly\n";
    echo "   The agent should work properly now.\n\n";
    exit(0);
} else {
    echo "❌ API key is missing or invalid (length: $keyLength)\n\n";
}

// Create a backup of the current config
$configFile = 'config/production.php';
$backupFile = 'config/production.php.backup';

if (!file_exists($backupFile)) {
    if (copy($configFile, $backupFile)) {
        echo "✅ Created backup: $backupFile\n";
    } else {
        echo "❌ Failed to create backup\n";
        exit(1);
    }
}

// Read the current config
$configContent = file_get_contents($configFile);
if ($configContent === false) {
    echo "❌ Failed to read config file\n";
    exit(1);
}

// Replace the empty API key with a placeholder
$oldPattern = "'api_key' => '',";
$newPattern = "'api_key' => 'sk-or-v1-placeholder-key-replace-with-real-key-from-openrouter-ai',";

if (strpos($configContent, $oldPattern) !== false) {
    $newContent = str_replace($oldPattern, $newPattern, $configContent);

    if (file_put_contents($configFile, $newContent)) {
        echo "✅ Updated config with placeholder API key\n";
        echo "   IMPORTANT: Replace the placeholder key with a real API key from https://openrouter.ai/\n\n";
        echo "Next steps:\n";
        echo "1. Go to https://openrouter.ai/ and get an API key\n";
        echo "2. Replace 'sk-or-v1-placeholder-key-replace-with-real-key-from-openrouter-ai' in config/production.php\n";
        echo "3. Redeploy your application\n\n";
    } else {
        echo "❌ Failed to update config file\n";
        exit(1);
    }
} else {
    echo "❌ Could not find the API key pattern in config file\n";
    echo "   You may need to manually update config/production.php\n";
}

echo "Configuration fix complete.\n";
?>