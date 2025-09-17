<?php
// Debug script for agent 500 error
// Run this on your server to get detailed error information

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_agent.log');

echo "=== Agent Debug Test ===\n\n";

// Test 1: Check PHP environment
echo "1. PHP Environment:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   Current directory: " . __DIR__ . "\n";
echo "   Script filename: " . (__SERVER['SCRIPT_FILENAME'] ?? 'NOT_SET') . "\n\n";

// Test 2: Load configuration
echo "2. Configuration Test:\n";
try {
    require_once 'config/config.php';
    echo "   ✓ Config loaded successfully\n";
    echo "   IS_PRODUCTION: " . (defined('IS_PRODUCTION') ? (IS_PRODUCTION ? 'TRUE' : 'FALSE') : 'NOT_DEFINED') . "\n";
    echo "   OPENROUTER_API_KEY length: " . strlen(OPENROUTER_API_KEY ?? '') . "\n";
    echo "   AGENT_SESSION_TIMEOUT_MINUTES: " . (defined('AGENT_SESSION_TIMEOUT_MINUTES') ? AGENT_SESSION_TIMEOUT_MINUTES : 'NOT_DEFINED') . "\n";
} catch (Exception $e) {
    echo "   ✗ Config error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Database connection
echo "3. Database Test:\n";
try {
    require_once 'src/lib/db.php';
    $pdo = get_db();
    if ($pdo) {
        echo "   ✓ Database connection successful\n";

        // Test required tables
        $tables = ['users', 'settings', 'prompt_agent_sessions', 'prompt_agent_messages'];
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "   ✓ Table `$table`: " . $result['count'] . " records\n";
            } catch (Exception $e) {
                echo "   ✗ Table `$table` error: " . $e->getMessage() . "\n";
            }
        }

        // Test settings table structure
        try {
            $stmt = $pdo->query("DESCRIBE settings");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "   ✓ Settings table columns: " . implode(', ', array_column($columns, 'Field')) . "\n";
        } catch (Exception $e) {
            echo "   ✗ Settings table structure error: " . $e->getMessage() . "\n";
        }

    } else {
        echo "   ✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Session test
echo "4. Session Test:\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "   ✓ Session started\n";
} else {
    echo "   ✓ Session already active\n";
}

if (isset($_SESSION['user'])) {
    echo "   ✓ User session exists: " . json_encode($_SESSION['user']) . "\n";
} else {
    echo "   ✗ No user session found\n";
    // Simulate a user session for testing
    $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];
    echo "   ✓ Created test user session\n";
}
echo "\n";

// Test 5: CSRF test
echo "5. CSRF Test:\n";
try {
    require_once 'src/utils/CSRF.php';
    $csrf = new CSRF();
    $token = $csrf->generateToken();
    echo "   ✓ CSRF token generated: " . substr($token, 0, 10) . "...\n";

    // Test validation
    $_POST['csrf_token'] = $token;
    if (CSRF::validateRequest()) {
        echo "   ✓ CSRF validation passed\n";
    } else {
        echo "   ✗ CSRF validation failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ CSRF error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Agent controller test
echo "6. Agent Controller Test:\n";
try {
    require_once 'src/controllers/PromptAgentController.php';

    // Create a test request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['csrf_token'] = $token ?? '';
    $testInput = json_encode(['prompt' => 'test prompt']);

    // Simulate JSON input
    file_put_contents('php://input', $testInput);

    echo "   ✓ Agent controller loaded\n";
    echo "   ✓ Test request prepared\n";

} catch (Exception $e) {
    echo "   ✗ Agent controller error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Debug Complete ===\n";
echo "Check debug_agent.log for any additional PHP errors.\n";
?>