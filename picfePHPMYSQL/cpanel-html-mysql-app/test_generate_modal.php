<?php
// test_generate_page.php - Test script to check if agent modal is included in generate page

// Start session and simulate logged-in user
session_start();
$_SESSION['user'] = [
    'id' => 1,
    'email' => 'test@example.com',
    'name' => 'Test User',
    'credits' => 100
];

// Include the config
require_once __DIR__ . '/config/config.php';

// Simulate the request to /generate
$_SERVER['REQUEST_URI'] = '/generate';

// Include the index.php to handle routing
ob_start();
include __DIR__ . '/index.php';
$content = ob_get_clean();

// Check if agent modal is present
if (strpos($content, 'prompt-agent-modal') !== false) {
    echo "✓ Agent modal HTML is present in the generate page\n";
} else {
    echo "✗ Agent modal HTML is NOT found in the generate page\n";
}

// Check for specific modal elements
$modalElements = [
    'id="prompt-agent-modal"',
    'id="agentMessages"',
    'id="suggestedPromptsList"',
    'id="enhancePromptBtn"'
];

foreach ($modalElements as $element) {
    if (strpos($content, $element) !== false) {
        echo "✓ Found: $element\n";
    } else {
        echo "✗ Missing: $element\n";
    }
}

// Output first 500 characters for debugging
echo "\n--- First 500 characters of page content ---\n";
echo substr($content, 0, 500) . "\n";
echo "--- End of content preview ---\n";
?>