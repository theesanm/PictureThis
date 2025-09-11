<?php
// log_client_error.php - receives POSTed JSON from client_error_reporter.js and logs to error log
error_reporting(E_ALL);
ini_set('display_errors', 0);

$raw = file_get_contents('php://input');
if (!$raw) {
    http_response_code(400);
    echo "no payload";
    exit;
}

$data = json_decode($raw, true);
if (!$data) {
    error_log('[CLIENT_ERR] invalid json payload: ' . $raw);
    http_response_code(400);
    echo "invalid json";
    exit;
}

$msg = '[CLIENT_ERR] ' . ($data['type'] ?? 'unknown') . ' ' . ($data['message'] ?? '') . ' url=' . ($data['url'] ?? '') . ' ua=' . ($data['ua'] ?? '') . ' ts=' . ($data['ts'] ?? '') . '\n';
if (isset($data['filename'])) $msg .= ' file=' . $data['filename'] . ' line=' . ($data['lineno'] ?? '') . ' col=' . ($data['colno'] ?? '') . '\n';
if (isset($data['stack'])) $msg .= ' stack=' . substr($data['stack'],0,2000) . '\n';

error_log($msg);

// return simple ok
header('Content-Type: text/plain');
echo "ok";

?>
