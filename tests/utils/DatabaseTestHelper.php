<?php
/**
 * Database Test Helper Class
 * Provides database-specific testing utilities
 */

class DatabaseTestHelper {

    private static $pdo;

    /**
     * Initialize database connection
     */
    public static function init($pdo = null) {
        if ($pdo) {
            self::$pdo = $pdo;
        } elseif (isset($GLOBALS['test_pdo'])) {
            self::$pdo = $GLOBALS['test_pdo'];
        } else {
            throw new Exception("Database connection not available");
        }
    }

    /**
     * Get database connection
     */
    public static function getConnection() {
        if (!self::$pdo) {
            self::init();
        }
        return self::$pdo;
    }

    /**
     * Create a test user
     */
    public static function createTestUser($overrides = []) {
        $pdo = self::getConnection();

        $userData = array_merge([
            'email' => 'test_' . uniqid() . '@example.com',
            'password_hash' => password_hash('testpass', PASSWORD_DEFAULT),
            'credits' => 100,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $overrides);

        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, credits, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userData['email'],
            $userData['password_hash'],
            $userData['credits'],
            $userData['created_at'],
            $userData['updated_at']
        ]);

        $userData['id'] = $pdo->lastInsertId();
        return $userData;
    }

    /**
     * Create a test agent session
     */
    public static function createTestAgentSession($userId, $overrides = []) {
        $pdo = self::getConnection();

        $sessionData = array_merge([
            'id' => TestHelper::generateTestSessionId(),
            'user_id' => $userId,
            'original_prompt' => 'Test prompt for enhancement',
            'session_status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
            'total_llm_calls' => 0,
            'total_credits_used' => 0
        ], $overrides);

        $stmt = $pdo->prepare("
            INSERT INTO prompt_agent_sessions
            (id, user_id, original_prompt, session_status, expires_at, total_llm_calls, total_credits_used)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $sessionData['id'],
            $sessionData['user_id'],
            $sessionData['original_prompt'],
            $sessionData['session_status'],
            $sessionData['expires_at'],
            $sessionData['total_llm_calls'],
            $sessionData['total_credits_used']
        ]);

        return $sessionData;
    }

    /**
     * Create a test agent message
     */
    public static function createTestAgentMessage($sessionId, $overrides = []) {
        $pdo = self::getConnection();

        $messageData = array_merge([
            'session_id' => $sessionId,
            'message_type' => 'user',
            'content' => 'Test message content',
            'credits_used' => 1
        ], $overrides);

        $stmt = $pdo->prepare("
            INSERT INTO prompt_agent_messages
            (session_id, message_type, content, credits_used)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $messageData['session_id'],
            $messageData['message_type'],
            $messageData['content'],
            $messageData['credits_used']
        ]);

        $messageData['id'] = $pdo->lastInsertId();
        return $messageData;
    }

    /**
     * Clean up test data
     */
    public static function cleanupTestData() {
        $pdo = self::getConnection();

        // Delete test messages first (due to foreign key constraints)
        $pdo->exec("DELETE FROM prompt_agent_messages WHERE session_id LIKE 'test_%'");

        // Delete test sessions
        $pdo->exec("DELETE FROM prompt_agent_sessions WHERE id LIKE 'test_%'");

        // Clean up test users
        $pdo->exec("DELETE FROM users WHERE email LIKE 'test_%'");
    }

    /**
     * Get table row count
     */
    public static function getTableRowCount($tableName) {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $tableName");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Check if table exists
     */
    public static function tableExists($tableName) {
        $pdo = self::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM $tableName LIMIT 1");
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if column exists in table
     */
    public static function columnExists($tableName, $columnName) {
        $pdo = self::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT $columnName FROM $tableName LIMIT 1");
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Execute a SQL file
     */
    public static function executeSqlFile($filePath) {
        $pdo = self::getConnection();

        if (!file_exists($filePath)) {
            throw new Exception("SQL file not found: $filePath");
        }

        $sql = file_get_contents($filePath);
        $pdo->exec($sql);
    }
}