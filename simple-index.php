<?php
// Simple test index.php
echo "<h1>PictureThis - Simple Test</h1>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>APP_ENV: " . (getenv('APP_ENV') ?: 'NOT SET') . "</p>";
?>