<?php
require_once __DIR__ . '/../../lib/db.php';

$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';

if (!$fullName || !$email || !$password || !$confirm) {
    $_SESSION['auth_error'] = 'All fields are required';
    header('Location: /register');
    exit;
}
if ($password !== $confirm) {
    $_SESSION['auth_error'] = 'Passwords do not match';
    header('Location: /register');
    exit;
}
if (strlen($password) < 8) {
    $_SESSION['auth_error'] = 'Password must be at least 8 characters';
    header('Location: /register');
    exit;
}

try {
    $pdo = get_db();
    // Check existing email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['auth_error'] = 'Email already registered';
        header('Location: /register');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$fullName, $email, $hash]);

    $_SESSION['auth_success'] = 'Registration successful. Please login.';
    header('Location: /login');
    exit;
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['auth_error'] = 'An error occurred during registration';
    header('Location: /register');
    exit;
}
