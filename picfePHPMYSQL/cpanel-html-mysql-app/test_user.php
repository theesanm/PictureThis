<?php
// test_user.php - one-off debug tool to list users from the database.
// Place this file in the site root and open in a browser. Remove after use.
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "PictureThis - test_user.php\n";
echo "========================\n\n";

// Load configuration and try to reuse the app DB helper if present
try {
    require_once __DIR__ . '/config/config.php';

    if (file_exists(__DIR__ . '/src/lib/db.php')) {
        require_once __DIR__ . '/src/lib/db.php';
        $pdo = get_db();
    } else {
        // Fallback: build PDO from config constants
        $host = DB_HOST;
        $port = null;
        if (strpos($host, ':') !== false) {
            list($host, $port) = explode(':', $host, 2);
        }
        if ($port) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, DB_NAME);
        } else {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, DB_NAME);
        }
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    $stmt = $pdo->query('SELECT id, full_name, email, credits, created_at, password_hash FROM users ORDER BY id ASC');
    $rows = $stmt->fetchAll();

    echo "Users found: " . count($rows) . "\n\n";
    foreach ($rows as $r) {
        // Mask the password hash to avoid leaking full hashes
        $mask = is_string($r['password_hash']) ? (substr($r['password_hash'], 0, 6) . '... len=' . strlen($r['password_hash'])) : 'NULL';
        echo "ID: " . $r['id'] . "\n";
        echo "Email: " . ($r['email'] ?? '') . "\n";
        echo "Name: " . ($r['full_name'] ?? '') . "\n";
        echo "Credits: " . ($r['credits'] ?? '0') . "\n";
        echo "Created: " . ($r['created_at'] ?? '') . "\n";
        echo "Password hash: " . $mask . "\n";
        echo "------------------------\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nNOTE: Remove this file from the server after use.\n";

?>
