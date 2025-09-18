<?php
// Debug version - add this at the very top to see what's happening
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering IMMEDIATELY to prevent headers from being sent
ob_start();

// Initialize session FIRST, before ANY output
session_start();

// Debug: Output basic information
echo "=== DEBUG: Script Started ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n\n";

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    // Web browser mode - set headers for proper output
    header('Content-Type: text/plain');
    header('Cache-Control: no-cache');
    header('Connection: close');

    // Increase execution time for web
    set_time_limit(60);
}

// Debug: Check if required files exist
echo "=== DEBUG: File Checks ===\n";
echo "CSRF.php exists: " . (file_exists('src/utils/CSRF.php') ? 'YES' : 'NO') . "\n";
echo "Config.php exists: " . (file_exists('config/config.php') ? 'YES' : 'NO') . "\n";
echo "set_session_http.php exists: " . (file_exists('set_session_http.php') ? 'YES' : 'NO') . "\n\n";

// Test the agent API by directly setting session (bypassing login)
// This isolates whether the issue is authentication or the API itself

// Test user credentials (from setup_database.php)
$testEmail = 'admin@picturethis.com';
$testPassword = 'admin123';
$baseUrl = 'https://demo.cfox.co.za';

// Collect all output
$output = "=== Testing Agent API (Bypassing Authentication) ===\n\n";

// Check if cookies file is writable
$cookiesFile = '/tmp/cookies_' . uniqid() . '.txt';
if (file_exists($cookiesFile)) {
    unlink($cookiesFile);
}

if (!is_writable(dirname($cookiesFile) ?: '.')) {
    $output .= "❌ Directory is not writable for cookies file\n";
    echo $output;
    ob_end_flush();
    exit(1);
}

$output .= "Directory is writable for cookies\n";
$output .= "Cookies file path: $cookiesFile\n\n";

// Step 1: Create a simple session by visiting the homepage
$output .= "Step 1: Creating session by visiting homepage...\n";

// Debug: Show the URL we're trying to access
$homeUrl = $baseUrl . '/';
$output .= "Homepage URL: $homeUrl\n";

// Ensure cookies file exists
if (!file_exists($cookiesFile)) {
    touch($cookiesFile);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $homeUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiesFile);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

$homeResponse = curl_exec($ch);
$homeHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Debug: Check for cURL errors
if (curl_error($ch)) {
    $output .= "cURL error: " . curl_error($ch) . "\n";
    $output .= "cURL error number: " . curl_errno($ch) . "\n";
    $output .= "URL that failed: $homeUrl\n";
}

// Debug: Show response info
$output .= "cURL info: " . print_r(curl_getinfo($ch), true) . "\n";

// Separate headers from body
$homeHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$homeHeaders = substr($homeResponse, 0, $homeHeaderSize);
$homeBody = substr($homeResponse, $homeHeaderSize);

curl_close($ch);

$output .= "✅ Homepage loaded (HTTP $homeHttpCode)\n";

// Debug: Show headers
$output .= "Homepage headers:\n";
$output .= $homeHeaders . "\n\n";

// Debug: Check if cookies were saved after homepage visit
if (file_exists($cookiesFile)) {
    $cookieContent = file_get_contents($cookiesFile);
    $output .= "Cookies saved after homepage: " . (strlen($cookieContent) > 0 ? "YES (" . strlen($cookieContent) . " bytes)" : "EMPTY") . "\n";
    if (strlen($cookieContent) > 0) {
        $output .= "Cookies content:\n```\n$cookieContent```\n";
    }
} else {
    $output .= "❌ Cookies file not created after homepage visit\n";
}

// Extract session cookie from headers
$sessionId = null;
if (preg_match('/set-cookie: PHPSESSID=([^;]+)/i', $homeHeaders, $matches)) {
    $sessionId = $matches[1];
    $output .= "✅ Extracted session ID: " . substr($sessionId, 0, 10) . "...\n";

    // Check if we need to change session ID
    $currentSessionId = session_id();
    if ($currentSessionId !== $sessionId) {
        $output .= "Current session ID: $currentSessionId\n";
        $output .= "New session ID: $sessionId\n";
        $output .= "Session IDs differ - this might cause issues\n";
    } else {
        $output .= "Session ID matches - good!\n";
    }

    // Manually write the cookie to the file in Netscape format
    $cookieLine = "demo.cfox.co.za\tFALSE\t/\tFALSE\t0\tPHPSESSID\t$sessionId\n";
    file_put_contents($cookiesFile, $cookieLine, FILE_APPEND);
    $output .= "✅ Manually saved cookie to file\n";
} else {
    $output .= "❌ Could not extract session ID from homepage\n";
    echo $output;
    ob_end_flush();
    exit(1);
}

$output .= "\n";

// Step 2: Set user session via HTTP (web server context)
$output .= "Step 2: Setting user session via HTTP...\n";

// Ensure cookies file exists
if (!file_exists($cookiesFile)) {
    touch($cookiesFile);
}

// Use HTTP request to set session in web server context
$sessionUrl = $baseUrl . '/set_session_http.php';
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $sessionUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookiesFile);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookiesFile);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_HEADER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 10);

$sessionResponse = curl_exec($ch2);
$sessionHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

// Debug: Check for cURL errors
if (curl_error($ch2)) {
    $output .= "cURL error in session setup: " . curl_error($ch2) . "\n";
}

// Separate headers from body for session response
$sessionHeaderSize = curl_getinfo($ch2, CURLINFO_HEADER_SIZE);
$sessionHeaders = substr($sessionResponse, 0, $sessionHeaderSize);
$sessionBody = substr($sessionResponse, $sessionHeaderSize);

curl_close($ch2);

$output .= "Session setup response (HTTP $sessionHttpCode): $sessionBody\n";

// Debug: Check cookies after session setup
if (file_exists($cookiesFile)) {
    $cookieContent = file_get_contents($cookiesFile);
    $output .= "Cookies after session setup: " . (strlen($cookieContent) > 0 ? "YES (" . strlen($cookieContent) . " bytes)" : "EMPTY") . "\n";
} else {
    $output .= "❌ Cookies file missing after session setup\n";
}

if ($sessionHttpCode === 200) {
    $sessionData = json_decode($sessionBody, true);
    if ($sessionData && isset($sessionData['success']) && $sessionData['success']) {
        $output .= "✅ Session set successfully via HTTP\n";
        if (isset($sessionData['session_id'])) {
            $sessionId = $sessionData['session_id']; // Update session ID to match HTTP session
            $output .= "✅ Updated session ID to: " . substr($sessionId, 0, 10) . "...\n";
        }

        // Manually set the user data in the local session
        if (isset($sessionData['user'])) {
            $_SESSION['user'] = $sessionData['user'];
            $output .= "✅ Manually set user data in local session\n";
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

// CRITICAL FIX: Switch to the server's session ID before generating CSRF token
$currentSessionId = session_id();
if ($currentSessionId !== $sessionId) {
    $output .= "Switching from local session ($currentSessionId) to server session ($sessionId)...\n";
    session_write_close(); // Close current session
    session_id($sessionId); // Set to server's session ID
    session_start(); // Start with server's session ID
    $output .= "✅ Successfully switched to server session: " . session_id() . "\n";
    
    // Re-set user data in the correct session
    if (isset($sessionData['user'])) {
        $_SESSION['user'] = $sessionData['user'];
        $output .= "✅ Re-set user data in server session\n";
    }
} else {
    $output .= "Session IDs already match - no switch needed\n";
}

$output .= "\n";

// Step 3: Generate CSRF token and test the agent API
$output .= "Step 3: Generating CSRF token and testing agent API...\n";

// Now generate CSRF token in the correct session
echo "=== DEBUG: Loading CSRF Class ===\n";
try {
    require_once 'src/utils/CSRF.php';
    echo "CSRF class loaded successfully\n";
    $csrf = new CSRF();
    $csrfToken = $csrf->generateToken();
    echo "CSRF token generated in server session: " . substr($csrfToken, 0, 10) . "...\n\n";
} catch (Exception $e) {
    echo "ERROR loading CSRF: " . $e->getMessage() . "\n";
    exit(1);
} catch (Error $e) {
    echo "FATAL ERROR loading CSRF: " . $e->getMessage() . "\n";
    exit(1);
}

// Debug: Check session data before API call
$output .= "Session data before API call:\n";
$output .= "- Current Session ID: " . session_id() . "\n";
$output .= "- Server Session ID: " . $sessionId . "\n";
$output .= "- Session IDs match: " . (session_id() === $sessionId ? 'YES' : 'NO - THIS IS THE PROBLEM!') . "\n";
$output .= "- Session save path: " . session_save_path() . "\n";
$output .= "- User in session: " . (isset($_SESSION['user']) ? 'YES' : 'NO') . "\n";
if (isset($_SESSION['user'])) {
    $output .= "- User data: " . json_encode($_SESSION['user']) . "\n";
}
if (isset($_SESSION['csrf_token'])) {
    $output .= "- CSRF token in session: " . substr($_SESSION['csrf_token'], 0, 10) . "...\n";
}
$output .= "- All session keys: " . implode(', ', array_keys($_SESSION)) . "\n";

$apiData = [
    'prompt' => 'A beautiful sunset over mountains',
    'csrf_token' => $csrfToken
];

// Always send as JSON for API consistency
$jsonPayload = json_encode($apiData);
$contentType = 'application/json';

$output .= "API Request Data:\n";
$output .= "- Prompt: " . $apiData['prompt'] . "\n";
$output .= "- CSRF Token: " . substr($apiData['csrf_token'], 0, 10) . "...\n";
$output .= "- JSON Payload: $jsonPayload\n";
$output .= "- Content-Type: $contentType\n\n";

$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, $baseUrl . '/api/prompt-agent/start');
curl_setopt($ch3, CURLOPT_POST, true);
curl_setopt($ch3, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch3, CURLOPT_HTTPHEADER, [
    'Content-Type: ' . $contentType
]);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_COOKIEFILE, $cookiesFile);
curl_setopt($ch3, CURLOPT_COOKIEJAR, $cookiesFile);
curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch3, CURLOPT_HEADER, true);
curl_setopt($ch3, CURLOPT_TIMEOUT, 30);
curl_setopt($ch3, CURLOPT_CONNECTTIMEOUT, 10);

// Debug: Show what cookies are being sent
if (file_exists($cookiesFile)) {
    $cookieData = file_get_contents($cookiesFile);
    $output .= "Cookies being sent with API request:\n";
    if ($cookieData) {
        $output .= "```\n$cookieData```\n";
    } else {
        $output .= "Cookies file exists but is empty\n";
    }
} else {
    $output .= "Cookies file does not exist\n";
}

if (php_sapi_name() !== 'cli') {
    $output .= "Request type: JSON (web mode)\n";
} else {
    $output .= "Request type: JSON (CLI mode)\n";
}

$apiResponse = curl_exec($ch3);
$apiHttpCode = curl_getinfo($ch3, CURLINFO_HTTP_CODE);

// Debug: Show detailed API response info
$output .= "API cURL info: " . print_r(curl_getinfo($ch3), true) . "\n";

// Debug: Check for cURL errors in API call
if (curl_error($ch3)) {
    $output .= "API cURL error: " . curl_error($ch3) . "\n";
    $output .= "API cURL error number: " . curl_errno($ch3) . "\n";
}

// Separate headers from body
$headerSize = curl_getinfo($ch3, CURLINFO_HEADER_SIZE);
$headers = substr($apiResponse, 0, $headerSize);
$body = substr($apiResponse, $headerSize);

curl_close($ch3);

$output .= "API Response Headers:\n";
$output .= $headers . "\n";
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

// Final flush for web browsers
if (php_sapi_name() !== 'cli') {
    flush();
}
?>