<?php
// Minimal DB connection helper for the simple cPanel PHP app

// Load config based on current directory context
if (file_exists(__DIR__ . '/config/config.php')) {
    // Running from cpanel directory - load cpanel config
    require_once __DIR__ . '/config/config.php';
} else {
    // Fallback to main config
    require_once __DIR__ . '/../../config/config.php';
}

$db = null; // Global connection variable, initialized on first use

function get_db() {
    global $db;
    if (empty($db) || !($db instanceof PDO)) {
        try {
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
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $db = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('[DB] PDO Exception: ' . $e->getMessage());
            // Return null instead of outputting error and exiting
            return null;
        }
    }
    return $db;
}

// Backwards-compatible alias used by some action scripts
function getDb() {
    return get_db();
}

?>
