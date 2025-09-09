<?php
// src/api/search.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';

$cfg = include __DIR__ . '/../../config/config.php';
$mysqli = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);
if($mysqli->connect_errno){
  http_response_code(500);
  echo json_encode(['error' => 'DB connection failed']);
  exit;
}

$q = isset($_GET['q']) ? $mysqli->real_escape_string($_GET['q']) : '';
$sql = "SELECT id, title, image_url FROM pictures " . ($q !== '' ? "WHERE title LIKE '%$q%'" : "") . " ORDER BY id DESC LIMIT 50";
$res = $mysqli->query($sql);
$rows = [];
if($res){
  while($r = $res->fetch_assoc()) $rows[] = $r;
}
echo json_encode($rows);
