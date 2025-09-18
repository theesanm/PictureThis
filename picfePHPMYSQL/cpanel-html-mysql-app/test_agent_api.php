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

// Step 2: Set user session via HTTP (web server context)
$output .= "Step 2: Setting user session via HTTP...\n";

// Use HTTP request to set session in web server context
$sessionUrl = $baseUrl . '/set_session_http.php';
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $sessionUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookiesFile);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookiesFile);

$sessionResponse = curl_exec($ch2);
$sessionHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

curl_close($ch2);

$output .= "Session setup response (HTTP $sessionHttpCode): $sessionResponse\n";

if ($sessionHttpCode === 200) {
    $sessionData = json_decode($sessionResponse, true);
    if ($sessionData && isset($sessionData['success']) && $sessionData['success']) {
        $output .= "✅ Session set successfully via HTTP\n";
        if (isset($sessionData['session_id'])) {
            $sessionId = $sessionData['session_id']; // Update session ID to match HTTP session
            $output .= "✅ Updated session ID to: " . substr($sessionId, 0, 10) . "...\n";
        }
    } else {
        $output .= "❌ Failed to set session via HTTP\n";
        echo $output;
        ob_end_flush();
        exit(1);
    }
} else {
    $output .= "❌ HTTP session setup failed with code: $sessionHttpCode\n";
    echo $output;
    ob_end_flush();
    exit(1);
}

$output .= "\n";

// Step 3: Generate CSRF token and test the agent API
$output .= "Step 3: Generating CSRF token and testing agent API...\n";

// Start session with the correct session ID from HTTP setup
session_id($sessionId);
session_start();

$output .= "✅ Session started with correct ID for CSRF token generation\n";

// Generate CSRF token (session should now contain user data)
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
if (file_exists('set_session_http.php')) {
    unlink('set_session_http.php');
}

$output .= "\n=== Test Complete ===\n";

// Output everything at once
echo $output;
ob_end_flush();
?>