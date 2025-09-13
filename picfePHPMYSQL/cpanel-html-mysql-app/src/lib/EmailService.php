<?php
// EmailService.php - Handles email sending using PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include PHPMailer classes
require_once __DIR__ . '/../../mailer/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../mailer/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../../mailer/PHPMailer-master/src/Exception.php';

class EmailService {

    private $smtpHost;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpPort;
    private $fromEmail;
    private $mailer;

    public function __construct() {
        // Load SMTP configuration from environment variables
        $this->smtpHost = getenv('SMTP_HOST') ?: 'mail.cfox.co.za';
        $this->smtpUsername = getenv('SMTP_USERNAME') ?: 'picturethis@cfox.co.za';
        $this->smtpPassword = getenv('SMTP_PASSWORD') ?: '';
        $this->smtpPort = getenv('SMTP_PORT') ?: 587;
        $this->fromEmail = getenv('FROM_EMAIL') ?: 'picturethis@cfox.co.za';

        // Initialize PHPMailer
        $this->initializeMailer();
    }

    /**
     * Initialize PHPMailer with SMTP settings
     */
    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->smtpHost;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->smtpUsername;
        $this->mailer->Password = $this->smtpPassword;
        $this->mailer->Port = $this->smtpPort;

        // Enable TLS encryption (compatible with older PHPMailer versions)
        if ($this->smtpPort == 587) {
            $this->mailer->SMTPSecure = 'tls'; // PHPMailer::ENCRYPTION_STARTTLS
        } elseif ($this->smtpPort == 465) {
            $this->mailer->SMTPSecure = 'ssl'; // PHPMailer::ENCRYPTION_SMTPS
        }

        // Set sender
        $this->mailer->setFrom($this->fromEmail, 'PictureThis');

        // Development mode settings (compatible with older PHPMailer versions)
        $isDevelopment = (!defined('IS_PRODUCTION') || !IS_PRODUCTION);
        if ($isDevelopment) {
            $this->mailer->SMTPDebug = 2; // SMTP::DEBUG_SERVER (verbose debug output)
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("PHPMailer: $str");
            };
        }
    }

    /**
     * Send email verification to new user
     */
    public function sendVerificationEmail($email, $name, $token) {
        $subject = 'Verify Your PictureThis Account';
        $baseUrl = getenv('APP_URL') ?: 'https://demo.cfox.co.za';
        $verificationUrl = $baseUrl . "/verify-email?token=" . $token;

        $message = "
<html>
<head>
    <title>Verify Your Account</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Welcome to PictureThis!</h1>
            <p>Your AI Image Generation Journey Starts Here</p>
        </div>
        <div class='content'>
            <h2>Hello $name!</h2>
            <p>Thank you for joining PictureThis! To start creating amazing AI-generated images, please verify your email address by clicking the button below:</p>

            <div style='text-align: center;'>
                <a href='$verificationUrl' class='button'>Verify My Email</a>
            </div>

            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Access to our AI image generation tools</li>
                <li>Save and manage your image gallery</li>
                <li>Professional image enhancement features</li>
            </ul>

            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <p style='word-break: break-all; background: #e8e8e8; padding: 10px; border-radius: 5px;'>$verificationUrl</p>

            <p>This verification link will expire in 24 hours for security reasons.</p>

            <p>If you didn't create an account with PictureThis, please ignore this email.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2025 PictureThis@cfox. All rights reserved.</p>
            <p>Having trouble? Contact us at <a href='mailto:support@cfox.co.za'>support@cfox.co.za</a></p>
        </div>
    </div>
</body>
</html>";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: PictureThis <picturethis@cfox.co.za>" . "\r\n";
        $headers .= "Reply-To: picturethis@cfox.co.za" . "\r\n";

        return $this->sendEmail($email, $subject, $message, $headers, 'verification');
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $name, $token) {
        $subject = 'Reset Your PictureThis Password';
        $baseUrl = getenv('APP_URL') ?: 'https://demo.cfox.co.za';
        $resetUrl = $baseUrl . "/reset-password?token=" . $token;

        $message = "
<html>
<head>
    <title>Reset Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #ff6b6b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Password Reset Request</h1>
            <p>Secure your PictureThis account</p>
        </div>
        <div class='content'>
            <h2>Hello $name,</h2>
            <p>We received a request to reset your password for your PictureThis account. If you made this request, click the button below to reset your password:</p>

            <div style='text-align: center;'>
                <a href='$resetUrl' class='button'>Reset My Password</a>
            </div>

            <div class='warning'>
                <strong>Security Notice:</strong> This password reset link will expire in 1 hour. For your security, don't share this email with anyone.
            </div>

            <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>

            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <p style='word-break: break-all; background: #e8e8e8; padding: 10px; border-radius: 5px;'>$resetUrl</p>

            <p><strong>Need help?</strong> Contact our support team if you're having trouble resetting your password.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2025 PictureThis@cfox. All rights reserved.</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: PictureThis <picturethis@cfox.co.za>" . "\r\n";
        $headers .= "Reply-To: picturethis@cfox.co.za" . "\r\n";

        return $this->sendEmail($email, $subject, $message, $headers, 'password_reset');
    }

    /**
     * Send welcome email after verification
     */
    public function sendWelcomeEmail($email, $name) {
        $subject = 'Welcome to PictureThis!';

        $message = "
<html>
<head>
    <title>Welcome to PictureThis!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .features { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Welcome to PictureThis!</h1>
            <p>Your account has been verified successfully</p>
        </div>
        <div class='content'>
            <h2>Welcome aboard, $name!</h2>
            <p>Your email has been verified and your account is now active. You're ready to start creating amazing AI-generated images!</p>

            <div class='features'>
                <h3>What you can do now:</h3>
                <ul>
                    <li><strong>Generate Images:</strong> Use our AI to create stunning images from text descriptions</li>
                    <li><strong>Image Gallery:</strong> Save and organize all your creations</li>
                    <li><strong>Professional Tools:</strong> Access advanced image enhancement features</li>
                </ul>
            </div>

            <div style='text-align: center;'>
                <a href='" . (getenv('APP_URL') ?: 'https://demo.cfox.co.za') . "/dashboard' class='button'>Start Creating Images</a>
            </div>

            <p>Need help getting started? Check out our documentation or contact support.</p>
        </div>
        <div class='footer'>
            <p>&copy; 2025 PictureThis@cfox. All rights reserved.</p>
            <p>Questions? Contact us at <a href='mailto:support@cfox.co.za'>support@cfox.co.za</a></p>
        </div>
    </div>
</body>
</html>";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: PictureThis <picturethis@cfox.co.za>" . "\r\n";
        $headers .= "Reply-To: picturethis@cfox.co.za" . "\r\n";

        return $this->sendEmail($email, $subject, $message, $headers, 'welcome');
    }

    /**
     * Unified email sending method with SMTP authentication and debug logging
     */
    private function sendEmail($to, $subject, $message, $headers, $emailType) {
        // Debug logging for development
        $isDevelopment = (!defined('IS_PRODUCTION') || !IS_PRODUCTION);
        
        if ($isDevelopment) {
            error_log("=== EMAIL DEBUG [$emailType] ===");
            error_log("To: $to");
            error_log("Subject: $subject");
            error_log("SMTP Host: {$this->smtpHost}");
            error_log("SMTP Port: {$this->smtpPort}");
            error_log("From: {$this->fromEmail}");
            error_log("Message length: " . strlen($message) . " characters");
        }

        // Try PHPMailer SMTP first, fallback to mail() if SMTP fails
        $result = $this->sendViaPHPMailer($to, $subject, $message, $headers, $emailType);

        if (!$result) {
            if ($isDevelopment) {
                error_log("PHPMailer failed, falling back to mail() function");
            }
            // Fallback to PHP mail() function
            $result = mail($to, $subject, $message, $headers);
        }

        if ($isDevelopment) {
            if ($result) {
                error_log("Email sent successfully to: $to");
                file_put_contents(__DIR__ . '/../../logs/email_debug.log', 
                    date('Y-m-d H:i:s') . " SUCCESS: Email sent to $to\n", FILE_APPEND);
            } else {
                error_log("Email failed to send to: $to");
                file_put_contents(__DIR__ . '/../../logs/email_debug.log', 
                    date('Y-m-d H:i:s') . " FAILED: Email to $to failed\n", FILE_APPEND);
            }
            error_log("=== END EMAIL DEBUG ===\n");
        }

        return $result;
    }

    /**
     * Send email via SMTP with authentication
     */
    private function sendViaSMTP($to, $subject, $message, $headers, $emailType) {
        $isDevelopment = (!defined('IS_PRODUCTION') || !IS_PRODUCTION);

        try {
            if ($isDevelopment) {
                error_log("Attempting SMTP connection to: {$this->smtpHost}:{$this->smtpPort}");
                error_log("Username: {$this->smtpUsername}");
                error_log("Password length: " . strlen($this->smtpPassword));
            }

            // For port 465, use SSL directly. For port 587, use TLS upgrade
            if ($this->smtpPort == 465) {
                $socket = fsockopen("ssl://{$this->smtpHost}", $this->smtpPort, $errno, $errstr, 30);
                if ($isDevelopment) {
                    error_log("Using SSL connection (port 465)");
                }
            } else {
                $socket = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
                if ($isDevelopment) {
                    error_log("Using plain connection, will upgrade to TLS");
                }
            }

            if (!$socket) {
                if ($isDevelopment) {
                    error_log("SMTP Connection failed: $errstr ($errno)");
                }
                return false;
            }

            if ($isDevelopment) {
                error_log("SMTP Socket created successfully");
            }

            // Set timeout for socket operations
            stream_set_timeout($socket, 30);

            // Read server greeting
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("SMTP Greeting: " . trim($response));
            }

            if (empty($response) || !preg_match('/^220/', $response)) {
                if ($isDevelopment) {
                    error_log("Invalid SMTP greeting: " . trim($response));
                }
                fclose($socket);
                return false;
            }

            // Send EHLO command
            fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("EHLO Response: " . trim($response));
            }

            // For port 587, try STARTTLS if available
            if ($this->smtpPort == 587 && strpos($response, 'STARTTLS') !== false) {
                fwrite($socket, "STARTTLS\r\n");
                $response = fgets($socket, 515);
                if ($isDevelopment) {
                    error_log("STARTTLS Response: " . trim($response));
                }

                if (preg_match('/^220/', $response)) {
                    // Enable crypto
                    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                        if ($isDevelopment) {
                            error_log("TLS upgrade failed");
                        }
                        fclose($socket);
                        return false;
                    }
                    if ($isDevelopment) {
                        error_log("TLS upgrade successful");
                    }

                    // Send EHLO again after TLS
                    fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
                    $response = fgets($socket, 515);
                    if ($isDevelopment) {
                        error_log("EHLO after TLS: " . trim($response));
                    }
                }
            }

            // Send AUTH LOGIN
            fwrite($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("AUTH LOGIN Response: " . trim($response));
            }

            if (!preg_match('/^334/', $response)) {
                if ($isDevelopment) {
                    error_log("AUTH LOGIN not accepted: " . trim($response));
                }
                fclose($socket);
                return false;
            }

            // Send username (base64 encoded)
            fwrite($socket, base64_encode($this->smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("Username Response: " . trim($response));
            }

            if (!preg_match('/^334/', $response)) {
                if ($isDevelopment) {
                    error_log("Username not accepted: " . trim($response));
                }
                fclose($socket);
                return false;
            }

            // Send password (base64 encoded)
            fwrite($socket, base64_encode($this->smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("Password Response: " . trim($response));
            }

            if (!preg_match('/^235/', $response)) {
                if ($isDevelopment) {
                    error_log("Authentication failed: " . trim($response));
                }
                fclose($socket);
                return false;
            }

            if ($isDevelopment) {
                error_log("SMTP Authentication successful");
            }

            // Send MAIL FROM
            fwrite($socket, "MAIL FROM:<{$this->fromEmail}>\r\n");
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("MAIL FROM Response: " . trim($response));
            }

            if (!preg_match('/^250/', $response)) {
                if ($isDevelopment) {
                    error_log("MAIL FROM failed: " . trim($response));
                }
                fclose($socket);
                return false;
            }

            // Send RCPT TO
            fwrite($socket, "RCPT TO:<$to>\r\n");
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("RCPT TO Response: " . trim($response));
            }

            if (!preg_match('/^250/', $response)) {
                if ($isDevelopment) {
                    error_log("RCPT TO failed: " . trim($response));
                }
                fclose($socket);
                return false;
            }

            // Send DATA
            fwrite($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("DATA Response: " . trim($response));
            }

            if (!preg_match('/^354/', $response)) {
                if ($isDevelopment) {
                    error_log("DATA command failed: " . trim($response));
                }
                fclose($socket);
                return false;
            }

            // Send email content
            $emailContent = "From: PictureThis <{$this->fromEmail}>\r\n";
            $emailContent .= "To: $to\r\n";
            $emailContent .= "Subject: $subject\r\n";
            $emailContent .= "MIME-Version: 1.0\r\n";
            $emailContent .= "Content-type:text/html;charset=UTF-8\r\n";
            $emailContent .= "Reply-To: {$this->fromEmail}\r\n";
            $emailContent .= "\r\n";
            $emailContent .= $message;
            $emailContent .= "\r\n.\r\n";

            fwrite($socket, $emailContent);

            // Read response
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("Message Send Response: " . trim($response));
            }

            $messageSent = preg_match('/^250/', $response);

            // Send QUIT
            fwrite($socket, "QUIT\r\n");
            $response = fgets($socket, 515);
            if ($isDevelopment) {
                error_log("QUIT Response: " . trim($response));
            }

            fclose($socket);

            if ($isDevelopment) {
                error_log("SMTP connection closed");
                error_log("Email sending result: " . ($messageSent ? "SUCCESS" : "FAILED"));
            }

            return $messageSent;

        } catch (Exception $e) {
            if ($isDevelopment) {
                error_log("SMTP Exception: " . $e->getMessage());
                error_log("Exception trace: " . $e->getTraceAsString());
            }
            return false;
        }
    }

    /**
     * Send email via PHPMailer SMTP
     */
    private function sendViaPHPMailer($to, $subject, $message, $headers, $emailType) {
        $isDevelopment = (!defined('IS_PRODUCTION') || !IS_PRODUCTION);

        try {
            if ($isDevelopment) {
                error_log("=== PHPMailer SMTP [$emailType] ===");
                error_log("To: $to");
                error_log("Subject: $subject");
                error_log("SMTP Host: {$this->smtpHost}:{$this->smtpPort}");
                error_log("Username: {$this->smtpUsername}");
            }

            // Clear previous recipients (for older PHPMailer versions)
            $this->mailer->clearAddresses();
            // Note: clearCC() and clearBCC() not available in older PHPMailer versions
            // $this->mailer->clearCC();
            // $this->mailer->clearBCC();

            // Set recipient
            $this->mailer->addAddress($to);

            // Set email content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $message;

            // Send the email
            $result = $this->mailer->send();

            if ($isDevelopment) {
                if ($result) {
                    error_log("PHPMailer: Email sent successfully to $to");
                } else {
                    error_log("PHPMailer: Failed to send email - " . $this->mailer->ErrorInfo);
                }
                error_log("=== PHPMailer End ===");
            }

            return $result;

        } catch (Exception $e) {
            if ($isDevelopment) {
                error_log("PHPMailer Exception: " . $e->getMessage());
                error_log("PHPMailer Error Info: " . $this->mailer->ErrorInfo);
            }
            return false;
        }
    }
}
