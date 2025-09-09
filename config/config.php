<?php
// config/config.php
// Edit these values or set environment variables in cPanel (recommended)
return [
  'host' => getenv('DB_HOST') ?: 'localhost',
  'user' => getenv('DB_USER') ?: 'cpanel_user',
  'pass' => getenv('DB_PASS') ?: 'cpanel_pass',
  'name' => getenv('DB_NAME') ?: 'cpanel_db',
  'charset' => 'utf8mb4',
];
