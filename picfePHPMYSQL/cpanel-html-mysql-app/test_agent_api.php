<?php
// Test the actual agent API endpoint
// This simulates the exact request that the frontend makes

// Start session FIRST before any output
session_start();

// Set up test environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

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

// Set up the request BEFORE any output
$_POST['csrf_token'] = $csrfToken;

// Write the JSON payload to php://input BEFORE loading the controller
$jsonPayload = json_encode($testPayload);
file_put_contents('php://input', $jsonPayload);

// Now we can output
echo "=== Testing Agent API Endpoint ===\n\n";
echo "Test payload: " . $jsonPayload . "\n\n";

try {
    require_once 'src/controllers/PromptAgentController.php';

    // Create controller instance
    $controller = new PromptAgentController();

    // Call the startSession method directly
    echo "Calling startSession method...\n";
    $result = $controller->startSession();

    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?>