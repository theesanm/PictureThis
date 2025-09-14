<?php
require_once __DIR__ . '/../utils/CSRF.php';

class EmailVerificationController {
    public function verify() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        require_once __DIR__ . '/../lib/db.php';

        $token = $_GET['token'] ?? '';

        if (!$token) {
            $_SESSION['auth_error'] = 'Invalid verification link.';
            header('Location: /login');
            exit;
        }

        try {
            $pdo = get_db();

            // Find user with this verification token
            $stmt = $pdo->prepare('SELECT id, full_name, email, email_verification_expires FROM users WHERE email_verification_token = ? AND email_verified = 0 LIMIT 1');
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $_SESSION['auth_error'] = 'Invalid or expired verification link.';
                header('Location: /login');
                exit;
            }

            // Check if token has expired
            require_once __DIR__ . '/../lib/timezone.php';
            $now = get_utc_now();
            $expires = new DateTime($user['email_verification_expires'], new DateTimeZone('UTC'));

            if ($now > $expires) {
                $_SESSION['auth_error'] = 'Verification link has expired. Please register again.';
                header('Location: /register');
                exit;
            }

            // Verify the email
            $updateStmt = $pdo->prepare('UPDATE users SET email_verified = 1, email_verification_token = NULL, email_verification_expires = NULL WHERE id = ?');
            $updateStmt->execute([$user['id']]);

            // Send welcome email
            require_once __DIR__ . '/../lib/EmailService.php';
            $emailService = new EmailService();
            $emailService->sendWelcomeEmail($user['email'], $user['full_name']);

            // Log the user in automatically
            $_SESSION['user'] = [
                'id' => $user['id'],
                'fullName' => $user['full_name'],
                'email' => $user['email']
            ];

            $_SESSION['auth_success'] = 'Email verified successfully! Welcome to PictureThis!';
            header('Location: /dashboard');
            exit;

        } catch (Exception $e) {
            if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
                error_log('Email verification error: ' . $e->getMessage());
            }
            $_SESSION['auth_error'] = 'An error occurred during verification.';
            header('Location: /login');
            exit;
        }
    }

    public function resend() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate CSRF token
        if (!CSRF::validateRequest()) {
            $_SESSION['auth_error'] = 'Invalid request. Please try again.';
            header('Location: ' . ($_POST['return_url'] ?? '/login'));
            exit;
        }

        require_once __DIR__ . '/../lib/db.php';

        $email = trim($_POST['email'] ?? '');
        $returnUrl = $_POST['return_url'] ?? '/login';

        if (!$email) {
            $_SESSION['auth_error'] = 'Email address is required.';
            header('Location: ' . $returnUrl . (!empty($email) ? '?email=' . urlencode($email) : ''));
            exit;
        }

        try {
            $pdo = get_db();

            // Find user with this email
            $stmt = $pdo->prepare('SELECT id, full_name, email_verified FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $_SESSION['auth_error'] = 'No account found with this email address.';
                header('Location: ' . $returnUrl . (!empty($email) ? '?email=' . urlencode($email) : ''));
                exit;
            }

            if ($user['email_verified']) {
                $_SESSION['auth_success'] = 'Your email is already verified. You can log in now.';
                header('Location: /login');
                exit;
            }

            // Generate new verification token
            require_once __DIR__ . '/../lib/timezone.php';
            $verificationToken = bin2hex(random_bytes(32));
            $tokenExpiry = get_utc_now()->modify('+24 hours')->format('Y-m-d H:i:s');

            // Update user with new token
            $updateStmt = $pdo->prepare('UPDATE users SET email_verification_token = ?, email_verification_expires = ? WHERE id = ?');
            $updateStmt->execute([$verificationToken, $tokenExpiry, $user['id']]);

            // Send verification email
            require_once __DIR__ . '/../lib/EmailService.php';
            $emailService = new EmailService();

            if ($emailService->sendVerificationEmail($email, $user['full_name'], $verificationToken)) {
                $_SESSION['auth_success'] = 'Verification email sent! Please check your email.';
            } else {
                $_SESSION['auth_error'] = 'Failed to send verification email. Please try again later.';
            }

        } catch (Exception $e) {
            if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
                error_log('Resend verification error: ' . $e->getMessage());
            }
            $_SESSION['auth_error'] = 'An error occurred. Please try again.';
        }

        header('Location: ' . $returnUrl . (!empty($email) ? '?email=' . urlencode($email) : ''));
        exit;
    }
}
?>