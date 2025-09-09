<?php
// src/api/search.php
header('Content-Type: application/json; charset=utf-8');

$cfg = include __DIR__ . '/../../config/config.php';
$mysqli = @new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);

// If DB is not reachable, return sample data so the frontend can be previewed locally
if($mysqli->connect_errno){
  $sample = [
    ['id'=>1, 'title'=>'Sample Image 1', 'image_url'=>'/public/placeholder-image.jpg'],
    ['id'=>2, 'title'=>'Sample Image 2', 'image_url'=>'/public/placeholder-image.jpg'],
    ['id'=>3, 'title'=>'Sample Image 3', 'image_url'=>'/public/placeholder-image.jpg'],
  ];
  echo json_encode($sample);
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
