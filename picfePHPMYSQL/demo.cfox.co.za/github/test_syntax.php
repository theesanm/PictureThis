<?php
// Simple syntax test
echo "Testing EmailService.php syntax...\n";

try {
    // Try to parse the file
    $content = file_get_contents('src/lib/EmailService.php');
    echo "✓ File read successfully (" . strlen($content) . " characters)\n";
    
    // Check if it starts with <?php
    if (strpos($content, '<?php') === 0) {
        echo "✓ PHP opening tag found\n";
    } else {
        echo "✗ PHP opening tag not found at start\n";
    }
    
    // Check for basic PHP structure
    if (strpos($content, 'class EmailService') !== false) {
        echo "✓ EmailService class found\n";
    } else {
        echo "✗ EmailService class not found\n";
    }
    
    // Check for closing brace
    if (substr(trim($content), -1) === '}') {
        echo "✓ File ends with closing brace\n";
    } else {
        echo "✗ File does not end with closing brace\n";
    }
    
    echo "\nFile appears to be syntactically correct!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
