<?php
class CheckEmailController {
    public function index() {
        // Handle POST - resend verification email
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (!$email) {
                $_SESSION['auth_error'] = 'Email address is required';
                header('Location: /check-email' . (!empty($email) ? '?email=' . urlencode($email) : ''));
                exit;
            }

            require_once __DIR__ . '/../lib/db.php';

            try {
                $pdo = get_db();

                // Check if user exists and is not verified
                $stmt = $pdo->prepare('SELECT id, full_name, email_verification_token, email_verification_expires FROM users WHERE email = ? AND email_verified = 0 LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $_SESSION['auth_error'] = 'No unverified account found with this email address';
                    header('Location: /check-email' . (!empty($email) ? '?email=' . urlencode($email) : ''));
                    exit;
                }

                // Check if token is expired
                require_once __DIR__ . '/../lib/timezone.php';
                $now = get_utc_now();
                $expires = new DateTime($user['email_verification_expires'], new DateTimeZone('UTC'));

                if ($now > $expires) {
                    // Generate new token
                    $verificationToken = bin2hex(random_bytes(32));
                    $tokenExpiry = $now->modify('+24 hours')->format('Y-m-d H:i:s');

                    $stmt = $pdo->prepare('UPDATE users SET email_verification_token = ?, email_verification_expires = ? WHERE id = ?');
                    $stmt->execute([$verificationToken, $tokenExpiry, $user['id']]);
                } else {
                    $verificationToken = $user['email_verification_token'];
                }

                // Send verification email
                require_once __DIR__ . '/../lib/EmailService.php';
                $emailService = new EmailService();

                if ($emailService->sendVerificationEmail($email, $user['full_name'], $verificationToken)) {
                    $_SESSION['auth_success'] = 'Verification email sent successfully! Please check your email.';
                } else {
                    $_SESSION['auth_error'] = 'Failed to send verification email. Please try again later.';
                }

                header('Location: /check-email?email=' . urlencode($email));
                exit;

            } catch (Exception $e) {
                error_log($e->getMessage());
                $_SESSION['auth_error'] = 'An error occurred while sending the email';
                header('Location: /check-email' . (!empty($email) ? '?email=' . urlencode($email) : ''));
                exit;
            }
        }

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/auth/check_email.php';
        include __DIR__ . '/../views/footer.php';
    }
}
?>