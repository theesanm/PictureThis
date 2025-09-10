<?php
// Serves generated-image-1.png from the central repo if not present locally
$local = __DIR__ . '/generated-image-1.png';
$global = dirname(__DIR__, 3) . '/public/generated-image-1.png';

if (file_exists($local)) {
    $path = $local;
} elseif (file_exists($global)) {
    $path = $global;
} else {
    header('Content-Type: image/png');
    // 1x1 transparent PNG fallback
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEX///+nxBvIAAAACklEQVQI12NgAAAAAgAB4iG8MwAAAABJRU5ErkJggg==');
    exit;
}

$mime = mime_content_type($path);
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
