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

// Step 2: Manually set user session by making a direct API call to set session
$output .= "Step 2: Setting user session manually...\n";

// For this test, we'll create a simple PHP script that sets the session
// and then make a request to it
$sessionScript = 'set_session.php';
$sessionScriptContent = '<?php
session_id("' . $sessionId . '");
session_start();
$_SESSION["user"] = [
    "id" => 1,
    "fullName" => "Admin User",
    "email" => "admin@picturethis.com"
];
echo "Session set successfully";
?>';

file_put_contents($sessionScript, $sessionScriptContent);

$sessionUrl = $baseUrl . '/' . $sessionScript;
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $sessionUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookiesFile);
curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookiesFile);

$sessionResponse = curl_exec($ch2);
$sessionHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

curl_close($ch2);

$output .= "Session setup response (HTTP $sessionHttpCode): $sessionResponse\n";

// Clean up the temporary script
if (file_exists($sessionScript)) {
    unlink($sessionScript);
}

$output .= "\n";

// Step 3: Generate CSRF token and test the agent API
$output .= "Step 3: Generating CSRF token and testing agent API...\n";

// Generate CSRF token (this will work now that we have a session)
require_once 'src/utils/CSRF.php';
$csrf = new CSRF();
$csrfToken = $csrf->generateToken();

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
$apiHttpCode = curl_getinfo($ch3, CURLINFO_HTTP_CODE);

// Separate headers from body
$headerSize = curl_getinfo($ch3, CURLINFO_HEADER_SIZE);
$headers = substr($apiResponse, 0, $headerSize);
$body = substr($apiResponse, $headerSize);

curl_close($ch3);

$output .= "API Response (HTTP $apiHttpCode):\n";
$output .= $body . "\n\n";

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