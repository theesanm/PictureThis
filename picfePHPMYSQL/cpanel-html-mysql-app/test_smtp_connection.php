<?php
// Test basic SMTP connection
echo "<h1>SMTP Connection Test</h1>";

// Test parameters
$smtpHost = getenv('SMTP_HOST') ?: 'mail.cfox.co.za';
$smtpPort = getenv('SMTP_PORT') ?: 587;
$smtpUsername = getenv('SMTP_USERNAME') ?: 'picturethis@cfox.co.za';
$smtpPassword = getenv('SMTP_PASSWORD') ?: '';

echo "<h2>Connection Parameters:</h2>";
echo "<ul>";
echo "<li>Host: $smtpHost</li>";
echo "<li>Port: $smtpPort</li>";
echo "<li>Username: $smtpUsername</li>";
echo "<li>Password Set: " . (empty($smtpPassword) ? 'No' : 'Yes') . "</li>";
echo "</ul>";

// Test basic socket connection
echo "<h2>Testing Socket Connection:</h2>";

$socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 10);

if (!$socket) {
    echo "<p style='color: red;'>❌ Socket connection failed: $errstr ($errno)</p>";
    
    // Try SSL connection
    echo "<p>Trying SSL connection...</p>";
    $socket = fsockopen("ssl://$smtpHost", 465, $errno, $errstr, 10);
    
    if (!$socket) {
        echo "<p style='color: red;'>❌ SSL connection also failed: $errstr ($errno)</p>";
    } else {
        echo "<p style='color: green;'>✅ SSL connection successful on port 465</p>";
        fclose($socket);
    }
} else {
    echo "<p style='color: green;'>✅ Socket connection successful on port $smtpPort</p>";
    
    // Read server greeting
    $response = fgets($socket, 515);
    echo "<p>Server greeting: <code>" . htmlspecialchars(trim($response)) . "</code></p>";
    
    // Send QUIT
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
}

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Update your .htaccess file with the actual SMTP password</li>";
echo "<li>Try the full email test at <a href='test_smtp_email.php'>test_smtp_email.php</a></li>";
echo "<li>Check the debug logs for detailed SMTP communication</li>";
echo "</ol>";
