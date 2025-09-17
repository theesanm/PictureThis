<?php
// Time zone and token expiry test
require_once __DIR__ . '/src/lib/db.php';
require_once __DIR__ . '/src/lib/timezone.php';

echo "<h1>Time Zone and Token Expiry Test</h1>";

try {
    $pdo = get_db();

    // Check PHP time
    echo "<h2>PHP Server Time (UTC)</h2>";
    echo "<p>Current PHP time: " . get_utc_now_string() . "</p>";
    echo "<p>PHP timezone: " . date_default_timezone_get() . "</p>";
    echo "<p>Token expiry (UTC): " . get_utc_now()->modify('+1 hour')->format('Y-m-d H:i:s') . "</p>";

    // Check MySQL time
    echo "<h2>MySQL Server Time</h2>";
    $timeStmt = $pdo->query("SELECT NOW() as mysql_now, @@session.time_zone as mysql_tz");
    $timeResult = $timeStmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Current MySQL time: " . $timeResult['mysql_now'] . "</p>";
    echo "<p>MySQL timezone: " . $timeResult['mysql_tz'] . "</p>";

    // Test token generation and validation
    echo "<h2>Token Generation Test</h2>";
    $testToken = bin2hex(random_bytes(32));
    $tokenExpiry = get_utc_now()->modify('+1 hour')->format('Y-m-d H:i:s');

    echo "<p>Generated token: " . htmlspecialchars(substr($testToken, 0, 20)) . "...</p>";
    echo "<p>Token expiry: " . $tokenExpiry . "</p>";

    // Insert test token
    $insertStmt = $pdo->prepare('INSERT INTO users (email, password_hash, reset_password_token, reset_password_expires) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE reset_password_token = ?, reset_password_expires = ?');
    $testEmail = 'test@example.com';
    $testHash = password_hash('test123', PASSWORD_DEFAULT);
    $insertStmt->execute([$testEmail, $testHash, $testToken, $tokenExpiry, $testToken, $tokenExpiry]);

    echo "<p>Test token inserted for email: " . $testEmail . "</p>";

    // Test validation query
    echo "<h2>Token Validation Test</h2>";
    $validateStmt = $pdo->prepare('SELECT id, reset_password_expires FROM users WHERE reset_password_token = ? LIMIT 1');
    $validateStmt->execute([$testToken]);
    $user = $validateStmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $isExpired = is_expired($user['reset_password_expires'], TOKEN_GRACE_PERIOD_MINUTES);
        if (!$isExpired) {
            echo "<p style='color: green;'>✓ Token validation successful (with " . TOKEN_GRACE_PERIOD_MINUTES . " minute grace period)</p>";
        } else {
            echo "<p style='color: red;'>✗ Token is expired</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Token not found</p>";
    }

    // Clean up test data
    $deleteStmt = $pdo->prepare('DELETE FROM users WHERE email = ?');
    $deleteStmt->execute([$testEmail]);
    echo "<p>Test data cleaned up</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>