<?php
// Simple test page to check if content is rendering
echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Test Page</title>";
echo "<style>body { background: white; color: black; font-family: Arial; }</style>";
echo "</head>";
echo "<body>";
echo "<h1>Test Page - Basic HTML</h1>";
echo "<p>If you can see this, basic HTML is working.</p>";

// Test database
try {
    require_once __DIR__ . '/src/lib/db.php';
    $pdo = get_db();
    echo "<p style='color: green;'>✅ Database connection works!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test file includes
if (file_exists(__DIR__ . '/src/views/header.php')) {
    echo "<p style='color: green;'>✅ Header file exists</p>";
} else {
    echo "<p style='color: red;'>❌ Header file missing</p>";
}

echo "<h2>Testing Home Page Content:</h2>";
echo "<div style='border: 1px solid black; padding: 10px; margin: 10px;'>";

// Try to include just the home content without header/footer
ob_start();
include __DIR__ . '/src/views/home.php';
$content = ob_get_clean();

if (!empty($content)) {
    echo "<p style='color: green;'>✅ Home content loaded successfully</p>";
    echo "<p>Content length: " . strlen($content) . " characters</p>";
    // Show first 500 characters
    echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>";
} else {
    echo "<p style='color: red;'>❌ Home content is empty</p>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>
