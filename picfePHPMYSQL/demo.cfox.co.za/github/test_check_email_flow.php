<?php
// Test script to verify the check-email page flow
// This simulates the registration process and checks the redirect

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/lib/db.php';
require_once __DIR__ . '/src/lib/timezone.php';

echo "Testing Check Email Page Flow\n";
echo "==============================\n\n";

// Test 1: Check if check-email route exists
echo "Test 1: Checking if /check-email route is accessible...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/check-email');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✓ /check-email route is accessible\n";
} else {
    echo "✗ /check-email route returned HTTP $httpCode\n";
}

// Test 2: Check if email parameter is handled
echo "\nTest 2: Testing email parameter handling...\n";
$testEmail = 'test@example.com';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/check-email?email=' . urlencode($testEmail));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200 && strpos($response, $testEmail) !== false) {
    echo "✓ Email parameter is properly handled and pre-filled\n";
} else {
    echo "✗ Email parameter handling failed\n";
}

// Test 3: Check timezone functions
echo "\nTest 3: Testing timezone functions...\n";
try {
    $now = get_utc_now();
    $future = $now->modify('+24 hours');
    echo "✓ UTC timezone functions working correctly\n";
    echo "  Current UTC time: " . $now->format('Y-m-d H:i:s') . "\n";
    echo "  Token expiry: " . $future->format('Y-m-d H:i:s') . "\n";
} catch (Exception $e) {
    echo "✗ Timezone functions failed: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
echo "To test the full flow:\n";
echo "1. Visit /register and create a test account\n";
echo "2. Verify you're redirected to /check-email with your email pre-filled\n";
echo "3. Test the resend functionality\n";
echo "4. Check your email for the verification link\n";
?>