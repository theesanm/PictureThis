<?php
// Test the exact same routing logic as index.php
echo "<h1>Routing Test</h1>";

// Replicate the exact routing logic from index.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "<p>Current path: '$path'</p>";

// Test the home page condition
if ($path === '/' || $path === '') {
    echo "<p style='color: green;'>✅ Home page route matched</p>";

    // Test loading HomeController
    try {
        require_once __DIR__ . '/src/controllers/HomeController.php';
        echo "<p style='color: green;'>✅ HomeController loaded</p>";

        $ctrl = new HomeController();
        echo "<p style='color: green;'>✅ HomeController instantiated</p>";

        // Instead of calling index(), let's test the components separately
        echo "<h2>Testing Components:</h2>";

        // Test header
        echo "<h3>Header:</h3>";
        ob_start();
        include __DIR__ . '/src/views/header.php';
        $header = ob_get_clean();
        echo "<p style='color: green;'>✅ Header loaded (" . strlen($header) . " chars)</p>";

        // Test home content
        echo "<h3>Home Content:</h3>";
        ob_start();
        include __DIR__ . '/src/views/home.php';
        $home = ob_get_clean();
        echo "<p style='color: green;'>✅ Home content loaded (" . strlen($home) . " chars)</p>";

        // Test footer
        echo "<h3>Footer:</h3>";
        ob_start();
        include __DIR__ . '/src/views/footer.php';
        $footer = ob_get_clean();
        echo "<p style='color: green;'>✅ Footer loaded (" . strlen($footer) . " chars)</p>";

        echo "<h2>Complete Output:</h2>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<strong>Total content length: " . (strlen($header) + strlen($home) + strlen($footer)) . " characters</strong>";
        echo "</div>";

    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

} else {
    echo "<p style='color: orange;'>⚠️  Home page route NOT matched</p>";
    echo "<p>Path was: '$path'</p>";
}

echo "<h2>Server Info:</h2>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
?>
