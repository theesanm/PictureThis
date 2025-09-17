<?php
/**
 * OpenRouter API Key Setup Script
 * Helps configure the OpenRouter API key for PictureThis
 */

// Check if we're running from the correct directory
if (!file_exists('config/config.php')) {
    die("Error: This script must be run from the PictureThis root directory\n");
}

echo "PictureThis OpenRouter API Key Setup\n";
echo "=====================================\n\n";

require_once 'config/config.php';

echo "Current Configuration:\n";
echo "- IS_PRODUCTION: " . (defined('IS_PRODUCTION') ? (IS_PRODUCTION ? 'true' : 'false') : 'NOT_DEFINED') . "\n";
echo "- OPENROUTER_API_KEY_RUNTIME: " . (defined('OPENROUTER_API_KEY_RUNTIME') ? 'SET (' . strlen(OPENROUTER_API_KEY_RUNTIME) . ' chars)' : 'NOT_SET') . "\n";
echo "- OPENROUTER_API_KEY: " . (defined('OPENROUTER_API_KEY') ? 'SET (' . strlen(OPENROUTER_API_KEY) . ' chars)' : 'NOT_SET') . "\n";
echo "- OPENROUTER_MODEL_RUNTIME: " . (defined('OPENROUTER_MODEL_RUNTIME') ? OPENROUTER_MODEL_RUNTIME : 'NOT_SET') . "\n\n";

if (defined('OPENROUTER_API_KEY_RUNTIME') && strlen(trim(OPENROUTER_API_KEY_RUNTIME)) > 10) {
    echo "✅ OpenRouter API key appears to be configured correctly.\n";
    echo "   The agent should work properly now.\n\n";
} else {
    echo "❌ OpenRouter API key is missing or invalid.\n";
    echo "   To fix this:\n";
    echo "   1. Get an API key from https://openrouter.ai/\n";
    echo "   2. Update config/production.php with your API key:\n";
    echo "      'openrouter' => [\n";
    echo "          'api_key' => 'your-api-key-here',\n";
    echo "          ...\n";
    echo "      ]\n";
    echo "   3. Redeploy your application\n\n";
}

echo "Testing API Key (if configured):\n";
if (defined('OPENROUTER_API_KEY_RUNTIME') && strlen(trim(OPENROUTER_API_KEY_RUNTIME)) > 10) {
    // Test the API key with a simple request
    $apiKey = OPENROUTER_API_KEY_RUNTIME;
    $url = 'https://openrouter.ai/api/v1/auth/key';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "✅ API key is valid and working\n";
    } else {
        echo "❌ API key test failed (HTTP $httpCode)\n";
        echo "   Response: " . substr($response, 0, 100) . "\n";
    }
} else {
    echo "❌ Cannot test API key - not configured\n";
}

echo "\nSetup complete.\n";
?>