<?php
// Minimal DB connection helper for the simple cPanel PHP app
require_once __DIR__ . '/../../config/config.php';

// Use PDO for the connection
try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $db = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In a real app you'd want better error handling
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}

function getDb()
{
    global $db;
    return $db;
}

// Backwards-compatible alias used by some action scripts
function get_db()
{
    return getDb();
}

?>
