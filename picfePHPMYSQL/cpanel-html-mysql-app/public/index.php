<?php
require_once '../config/config.php';
require_once '../src/lib/db.php';
require_once '../src/controllers/HomeController.php';

session_start();

$controller = new HomeController();
$controller->index();
?>