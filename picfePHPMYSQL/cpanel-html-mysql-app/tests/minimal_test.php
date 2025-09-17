<?php
/**
 * Minimal diagnostics test
 * Ultra-simple version to isolate 500 error
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Minimal Diagnostics Test</title>
</head>
<body>
    <h1>Minimal Diagnostics Test</h1>
    <p>PHP Version: " . PHP_VERSION . "</p>
    <p>Current Time: " . date('Y-m-d H:i:s') . "</p>
    <p>Current Directory: " . __DIR__ . "</p>
    <p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>
</body>
</html>";
?>