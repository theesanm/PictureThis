<?php
// Test the agent API with proper authentication
// This script logs in first, then tests the agent endpoint

// Test user credentials (from setup_database.php)
$testEmail = 'admin@picturethis.com';
$testPassword = 'admin123';
$baseUrl = 'https://demo.cfox.co.za';

echo "=== Testing Agent API with Authentication ===\n\n";

// Check if cookies file is writable
$cookiesFile = 'cookies.txt';
if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}

if (!is_writable(dirname($cookiesFile) ?: '.')) {
    echo "❌ Directory is not writable for cookies file\n";
    exit(1);
}

echo "Directory is writable for cookies\n\n";

// Step 1: Visit login page first to get CSRF token
echo "Step 1: Visiting login page to get CSRF token...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiesFile);
curl_setopt($ch, CURLOPT_HEADER, true);

$loginPageResponse = curl_exec($ch);
$loginPageHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Separate headers from body
$loginPageHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$loginPageHeaders = substr($loginPageResponse, 0, $loginPageHeaderSize);
$loginPageBody = substr($loginPageResponse, $loginPageHeaderSize);

curl_close($ch);

echo "✅ Login page loaded (HTTP $loginPageHttpCode)\n";

// Extract CSRF token from the login page HTML
if (preg_match('/name="csrf_token" value="([^"]+)"/', $loginPageBody, $matches)) {
    $csrfToken = $matches[1];
    echo "✅ Extracted CSRF token: " . substr($csrfToken, 0, 10) . "...\n";
} else {
    echo "❌ Could not extract CSRF token from login page\n";
    echo "Login page content:\n" . substr($loginPageBody, 0, 500) . "\n";
    exit(1);
}

echo "\n";

// Step 2: Login with the extracted CSRF token
echo "Step 2: Logging in with valid CSRF token...\n";
$loginData = [
    'email' => $testEmail,
    'password' => $testPassword,
    'csrf_token' => $csrfToken
];

$loginPostData = http_build_query($loginData);

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $loginPostData);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookiesFile);
curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookiesFile);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);
curl_setopt($ch2, CURLOPT_HEADER, true);

$loginResponse = curl_exec($ch2);
$loginHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

// Separate headers from body for login
$loginHeaderSize = curl_getinfo($ch2, CURLINFO_HEADER_SIZE);
$loginHeaders = substr($loginResponse, 0, $loginHeaderSize);
$loginBody = substr($loginResponse, $loginHeaderSize);

if (curl_errno($ch2)) {
    echo "❌ Login failed: " . curl_error($ch2) . "\n";
    curl_close($ch2);
    exit(1);
}

curl_close($ch2);

echo "✅ Login completed (HTTP $loginHttpCode)\n";
echo "Login Response Headers:\n" . $loginHeaders . "\n";
echo "Login Response Body:\n" . substr($loginBody, 0, 200) . "\n\n";

// Debug: Check if cookies were saved
if (file_exists($cookiesFile)) {
    echo "Cookies file created\n";
    $cookies = file_get_contents($cookiesFile);
    echo "Cookies content: " . $cookies . "\n";
} else {
    echo "❌ Cookies file not created\n";
    // Try to manually extract session cookie from headers
    if (preg_match('/Set-Cookie: ([^;]+)/', $loginHeaders, $matches)) {
        $sessionCookie = $matches[1];
        echo "Found session cookie in headers: $sessionCookie\n";
        file_put_contents($cookiesFile, "# Netscape HTTP Cookie File\n$baseUrl\tFALSE\t/\tFALSE\t0\t" . str_replace('=', "\t", $sessionCookie) . "\n");
        echo "Manually saved session cookie to file\n";
    }
}

echo "\n";

// Step 3: Test the agent API with the session cookie
echo "Step 3: Testing agent API...\n";

// Debug: Check cookies before API call
if (file_exists($cookiesFile)) {
    echo "Cookies file exists before API call\n";
    $cookies = file_get_contents($cookiesFile);
    echo "Cookies content: " . $cookies . "\n";
} else {
    echo "❌ Cookies file missing before API call\n";
}

echo "\n";

// Generate new CSRF token for the API call (using the session from login)
require_once 'src/utils/CSRF.php';
$csrf2 = new CSRF();
$csrfToken2 = $csrf2->generateToken();

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
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookiesFile);
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
echo "Cookies from file: " . (file_exists($cookiesFile) ? file_get_contents($cookiesFile) : 'NO COOKIES FILE') . "\n\n";

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
if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}

echo "\n=== Test Complete ===\n";
?>