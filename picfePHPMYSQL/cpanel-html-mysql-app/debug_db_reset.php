<?php
// Database connection test and reset token inspection
require_once __DIR__ . '/src/lib/db.php';

echo "<h1>Database Connection Test</h1>";

try {
    $pdo = get_db();
    echo "<p style='color: green;'>✓ Database connection successful</p>";

    // Check users table structure
    echo "<h2>Users Table Structure</h2>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Check for users with reset tokens
    echo "<h2>Users with Reset Tokens</h2>";
    $resetStmt = $pdo->query("SELECT id, email, reset_password_token, reset_password_expires FROM users WHERE reset_password_token IS NOT NULL");
    $resetUsers = $resetStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($resetUsers)) {
        echo "<p>No users currently have reset tokens.</p>";
        echo "<p>This could mean:</p>";
        echo "<ul>";
        echo "<li>No password reset requests have been made</li>";
        echo "<li>Tokens are being cleared after use</li>";
        echo "<li>Tokens are expiring quickly</li>";
        echo "</ul>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Email</th><th>Token (first 20 chars)</th><th>Expires</th><th>Status</th></tr>";
        foreach ($resetUsers as $user) {
            $status = 'Valid';
            $now = new DateTime();
            $expires = new DateTime($user['reset_password_expires']);

            if ($now > $expires) {
                $status = 'Expired';
            }

            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($user['reset_password_token'], 0, 20)) . "...</td>";
            echo "<td>" . $user['reset_password_expires'] . "</td>";
            echo "<td>" . $status . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Show total users
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $total = $countStmt->fetch(PDO::FETCH_ASSOC);
    echo "<h2>Total Users: " . $total['total'] . "</h2>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>