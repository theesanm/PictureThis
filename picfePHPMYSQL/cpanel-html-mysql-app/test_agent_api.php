<?php
// Test the actual agent API endpoint via HTTP
// This generates a curl command to test the real endpoint

// Start session FIRST
session_start();

// Simulate logged in user
$_SESSION['user'] = [
    'id' => 1,
    'email' => 'test@example.com'
];

// Generate CSRF token
require_once 'src/utils/CSRF.php';
$csrf = new CSRF();
$csrfToken = $csrf->generateToken();

// Simulate the JSON payload from the frontend
$testPayload = [
    'prompt' => 'A beautiful sunset over mountains',
    'csrf_token' => $csrfToken
];

// Get the base URL (adjust if needed)
$baseUrl = 'https://demo.cfox.co.za'; // Change this to your actual domain
$endpoint = '/api/prompt-agent/start';

// Create the curl command
$jsonPayload = json_encode($testPayload);
$curlCommand = "curl -X POST '{$baseUrl}{$endpoint}' -H 'Content-Type: application/json' -d '{$jsonPayload}'";

echo "=== Testing Agent API Endpoint via HTTP ===\n\n";
echo "Generated curl command:\n";
echo $curlCommand . "\n\n";
echo "Run this command to test the actual endpoint:\n";
echo "(This will properly populate php://input with the JSON payload)\n\n";

// Also show what we're sending
echo "Payload being sent:\n";
echo $jsonPayload . "\n\n";

echo "Expected result: The endpoint should process the prompt and return a success response.\n";
echo "If it fails with 'Original prompt is required', then the issue is in the web server configuration.\n";
?>