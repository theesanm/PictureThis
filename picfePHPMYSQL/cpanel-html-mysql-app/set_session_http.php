<?php
// Simple script to set session for testing
// This runs in the web server context, not CLI

session_start();

$_SESSION['user'] = [
    'id' => 1,
    'fullName' => 'Admin User',
    'email' => 'admin@picturethis.com'
];

echo json_encode([
    'success' => true,
    'message' => 'Session set successfully',
    'session_id' => session_id(),
    'user' => $_SESSION['user']
]);
?>