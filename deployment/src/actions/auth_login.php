<?php
require_once __DIR__ . '/../../lib/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $_SESSION['auth_error'] = 'Email and password are required';
    header('Location: /login');
    exit;
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Login success
        $_SESSION['user'] = [
            'id' => $user['id'],
            'fullName' => $user['full_name'],
            'email' => $user['email']
        ];
        header('Location: /dashboard');
        exit;
    } else {
        $_SESSION['auth_error'] = 'Invalid credentials';
        header('Location: /login');
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['auth_error'] = 'An error occurred';
    header('Location: /login');
    exit;
}
