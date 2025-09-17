<?php
// Test the agent API with proper authentication
// This script logs in first, then tests the agent endpoint

// Test user credentials (from setup_database.php)
$testEmail = 'admin@picturethis.com';
$testPassword = 'admin123';
$baseUrl = 'https://demo.cfox.co.za';

// Generate CSRF token for login
require_once 'src/utils/CSRF.php';
$csrf = new CSRF();
$csrfToken = $csrf->generateToken();

echo "=== Testing Agent API with Authentication ===\n\n";

// Step 1: Login to get session
echo "Step 1: Logging in...\n";
$loginData = [
    'email' => $testEmail,
    'password' => $testPassword,
    'csrf_token' => $csrfToken
];

$loginCommand = "curl -X POST '{$baseUrl}/login' " .
    "-H 'Content-Type: application/x-www-form-urlencoded' " .
    "-d 'email={$testEmail}&password={$testPassword}&csrf_token={$csrfToken}' " .
    "-c cookies.txt -L -s";

echo "Login command: $loginCommand\n";
exec($loginCommand, $loginOutput, $loginReturnCode);

if ($loginReturnCode !== 0) {
    echo "❌ Login failed with return code: $loginReturnCode\n";
    echo "Output: " . implode("\n", $loginOutput) . "\n";
    exit(1);
}

echo "✅ Login completed\n\n";

// Step 2: Test the agent API with the session cookie
echo "Step 2: Testing agent API...\n";

// Generate new CSRF token for the API call
$csrfToken2 = $csrf->generateToken();

$apiData = [
    'prompt' => 'A beautiful sunset over mountains',
    'csrf_token' => $csrfToken2
];

$jsonPayload = json_encode($apiData);
$apiCommand = "curl -X POST '{$baseUrl}/api/prompt-agent/start' " .
    "-H 'Content-Type: application/json' " .
    "-d '{$jsonPayload}' " .
    "-b cookies.txt -s";

echo "API command: $apiCommand\n";
echo "Payload: $jsonPayload\n\n";

exec($apiCommand, $apiOutput, $apiReturnCode);

echo "API Response:\n";
echo implode("\n", $apiOutput) . "\n\n";

if ($apiReturnCode === 0) {
    $response = json_decode(implode("\n", $apiOutput), true);
    if ($response && isset($response['success']) && $response['success']) {
        echo "✅ Agent API test PASSED!\n";
    } else {
        echo "❌ Agent API test FAILED!\n";
        if ($response && isset($response['message'])) {
            echo "Error: " . $response['message'] . "\n";
        }
    }
} else {
    echo "❌ API call failed with return code: $apiReturnCode\n";
}

// Clean up
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}

echo "\n=== Test Complete ===\n";
?>