<?php
// Exact replica of what happens on the home page
echo "<!-- Exact Home Page Test -->\n";

// Enable error reporting to catch any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "<title>Home Page Test</title>\n";
echo "<style>body { background: lightblue; padding: 20px; }</style>\n";
echo "</head>\n";
echo "<body>\n";
echo "<h1 style='color: red;'>Testing Home Page Loading...</h1>\n";

// Step 1: Test database connection (same as home.php)
echo "<h2>Step 1: Database Connection</h2>\n";
try {
    require_once __DIR__ . '/src/lib/db.php';
    $pdo = get_db();
    echo "<p style='color: green;'>✅ Database connected</p>\n";

    $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
    $imageCount = $pdo->query("SELECT COUNT(*) as count FROM images")->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $userCount['count'] ?? 0;
    $totalImages = $imageCount['count'] ?? 0;
    echo "<p>Users: $totalUsers, Images: $totalImages</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Step 2: Test header include
echo "<h2>Step 2: Header Include</h2>\n";
try {
    ob_start();
    include __DIR__ . '/src/views/header.php';
    $headerContent = ob_get_clean();
    echo "<p style='color: green;'>✅ Header included (" . strlen($headerContent) . " chars)</p>\n";
    // Don't output the header content to avoid duplicate HTML
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Header error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Step 3: Test home content include
echo "<h2>Step 3: Home Content Include</h2>\n";
try {
    ob_start();
    include __DIR__ . '/src/views/home.php';
    $homeContent = ob_get_clean();
    echo "<p style='color: green;'>✅ Home content included (" . strlen($homeContent) . " chars)</p>\n";
    echo "<details><summary>Show home content preview</summary><pre>" . htmlspecialchars(substr($homeContent, 0, 500)) . "...</pre></details>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Home content error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Step 4: Test footer include
echo "<h2>Step 4: Footer Include</h2>\n";
try {
    ob_start();
    include __DIR__ . '/src/views/footer.php';
    $footerContent = ob_get_clean();
    echo "<p style='color: green;'>✅ Footer included (" . strlen($footerContent) . " chars)</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Footer error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h2>Summary</h2>\n";
echo "<p>If all steps are green, the home page should work. Total content length: " . (strlen($headerContent ?? '') + strlen($homeContent ?? '') + strlen($footerContent ?? '')) . " characters</p>\n";

echo "<h2>Debug Info</h2>\n";
echo "<p>PHP Version: " . phpversion() . "</p>\n";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>\n";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>\n";

echo "</body>\n";
echo "</html>\n";
?>
