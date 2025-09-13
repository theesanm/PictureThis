<?php
class LoginController {
    public function index() {
        // Handle POST - authenticate
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            require_once __DIR__ . '/../lib/db.php';

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                $_SESSION['auth_error'] = 'Email and password required';
                header('Location: /login');
                exit;
            }

            try {
                $pdo = get_db();
                $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, email_verified FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $_SESSION['auth_error'] = 'Invalid credentials';
                    header('Location: /login');
                    exit;
                }

                // Check if email is verified
                if (!$user['email_verified']) {
                    $_SESSION['auth_error'] = 'Please verify your email address before logging in. Check your email for the verification link.';
                    header('Location: /login');
                    exit;
                }

                // Auth success
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'fullName' => $user['full_name'],
                    'email' => $user['email']
                ];
                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
                    error_log('[LOGIN] exception: ' . $e->getMessage());
                    // Diagnostics for session state on exception
                    if (session_status() !== PHP_SESSION_NONE) {
                        error_log('[LOGIN] exception session_id=' . session_id() . ' save_path=' . ini_get('session.save_path'));
                    } else {
                        error_log('[LOGIN] exception session not started');
                    }
                }
                $_SESSION['auth_error'] = 'An error occurred during login';
                header('Location: /login');
                exit;
            }
        }

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/auth/login.php';
        include __DIR__ . '/../views/footer.php';
    }

    public function logout() {
        // Clear the session
        session_start();
        session_unset();
        session_destroy();
        
        // Redirect to login page
        header('Location: /login');
        exit;
    }
}
