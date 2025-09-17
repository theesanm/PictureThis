<?php
require_once __DIR__ . '/../../lib/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        // Diagnostic logging
        error_log('[AUTH_LOGIN] success session_id=' . session_id() . ' headers_sent=' . (headers_sent()?1:0) . ' save_path=' . ini_get('session.save_path'));
        foreach (headers_list() as $h) { error_log('[AUTH_LOGIN] header: ' . $h); }
        header('Location: /dashboard');
        exit;
    } else {
        $_SESSION['auth_error'] = 'Invalid credentials';
        error_log('[AUTH_LOGIN] invalid credentials for ' . $email . ' session_id=' . session_id() . ' save_path=' . ini_get('session.save_path'));
        header('Location: /login');
        exit;
    }
} catch (Exception $e) {
    error_log('[AUTH_LOGIN] exception: ' . $e->getMessage());
    $_SESSION['auth_error'] = 'An error occurred';
    if (session_status() !== PHP_SESSION_NONE) error_log('[AUTH_LOGIN] exception session_id=' . session_id() . ' save_path=' . ini_get('session.save_path'));
    header('Location: /login');
    exit;
}
