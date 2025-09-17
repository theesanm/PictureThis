<?php
// Test that mimics the HomeController exactly
echo "<h1>Testing HomeController Logic</h1>";

// Step 1: Include header
echo "<h2>Step 1: Including header.php</h2>";
try {
    include __DIR__ . '/src/views/header.php';
    echo "<p style='color: green;'>✅ Header included successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Header error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 2: Include home content
echo "<h2>Step 2: Including home.php</h2>";
try {
    include __DIR__ . '/src/views/home.php';
    echo "<p style='color: green;'>✅ Home content included successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Home content error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 3: Include footer
echo "<h2>Step 3: Including footer.php</h2>";
try {
    include __DIR__ . '/src/views/footer.php';
    echo "<p style='color: green;'>✅ Footer included successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Footer error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If all steps are green, the HomeController logic should work.</p>";
?>
