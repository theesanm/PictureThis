<?php
// Test database connection
require_once 'config/config.php';
require_once 'src/lib/db.php';

$db = get_db();
if ($db) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed.";
}
?>