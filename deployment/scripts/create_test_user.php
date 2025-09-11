<?php
// Creates a test user for local dev and prints credentials
require_once __DIR__ . '/../src/lib/db.php';
$pdo = get_db();
$email = 'dev+test@example.com';
$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, credits, created_at) VALUES (?, ?, ?, 0, NOW())');
    $stmt->execute(['Dev Test', $email, $hash]);
    echo "Created user: {$email} / {$password}\n";
} catch (PDOException $e) {
    echo "Could not create user (maybe already exists): " . $e->getMessage() . "\n";
}
