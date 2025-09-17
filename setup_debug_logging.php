<?php
/**
 * Debug Log Setup Script
 * Ensures debug.log exists and has proper permissions for PictureThis deployment
 */

// Configuration
$debugLogPath = __DIR__ . '/debug.log';
$logDir = dirname($debugLogPath);

echo "Setting up debug logging for PictureThis...\n";

// Ensure the directory exists
if (!is_dir($logDir)) {
    if (mkdir($logDir, 0755, true)) {
        echo "✓ Created log directory: $logDir\n";
    } else {
        echo "✗ Failed to create log directory: $logDir\n";
        exit(1);
    }
}

// Ensure the debug.log file exists
if (!file_exists($debugLogPath)) {
    if (file_put_contents($debugLogPath, "Debug log initialized on " . date('Y-m-d H:i:s') . "\n") !== false) {
        echo "✓ Created debug.log file: $debugLogPath\n";
    } else {
        echo "✗ Failed to create debug.log file: $debugLogPath\n";
        exit(1);
    }
}

// Set proper permissions
if (chmod($debugLogPath, 0666)) {
    echo "✓ Set permissions on debug.log (0666)\n";
} else {
    echo "✗ Failed to set permissions on debug.log\n";
}

// Test if the file is writable
if (is_writable($debugLogPath)) {
    echo "✓ Debug.log is writable\n";
    
    // Add a test entry
    $testMessage = "[" . date('Y-m-d H:i:s') . "] [SETUP] Debug logging initialized successfully\n";
    if (file_put_contents($debugLogPath, $testMessage, FILE_APPEND) !== false) {
        echo "✓ Successfully wrote test entry to debug.log\n";
    } else {
        echo "✗ Failed to write test entry to debug.log\n";
    }
} else {
    echo "✗ Debug.log is not writable\n";
    exit(1);
}

echo "\nDebug logging setup complete!\n";
echo "Log file: $debugLogPath\n";
?>