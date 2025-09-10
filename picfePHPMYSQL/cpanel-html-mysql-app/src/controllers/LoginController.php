<?php
class LoginController {
    public function index() {
        // Handle POST - authenticate
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $_SESSION['auth_error'] = 'Invalid credentials';
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
                error_log($e->getMessage());
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
