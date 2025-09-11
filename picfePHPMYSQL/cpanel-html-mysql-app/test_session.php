<?php
// test_session.php - one-off debug page to verify PHP session behavior.
// Place in webroot next to index.php, open in an incognito window, reload to see persistence.
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "PictureThis - test_session.php\n";
echo "========================\n\n";

echo "--- SERVER/ENV ---\n";
echo "HTTP_HOST=" . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
echo "HTTPS=" . ($_SERVER['HTTPS'] ?? '') . "\n\n";

echo "--- PHP SESSION INFO BEFORE START ---\n";
echo "session_status(): " . session_status() . " (0=DISABLED,1=NONE,2=ACTIVE)\n";
echo "headers_sent(): " . (headers_sent() ? 'true' : 'false') . "\n";
echo "session.save_path=" . ini_get('session.save_path') . "\n";
echo "session.cookie_lifetime=" . ini_get('session.cookie_lifetime') . "\n";
echo "session.cookie_secure=" . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_domain=" . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_path=" . ini_get('session.cookie_path') . "\n\n";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$testKey = 'debug_test';
if (empty($_SESSION[$testKey])) {
    $_SESSION[$testKey] = ['count' => 1, 'ts' => time()];
    $justSet = true;
} else {
    $_SESSION[$testKey]['count']++;
    $justSet = false;
}

echo "Session ID: " . session_id() . "\n";
echo "Just set? " . ($justSet ? 'yes' : 'no') . "\n";
echo "_SESSION[{$testKey}]: " . print_r($_SESSION[$testKey], true) . "\n\n";

echo "--- RESPONSE HEADERS (server-side) ---\n";
foreach (headers_list() as $h) echo $h . "\n";

echo "\n--- USAGE ---\n";
echo "1) Open this page in an incognito window and note the 'Just set?' value.\n";
echo "2) Reload the page — it should show Just set? no and count incremented.\n";
echo "3) In browser devtools > Application > Cookies verify cookie 'PHPSESSID' exists for the domain.\n";

echo "\nNOTE: Remove this file after use.\n";

?>
