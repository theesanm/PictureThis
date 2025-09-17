<?php
// Create admin user script
require_once __DIR__ . '/src/lib/db.php';

try {
    $pdo = get_db();

    // Check if admin user already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute(['admin@picturethis.com']);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo "Admin user already exists!\n";
        echo "Email: admin@picturethis.com\n";
        echo "ID: " . $existingUser['id'] . "\n";
        exit;
    }

    // Create admin user
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $credits = 100; // Give admin some credits

    $stmt = $pdo->prepare('
        INSERT INTO users (
            full_name,
            email,
            password_hash,
            credits,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, NOW(), NOW())
    ');

    $stmt->execute([
        'Admin User',
        'admin@picturethis.com',
        $hashedPassword,
        $credits
    ]);

    $userId = $pdo->lastInsertId();

    echo "✅ Admin user created successfully!\n";
    echo "Email: admin@picturethis.com\n";
    echo "Password: admin123\n";
    echo "Credits: $credits\n";
    echo "User ID: $userId\n";

} catch (Exception $e) {
    echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
}
?>
