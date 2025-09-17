<?php
require_once 'config/config.php';
require_once 'src/lib/db.php';

// Simulate a logged-in user with the correct ID (user ID 2 has the image)
$_SESSION['user'] = ['id' => 2, 'email' => 'test@example.com', 'credits' => 100];

require_once 'src/controllers/GalleryController.php';
$controller = new GalleryController();

// Test the index method
ob_start();
$controller->index();
$output = ob_get_clean();

// Extract debug info
$debugStart = strpos($output, '<!-- DEBUG:');
if ($debugStart !== false) {
    $debugEnd = strpos($output, '-->', $debugStart);
    $debugInfo = substr($output, $debugStart, $debugEnd - $debugStart + 3);
    echo 'Debug Info:' . PHP_EOL;
    echo $debugInfo . PHP_EOL;
} else {
    echo 'No debug info found' . PHP_EOL;
}

echo 'Contains images: ' . (strpos($output, 'generated_') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Contains empty gallery message: ' . (strpos($output, 'No images yet') !== false ? 'YES' : 'NO') . PHP_EOL;
?>
