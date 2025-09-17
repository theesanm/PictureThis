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

$loginPostData = http_build_query($loginData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginPostData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "❌ Login failed: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(1);
}

curl_close($ch);

echo "✅ Login completed (HTTP $loginHttpCode)\n";

// Debug: Check if cookies were saved
if (file_exists('cookies.txt')) {
    echo "Cookies file created\n";
    $cookies = file_get_contents('cookies.txt');
    echo "Cookies content: " . $cookies . "\n";
} else {
    echo "❌ Cookies file not created\n";
}

echo "\n";

// Step 2: Test the agent API with the session cookie
echo "Step 2: Testing agent API...\n";

// Debug: Check cookies before API call
if (file_exists('cookies.txt')) {
    echo "Cookies file exists before API call\n";
    $cookies = file_get_contents('cookies.txt');
    echo "Cookies content: " . $cookies . "\n";
} else {
    echo "❌ Cookies file missing before API call\n";
}

echo "\n";

// Generate new CSRF token for the API call
$csrfToken2 = $csrf->generateToken();

$apiData = [
    'prompt' => 'A beautiful sunset over mountains',
    'csrf_token' => $csrfToken2
];

$jsonPayload = json_encode($apiData);

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $baseUrl . '/api/prompt-agent/start');
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch2, CURLOPT_VERBOSE, true);
curl_setopt($ch2, CURLOPT_HEADER, true); // Include headers in response

$apiResponse = curl_exec($ch2);
$apiHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

// Separate headers from body
$headerSize = curl_getinfo($ch2, CURLINFO_HEADER_SIZE);
$headers = substr($apiResponse, 0, $headerSize);
$body = substr($apiResponse, $headerSize);

if (curl_errno($ch2)) {
    echo "❌ API call failed: " . curl_error($ch2) . "\n";
    curl_close($ch2);
    exit(1);
}

curl_close($ch2);

echo "API Request Headers:\n";
echo "Content-Type: application/json\n";
echo "Cookies from file: " . (file_exists('cookies.txt') ? file_get_contents('cookies.txt') : 'NO COOKIES FILE') . "\n\n";

echo "API Response Headers (HTTP $apiHttpCode):\n";
echo $headers . "\n";

echo "API Response Body:\n";
echo $body . "\n\n";

if ($apiHttpCode === 200) {
    $response = json_decode($body, true);
    if ($response && isset($response['success']) && $response['success']) {
        echo "✅ Agent API test PASSED!\n";
    } else {
        echo "❌ Agent API test FAILED!\n";
        if ($response && isset($response['message'])) {
            echo "Error: " . $response['message'] . "\n";
        }
    }
} else {
    echo "❌ API call failed with HTTP code: $apiHttpCode\n";
}

// Clean up
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}

echo "\n=== Test Complete ===\n";
?>