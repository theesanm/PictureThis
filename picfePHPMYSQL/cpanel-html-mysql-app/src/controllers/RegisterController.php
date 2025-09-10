<?php
class RegisterController {
    public function index() {
        // Handle POST - create user
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../lib/db.php';

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

                $userId = $pdo->lastInsertId();
                // Log the user in
                $_SESSION['user'] = [
                    'id' => $userId,
                    'fullName' => $fullName,
                    'email' => $email
                ];
                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                error_log($e->getMessage());
                $_SESSION['auth_error'] = 'An error occurred during registration';
                header('Location: /register');
                exit;
            }
        }

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/auth/register.php';
        include __DIR__ . '/../views/footer.php';
    }
}
