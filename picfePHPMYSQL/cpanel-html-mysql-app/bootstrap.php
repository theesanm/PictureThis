<?php
/**
 * Test Bootstrap File
 * Sets up the testing environment for all tests
 */

// Define test environment
define('TESTING', true);
if (!defined('IS_PRODUCTION')) {
    define('IS_PRODUCTION', false);
}

// Include the main configuration
require_once __DIR__ . '/../config/config.php';

// Include database configuration
require_once __DIR__ . '/../config/development.php';

// Include test utilities
require_once __DIR__ . '/utils/TestHelper.php';
require_once __DIR__ . '/utils/DatabaseTestHelper.php';

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up test database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Store PDO instance globally for tests
    $GLOBALS['test_pdo'] = $pdo;

} catch (PDOException $e) {
    die("Test database connection failed: " . $e->getMessage());
}

// Clean up function for tests
function cleanupTestData() {
    global $pdo;
    if ($pdo) {
        try {
            // Clean up test data - only if tables exist
            $pdo->exec("DELETE FROM prompt_agent_messages WHERE session_id LIKE 'test_%'");
            $pdo->exec("DELETE FROM prompt_agent_sessions WHERE id LIKE 'test_%'");
            $pdo->exec("UPDATE users SET current_agent_session_id = NULL WHERE id LIKE 'test_%'");
        } catch (PDOException $e) {
            // Silently ignore if tables don't exist yet
            if (strpos($e->getMessage(), "Base table or view not found") === false) {
                error_log("Cleanup error: " . $e->getMessage());
            }
        }
    }
}

// Register cleanup on shutdown
register_shutdown_function('cleanupTestData');

// Only show initialization message for pure CLI (not when running test files directly)
if (php_sapi_name() === 'cli' && basename($_SERVER['SCRIPT_NAME'] ?? '') === 'bootstrap.php') {
    echo "✅ Test environment initialized successfully\n";
}