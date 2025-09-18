<?php
// Test the agent API by directly setting session (bypassing login)
// This isolates whether the issue is authentication or the API itself

// Start output buffering to prevent headers from being sent
ob_start();

// Test user credentials (from setup_database.php)
$testEmail = 'admin@picturethis.com';
$testPassword = 'admin123';
$baseUrl = 'https://demo.cfox.co.za';

// Collect all output
$output = "=== Testing Agent API (Bypassing Authentication) ===\n\n";

// Check if cookies file is writable
$cookiesFile = 'cookies.txt';
if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}

if (!is_writable(dirname($cookiesFile) ?: '.')) {
    $output .= "❌ Directory is not writable for cookies file\n";
    echo $output;
    ob_end_flush();
    exit(1);
}

$output .= "Directory is writable for cookies\n\n";

// Step 1: Create a simple session by visiting the homepage
$output .= "Step 1: Creating session by visiting homepage...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiesFile);
curl_setopt($ch, CURLOPT_HEADER, true);

$homeResponse = curl_exec($ch);
$homeHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Separate headers from body
$homeHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$homeHeaders = substr($homeResponse, 0, $homeHeaderSize);
$homeBody = substr($homeResponse, $homeHeaderSize);

curl_close($ch);

$output .= "✅ Homepage loaded (HTTP $homeHttpCode)\n";

// Extract session cookie from headers
$sessionId = null;
if (preg_match('/Set-Cookie: PHPSESSID=([^;]+)/', $homeHeaders, $matches)) {
    $sessionId = $matches[1];
    $output .= "✅ Extracted session ID: " . substr($sessionId, 0, 10) . "...\n";
} else {
    $output .= "❌ Could not extract session ID from homepage\n";
    echo $output;
    ob_end_flush();
    exit(1);
}

$output .= "\n";

// Step 2: Start session directly and set user
$output .= "Step 2: Starting session directly and setting user...\n";

// Start session directly in this script (same process, no curl)
session_id($sessionId);
session_start();

// Set the user session directly
$_SESSION['user'] = [
    'id' => 1,
    'fullName' => 'Admin User',
    'email' => 'admin@picturethis.com'
];

$output .= "✅ Session started and user set directly in test script\n";

$output .= "\n";

// Step 3: Generate CSRF token and test the agent API
$output .= "Step 3: Generating CSRF token and testing agent API...\n";

// Generate CSRF token (session is already started from Step 2)
require_once 'src/utils/CSRF.php';
$csrf = new CSRF();
$csrfToken = $csrf->generateToken();

$output .= "✅ CSRF token generated: " . substr($csrfToken, 0, 10) . "...\n";

// Debug: Check session data before API call
$output .= "Session data before API call:\n";
$output .= "- Session ID: " . session_id() . "\n";
$output .= "- Session save path: " . session_save_path() . "\n";
$output .= "- User in session: " . (isset($_SESSION['user']) ? 'YES' : 'NO') . "\n";
if (isset($_SESSION['user'])) {
    $output .= "- User data: " . json_encode($_SESSION['user']) . "\n";
}

$apiData = [
    'prompt' => 'A beautiful sunset over mountains',
    'csrf_token' => $csrfToken
];

$jsonPayload = json_encode($apiData);

$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, $baseUrl . '/api/prompt-agent/start');
curl_setopt($ch3, CURLOPT_POST, true);
curl_setopt($ch3, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_COOKIEFILE, $cookiesFile);
curl_setopt($ch3, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch3, CURLOPT_HEADER, true);

$apiResponse = curl_exec($ch3);
$apiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Separate headers from body
$headerSize = curl_getinfo($ch3, CURLINFO_HEADER_SIZE);
$headers = substr($apiResponse, 0, $headerSize);
$body = substr($apiResponse, $headerSize);

curl_close($ch3);

$output .= "\nAPI Response (HTTP $apiHttpCode):\n";
$output .= $body . "\n\n";

// Debug: Check session data after API call
$output .= "Session data after API call:\n";
$output .= "- Session still active: " . (session_status() === PHP_SESSION_ACTIVE ? 'YES' : 'NO') . "\n";
$output .= "- User still in session: " . (isset($_SESSION['user']) ? 'YES' : 'NO') . "\n";

if ($apiHttpCode === 200) {
    $response = json_decode($body, true);
    if ($response && isset($response['success']) && $response['success']) {
        $output .= "✅ Agent API test PASSED!\n";
    } else {
        $output .= "❌ Agent API test FAILED!\n";
        if ($response && isset($response['message'])) {
            $output .= "Error: " . $response['message'] . "\n";
        }
    }
} else {
    $output .= "❌ API call failed with HTTP code: $apiHttpCode\n";
}

// Clean up
if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}

$output .= "\n=== Test Complete ===\n";

// Output everything at once
echo $output;
ob_end_flush();
?>