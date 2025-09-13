<?php
class PasswordResetController {
    public function forgot() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Handle POST - send reset email
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../lib/db.php';

            $email = trim($_POST['email'] ?? '');

            if (!$email) {
                $_SESSION['auth_error'] = 'Email address is required.';
                header('Location: /forgot-password');
                exit;
            }

            try {
                $pdo = get_db();

                // Find user with this email
                $stmt = $pdo->prepare('SELECT id, full_name FROM users WHERE email = ? AND email_verified = 1 LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    // Don't reveal if email exists or not for security
                    $_SESSION['auth_success'] = 'If an account with this email exists, a password reset link has been sent.';
                    header('Location: /login');
                    exit;
                }

                // Generate reset token
                require_once __DIR__ . '/../lib/timezone.php';
                $resetToken = bin2hex(random_bytes(32));
                $tokenExpiry = get_utc_now()->modify('+1 hour')->format('Y-m-d H:i:s');

                // Update user with reset token
                $updateStmt = $pdo->prepare('UPDATE users SET reset_password_token = ?, reset_password_expires = ? WHERE id = ?');
                $updateStmt->execute([$resetToken, $tokenExpiry, $user['id']]);

                // Send password reset email
                require_once __DIR__ . '/../lib/EmailService.php';
                $emailService = new EmailService();

                if ($emailService->sendPasswordResetEmail($email, $user['full_name'], $resetToken)) {
                    $_SESSION['auth_success'] = 'Password reset link sent! Please check your email.';
                } else {
                    $_SESSION['auth_error'] = 'Failed to send password reset email. Please try again later.';
                }

            } catch (Exception $e) {
                if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
                    error_log('Password reset request error: ' . $e->getMessage());
                }
                $_SESSION['auth_error'] = 'An error occurred. Please try again.';
            }

            header('Location: /login');
            exit;
        }

        // Handle GET - show form
        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/auth/forgot_password.php';
        include __DIR__ . '/../views/footer.php';
    }

    public function reset() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Handle POST - reset password
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../lib/db.php';

            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            if (!$token || !$password || !$confirmPassword) {
                $_SESSION['auth_error'] = 'All fields are required.';
                header('Location: /reset-password?token=' . urlencode($token));
                exit;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['auth_error'] = 'Passwords do not match.';
                header('Location: /reset-password?token=' . urlencode($token));
                exit;
            }

            if (strlen($password) < 8) {
                $_SESSION['auth_error'] = 'Password must be at least 8 characters.';
                header('Location: /reset-password?token=' . urlencode($token));
                exit;
            }

            try {
                $pdo = get_db();
                require_once __DIR__ . '/../lib/timezone.php';

                // Verify token and get user with grace period
                $stmt = $pdo->prepare('SELECT id, reset_password_expires FROM users WHERE reset_password_token = ? LIMIT 1');
                $stmt->execute([$token]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $_SESSION['auth_error'] = 'Invalid password reset link.';
                    header('Location: /login');
                    exit;
                }

                // Check if token is expired (with grace period)
                if (is_expired($user['reset_password_expires'], TOKEN_GRACE_PERIOD_MINUTES)) {
                    $_SESSION['auth_error'] = 'Password reset link has expired. Please request a new one.';
                    header('Location: /login');
                    exit;
                }

                // Hash new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update password and clear reset token
                $updateStmt = $pdo->prepare('UPDATE users SET password_hash = ?, reset_password_token = NULL, reset_password_expires = NULL WHERE id = ?');
                $updateStmt->execute([$hashedPassword, $user['id']]);

                $_SESSION['auth_success'] = 'Password reset successfully! You can now log in with your new password.';
                header('Location: /login');
                exit;

            } catch (Exception $e) {
                if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
                    error_log('Password reset error: ' . $e->getMessage());
                }
                $_SESSION['auth_error'] = 'An error occurred. Please try again.';
                header('Location: /reset-password?token=' . urlencode($token));
                exit;
            }
        }

        // Handle GET - show form
        $token = $_GET['token'] ?? '';

        if (!$token) {
            $_SESSION['auth_error'] = 'Invalid password reset link.';
            header('Location: /login');
            exit;
        }

        // Verify token exists and hasn't expired
        require_once __DIR__ . '/../lib/db.php';
        require_once __DIR__ . '/../lib/timezone.php';
        $pdo = get_db();

        $stmt = $pdo->prepare('SELECT id, reset_password_expires FROM users WHERE reset_password_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['auth_error'] = 'Invalid password reset link.';
            header('Location: /login');
            exit;
        }

        // Check if token is expired (with grace period)
        if (is_expired($user['reset_password_expires'], TOKEN_GRACE_PERIOD_MINUTES)) {
            $_SESSION['auth_error'] = 'Password reset link has expired. Please request a new one.';
            header('Location: /login');
            exit;
        }

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/auth/reset_password.php';
        include __DIR__ . '/../views/footer.php';
    }
}
?>