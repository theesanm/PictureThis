<?php
// Test production database connection
define('IS_PRODUCTION', true); // Force production mode
require_once 'config/config.php';
require_once 'src/lib/db.php';

$db = get_db();
if ($db) {
    echo "Production database connection successful!";
} else {
    echo "Production database connection failed.";
}
?>