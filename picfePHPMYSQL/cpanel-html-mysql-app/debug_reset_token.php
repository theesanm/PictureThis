<?php
// Debug script to test password reset token validation
require_once __DIR__ . '/src/lib/db.php';
require_once __DIR__ . '/src/lib/timezone.php';

// Test token (replace with actual token from email)
$testToken = isset($_GET['token']) ? $_GET['token'] : '';

if (!$testToken) {
    echo "<h1>Password Reset Token Debug</h1>";
    echo "<p>Usage: ?token=YOUR_TOKEN_HERE</p>";
    echo "<p>Replace YOUR_TOKEN_HERE with the actual token from the reset email.</p>";
    exit;
}

try {
    $pdo = get_db();

    // Check if token exists
    $stmt = $pdo->prepare('SELECT id, email, reset_password_expires FROM users WHERE reset_password_token = ? LIMIT 1');
    $stmt->execute([$testToken]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h1>Token Debug Results</h1>";
    echo "<h2>Token: " . htmlspecialchars($testToken) . "</h2>";

    if (!$user) {
        echo "<p style='color: red;'><strong>ERROR:</strong> Token not found in database!</p>";
        echo "<p>Possible causes:</p>";
        echo "<ul>";
        echo "<li>Token was already used and cleared</li>";
        echo "<li>Token was never stored properly</li>";
        echo "<li>Token in URL doesn't match stored token</li>";
        echo "</ul>";

        // Show all users with reset tokens
        echo "<h3>All users with reset tokens:</h3>";
        $allStmt = $pdo->query('SELECT id, email, reset_password_token, reset_password_expires FROM users WHERE reset_password_token IS NOT NULL');
        $allUsers = $allStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($allUsers)) {
            echo "<p>No users have reset tokens.</p>";
        } else {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Email</th><th>Token (first 10 chars)</th><th>Expires</th></tr>";
            foreach ($allUsers as $u) {
                echo "<tr>";
                echo "<td>" . $u['id'] . "</td>";
                echo "<td>" . htmlspecialchars($u['email']) . "</td>";
                echo "<td>" . htmlspecialchars(substr($u['reset_password_token'], 0, 10)) . "...</td>";
                echo "<td>" . $u['reset_password_expires'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: green;'><strong>SUCCESS:</strong> Token found for user ID: " . $user['id'] . "</p>";
        echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
        echo "<p>Expires: " . $user['reset_password_expires'] . "</p>";

        // Check if token is expired (with grace period)
        $isExpired = is_expired($user['reset_password_expires'], TOKEN_GRACE_PERIOD_MINUTES);
        $timeRemaining = get_time_remaining($user['reset_password_expires']);

        if ($isExpired) {
            echo "<p style='color: red;'><strong>EXPIRED:</strong> Token expired at " . format_datetime_for_user($user['reset_password_expires']) . "</p>";
        } else {
            echo "<p style='color: green;'><strong>VALID:</strong> Token is still valid (" . $timeRemaining . " remaining)</p>";
        }

        // Test the exact query used in the controller (with grace period)
        echo "<h3>Controller Query Test (with " . TOKEN_GRACE_PERIOD_MINUTES . " min grace period):</h3>";
        $controllerStmt = $pdo->prepare('SELECT id, reset_password_expires FROM users WHERE reset_password_token = ? LIMIT 1');
        $controllerStmt->execute([$testToken]);
        $controllerUser = $controllerStmt->fetch(PDO::FETCH_ASSOC);

        if ($controllerUser && !is_expired($controllerUser['reset_password_expires'], TOKEN_GRACE_PERIOD_MINUTES)) {
            echo "<p style='color: green;'><strong>CONTROLLER QUERY SUCCESS:</strong> Token would be accepted by controller.</p>";
        } else {
            echo "<p style='color: red;'><strong>CONTROLLER QUERY FAILED:</strong> Token would be rejected by controller.</p>";
            echo "<p>This means either the token doesn't match exactly or it's expired (including grace period).</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>DATABASE ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='" . $_SERVER['PHP_SELF'] . "'>Test another token</a></p>";
?>