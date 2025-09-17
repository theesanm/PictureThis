<?php
// test_agent_session.php - Test agent session creation with proper authentication

// Start session and simulate logged-in user
session_start();
$_SESSION['user'] = [
    'id' => 1, // Use the actual user ID from database
    'email' => 'admin@picturethis.com',
    'credits' => 44
];

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/utils/CSRF.php';
require_once __DIR__ . '/src/controllers/PromptAgentController.php';

// Generate and store CSRF token in session
$csrfToken = CSRF::generateToken();

// Create JSON payload
$payload = json_encode(['prompt' => 'A beautiful sunset over mountains']);

// Create a temporary file to simulate php://input
$tempFile = tempnam(sys_get_temp_dir(), 'php_input');
file_put_contents($tempFile, $payload);

// Redirect php://input to our temp file
$originalStdin = fopen('php://stdin', 'r');
fclose(STDIN);
define('STDIN', fopen($tempFile, 'r'));

// Set up headers for CSRF validation
$_SERVER['HTTP_X_CSRF_TOKEN'] = $csrfToken;
$_POST['csrf_token'] = $csrfToken;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capture output
ob_start();

// Create controller and test
$controller = new PromptAgentController();
$controller->startSession();

$output = ob_get_clean();

// Now we can output our test results
echo "Generated CSRF token: $csrfToken\n";
echo "Session CSRF token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "\n";

// Test CSRF validation
$csrfValid = CSRF::validateRequest();
echo "CSRF validation result: " . ($csrfValid ? 'VALID' : 'INVALID') . "\n";
echo "POST csrf_token: " . ($_POST['csrf_token'] ?? 'NOT SET') . "\n";
echo "SERVER HTTP_X_CSRF_TOKEN: " . ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? 'NOT SET') . "\n";
echo "Session csrf_token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "\n";
echo "Payload sent: $payload\n\n";

echo "Agent session creation test result:\n";
echo $output . "\n";

// Check if session was created
require_once __DIR__ . '/src/lib/db.php';
$pdo = get_db();
$stmt = $pdo->prepare('SELECT * FROM prompt_agent_sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
$stmt->execute([1]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if ($session) {
    echo "\nSession created successfully:\n";
    echo "- Session ID: " . $session['id'] . "\n";
    echo "- User ID: " . $session['user_id'] . "\n";
    echo "- Original Prompt: " . $session['original_prompt'] . "\n";
    echo "- Expires At: " . $session['expires_at'] . "\n";
    echo "- Credits Used: " . $session['total_credits_used'] . "\n";
} else {
    echo "\nNo session was created.\n";
}
?>