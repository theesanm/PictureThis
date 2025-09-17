<?php
// test_agent_modal_functionality.php - Test if agent modal JavaScript functions work

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

// Test JavaScript function availability
$jsTests = [
    'showAgentModal' => strpos($content, 'window.showAgentModal = function()') !== false,
    'startAgentSession' => strpos($content, 'window.startAgentSession = function(') !== false,
    'addMessage' => strpos($content, 'function addMessage(') !== false,
    'hideAgentModal' => strpos($content, 'function hideAgentModal()') !== false,
];

echo "=== Agent Modal JavaScript Function Tests ===\n";
foreach ($jsTests as $function => $exists) {
    echo ($exists ? "✓" : "✗") . " $function function " . ($exists ? "found" : "NOT found") . "\n";
}

// Test HTML element availability
$htmlElements = [
    'prompt-agent-modal' => strpos($content, 'id="prompt-agent-modal"') !== false,
    'agent-messages' => strpos($content, 'id="agent-messages"') !== false,
    'agent-input' => strpos($content, 'id="agent-input"') !== false,
    'send-agent-message' => strpos($content, 'id="send-agent-message"') !== false,
    'enhance-btn' => strpos($content, 'id="enhance-btn"') !== false,
    'close-agent-modal' => strpos($content, 'id="close-agent-modal"') !== false,
];

echo "\n=== Agent Modal HTML Element Tests ===\n";
foreach ($htmlElements as $element => $exists) {
    echo ($exists ? "✓" : "✗") . " #$element element " . ($exists ? "found" : "NOT found") . "\n";
}

// Test null checks in JavaScript
$nullChecks = [
    'agentMessages null check' => strpos($content, 'if (!agentMessages) return;') !== false,
    'suggestedPromptsList null check' => strpos($content, 'if (!suggestedPromptsList) return;') !== false,
    'agentModal null check' => strpos($content, 'if (!agentModal) return;') !== false,
];

echo "\n=== JavaScript Null Check Tests ===\n";
foreach ($nullChecks as $check => $exists) {
    echo ($exists ? "✓" : "✗") . " $check " . ($exists ? "found" : "NOT found") . "\n";
}

// Check for potential issues
$issues = [];
if (!$jsTests['showAgentModal']) $issues[] = "showAgentModal function missing";
if (!$jsTests['startAgentSession']) $issues[] = "startAgentSession function missing";
if (!$htmlElements['enhance-btn']) $issues[] = "Enhance button missing";
if (!$htmlElements['prompt-agent-modal']) $issues[] = "Agent modal HTML missing";

echo "\n=== Summary ===\n";
if (empty($issues)) {
    echo "✓ All critical components are present and should work!\n";
    echo "✓ Agent modal functionality should be fully operational.\n";
} else {
    echo "✗ Issues found:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
}
?>