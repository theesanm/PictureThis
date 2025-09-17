<?php
require_once 'config/config.php';
require_once 'src/lib/EmailService.php';

// Test the EmailService
$emailService = new EmailService();
echo 'EmailService loaded successfully
';

// Test debug logging
$result = $emailService->sendVerificationEmail('test@example.com', 'Test User', 'test-token-123');
echo 'Email send result: ' . ($result ? 'true' : 'false') . '
';
echo 'Check logs/email_debug.log for debug output
';

