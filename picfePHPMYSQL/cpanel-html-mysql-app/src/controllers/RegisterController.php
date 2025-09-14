<?php
require_once __DIR__ . '/../utils/CSRF.php';

class RegisterController {
    public function index() {
        // Handle POST - create user
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!CSRF::validateRequest()) {
                $_SESSION['auth_error'] = 'Invalid request. Please try again.';
                header('Location: /register');
                exit;
            }

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

                // Generate verification token
                require_once __DIR__ . '/../lib/timezone.php';
                $verificationToken = bin2hex(random_bytes(32));
                $tokenExpiry = get_utc_now()->modify('+24 hours')->format('Y-m-d H:i:s');

                $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, email_verification_token, email_verification_expires, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$fullName, $email, $hash, $verificationToken, $tokenExpiry]);

                $userId = $pdo->lastInsertId();

                // Send verification email
                require_once __DIR__ . '/../lib/EmailService.php';
                $emailService = new EmailService();

                if ($emailService->sendVerificationEmail($email, $fullName, $verificationToken)) {
                    // Regenerate CSRF token after successful registration
                    CSRF::regenerateToken();

                    // Redirect to check email page with email parameter
                    header('Location: /check-email?email=' . urlencode($email));
                    exit;
                } else {
                    $_SESSION['auth_error'] = 'Registration successful but verification email could not be sent. Please contact support.';
                    header('Location: /login');
                    exit;
                }
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
