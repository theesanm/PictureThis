<?php
// Minimal DB connection helper for the simple cPanel PHP app
// require_once __DIR__ . '/../../config/config.php'; // Removed to avoid duplicate loading and session ini_set errors

$db = null; // Global connection variable, initialized on first use

function get_db() {
    global $db;
    if (empty($db) || !($db instanceof PDO)) {
        try {
            error_log('[DB] Attempting connection - DB_HOST=' . (defined('DB_HOST') ? DB_HOST : 'UNDEFINED'));
            error_log('[DB] DB_USER=' . (defined('DB_USER') ? 'defined' : 'UNDEFINED'));
            error_log('[DB] DB_NAME=' . (defined('DB_NAME') ? DB_NAME : 'UNDEFINED'));
            
            // Support DB_HOST with optional port (e.g. "localhost:3306") to be resilient to different config styles
            $host = DB_HOST;
            $port = null;
            if (strpos(DB_HOST, ':') !== false) {
                list($host, $port) = explode(':', DB_HOST, 2);
            }

            if ($port) {
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, DB_NAME);
            } else {
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, DB_NAME);
            }
            error_log('[DB] DSN: ' . $dsn);
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $db = new PDO($dsn, DB_USER, DB_PASS, $options);
            error_log('[DB] PDO connection successful');
        } catch (PDOException $e) {
            error_log('[DB] PDO Exception: ' . $e->getMessage());
            http_response_code(500);
            echo "Database connection failed: " . htmlspecialchars($e->getMessage());
            exit;
        }
    }
    return $db;
}

// Backwards-compatible alias used by some action scripts
function getDb() {
    return get_db();
}

?>
