<?php
// Simple syntax check for EmailService.php
echo "Testing EmailService.php syntax...\n";

// Try to include the file
try {
    require_once 'config/config.php';
    require_once 'src/lib/EmailService.php';
    echo "✓ EmailService.php loaded successfully\n";
    
    // Test instantiation
    $emailService = new EmailService();
    echo "✓ EmailService instantiated successfully\n";
    
    // Check if debug logging would work
    if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
        echo "✓ Debug logging is enabled (development mode)\n";
    } else {
        echo "ℹ Production mode detected - debug logging disabled\n";
    }
    
    echo "\nEmailService is ready for testing!\n";
    echo "To test email sending, run: test_email_service.php\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
