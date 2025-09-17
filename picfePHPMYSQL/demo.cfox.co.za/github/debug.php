<?php
// Debug script to check database and session issues
session_start();

echo "<h1>PictureThis Debug</h1>";
echo "<h2>Session Status:</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h2>Database Connection Test:</h2>";
try {
    require_once __DIR__ . '/src/lib/db.php';
    $pdo = get_db();
    echo "✅ Database connection successful!<br>";

    // Check if tables exist
    $tables = ['users', 'images'];
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' does NOT exist<br>";
        }
    }

    // Try to get user count
    $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
    echo "✅ Users count: " . ($userCount['count'] ?? 0) . "<br>";

    // Try to get image count
    $imageCount = $pdo->query("SELECT COUNT(*) as count FROM images")->fetch(PDO::FETCH_ASSOC);
    echo "✅ Images count: " . ($imageCount['count'] ?? 0) . "<br>";

} catch (Exception $e) {
    echo "❌ Database error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<h2>PHP Info:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

echo "<h2>File System Check:</h2>";
$filesToCheck = [
    'src/views/header.php',
    'src/views/home.php',
    'src/views/footer.php',
    'public/css/style.css'
];

foreach ($filesToCheck as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file does NOT exist<br>";
    }
}
?>
