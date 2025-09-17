<?php
// Test SMTP email functionality
require_once 'config/config.php';
require_once 'src/lib/EmailService.php';

echo "<h1>SMTP Email Test</h1>";

// Test SMTP configuration loading
$emailService = new EmailService();
echo "<p>EmailService loaded successfully</p>";

// Display current SMTP configuration (without password)
echo "<h2>SMTP Configuration:</h2>";
echo "<ul>";
echo "<li>SMTP Host: " . getenv('SMTP_HOST') . "</li>";
echo "<li>SMTP Username: " . getenv('SMTP_USERNAME') . "</li>";
echo "<li>SMTP Port: " . getenv('SMTP_PORT') . "</li>";
echo "<li>From Email: " . getenv('FROM_EMAIL') . "</li>";
echo "<li>Password Set: " . (getenv('SMTP_PASSWORD') ? 'Yes' : 'No') . "</li>";
echo "</ul>";

// Test email sending
if (isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    echo "<h2>Sending Test Email...</h2>";
    
    $result = $emailService->sendVerificationEmail($testEmail, 'Test User', 'test-token-123');
    
    if ($result) {
        echo "<p style='color: green;'>✓ Email sent successfully! Check your inbox and logs.</p>";
    } else {
        echo "<p style='color: red;'>✗ Email failed to send. Check the debug logs for details.</p>";
    }
    
    // Show debug log if it exists
    $logFile = __DIR__ . '/logs/email_debug.log';
    if (file_exists($logFile)) {
        echo "<h3>Debug Log:</h3>";
        echo "<pre>" . htmlspecialchars(file_get_contents($logFile)) . "</pre>";
    }
}
?>

<form method="post">
    <h2>Test Email Sending</h2>
    <label for="test_email">Test Email Address:</label>
    <input type="email" id="test_email" name="test_email" value="theesanm@gmail.com" required>
    <button type="submit">Send Test Email</button>
</form>

<p><strong>Instructions:</strong></p>
<ol>
    <li>Update your .htaccess file with the actual SMTP password for picturethis@cfox.co.za</li>
    <li>Enter a test email address above and click "Send Test Email"</li>
    <li>Check your email and the debug logs below</li>
    <li>If emails still fail, check that the SMTP credentials are correct</li>
</ol>
