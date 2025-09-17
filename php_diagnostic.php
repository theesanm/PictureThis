<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title>PHP Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <h1>🔍 PHP Diagnostic for cPanel</h1>

    <div class='section success'>
        <h3>✅ Basic PHP Test</h3>
        <p>PHP Version: " . PHP_VERSION . "</p>
        <p>Current Directory: " . __DIR__ . "</p>
        <p>Working Directory: " . getcwd() . "</p>
    </div>";

$php_class = version_compare(PHP_VERSION, '8.0.0', '>=') ? 'success' : 'error';
$php_status = version_compare(PHP_VERSION, '8.0.0', '>=') ? '✅ PASS' : '❌ FAIL';

echo "
    <div class='section $php_class'>
        <h3>PHP Version Check</h3>
        <p>Current PHP Version: " . PHP_VERSION . "</p>
        <p>Required: PHP 8.0+</p>
        <p>Status: $php_status</p>
    </div>";

$write_class = is_writable('.') ? 'success' : 'error';
$write_status = is_writable('.') ? '✅ YES' : '❌ NO';

echo "
    <div class='section $write_class'>
        <h3>Directory Permissions</h3>
        <p>Current Directory Writable: $write_status</p>
    </div>

    <div class='section'>
        <h3>Directory Contents</h3>
        <pre>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $type = is_dir($file) ? '[DIR]' : '[FILE]';
        echo "$type $file\n";
    }
}
echo "</pre>
    </div>

    <div class='section'>
        <h3>GitHub Folder Check</h3>
        <p>GitHub folder exists: " . (is_dir('github') ? '✅ YES' : '❌ NO') . "</p>";
if (is_dir('github')) {
    echo "<p>GitHub contents:</p><pre>";
    $github_files = scandir('github');
    foreach ($github_files as $file) {
        if ($file !== '.' && $file !== '..') {
            $path = 'github/' . $file;
            $type = is_dir($path) ? '[DIR]' : '[FILE]';
            echo "$type $file\n";
        }
    }
    echo "</pre>";
}
echo "
    </div>

    <div class='section'>
        <h3>Next Steps</h3>
        <p>If you see this page, PHP is working. The issue might be:</p>
        <ul>
            <li>Syntax error in web_install.php</li>
            <li>Missing PHP extensions</li>
            <li>File permissions issue</li>
            <li>Corrupted file upload</li>
        </ul>
        <p><strong>To fix web_install.php:</strong></p>
        <ol>
            <li>Download the corrected web_install.php from the repository</li>
            <li>Upload it again to your cPanel root directory</li>
            <li>Make sure file permissions are set to 644</li>
        </ol>
    </div>
</body>
</html>";
?>