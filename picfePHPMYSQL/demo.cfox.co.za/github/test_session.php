<?php
// test_session.php - one-off debug page to verify PHP session behavior.
// Place in webroot next to index.php, open in an incognito window, reload to see persistence.

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Detect if headers were already sent (file and line) before attempting session_start.
$headersFile = '';
$headersLine = 0;
$headersAlready = headers_sent($headersFile, $headersLine);

// Attempt to start session only if headers have not been sent yet and session not active.
$sessionStarted = false;
if (!$headersAlready) {
    if (session_status() === PHP_SESSION_NONE) {
        $sessionStarted = session_start();
    } else {
        $sessionStarted = (session_status() === PHP_SESSION_ACTIVE);
    }
}

// Set content-type header only if we can still send headers.
if (!$headersAlready) {
    header('Content-Type: text/plain; charset=utf-8');
}

echo "PictureThis - test_session.php\n";
echo "========================\n\n";

echo "--- SERVER/ENV ---\n";
echo "HTTP_HOST=" . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
echo "HTTPS=" . ($_SERVER['HTTPS'] ?? '') . "\n\n";

echo "--- PHP SESSION INFO BEFORE/AFTER START ATTEMPT ---\n";
echo "session_status(): " . session_status() . " (0=DISABLED,1=NONE,2=ACTIVE)\n";
echo "headers_sent(): " . ($headersAlready ? 'true' : 'false') . ($headersAlready ? " (first output at $headersFile:$headersLine)" : '') . "\n";
echo "session.save_path=" . ini_get('session.save_path') . "\n";
echo "session.cookie_lifetime=" . ini_get('session.cookie_lifetime') . "\n";
echo "session.cookie_secure=" . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_domain=" . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_path=" . ini_get('session.cookie_path') . "\n\n";

$testKey = 'debug_test';
if ($sessionStarted) {
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
} else {
    echo "Session could NOT be started because headers were already sent or another issue occurred.\n";
    echo "Session ID: " . session_id() . "\n";
    echo "You may have output sent before session_start (see headers_sent above).\n\n";
}

echo "--- RESPONSE HEADERS (server-side) ---\n";
foreach (headers_list() as $h) echo $h . "\n";

echo "\n--- USAGE ---\n";
echo "1) Open this page in an incognito window and note the 'Just set?' value (or the session failure message).\n";
echo "2) Reload the page — it should show Just set? no and count incremented if sessions work.\n";
echo "3) In browser devtools > Application > Cookies verify cookie 'PHPSESSID' exists for the domain.\n";

echo "\nNOTE: Remove this file after use.\n";

?>
