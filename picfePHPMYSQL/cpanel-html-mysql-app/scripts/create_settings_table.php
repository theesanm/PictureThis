<?php
require_once __DIR__ . '/../src/lib/db.php';
$pdo = get_db();
$sql = "CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(191) NOT NULL PRIMARY KEY,
  v TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
try {
    $pdo->exec($sql);
    echo "settings table created or already exists.\n";
} catch (PDOException $e) {
    echo "Error creating settings table: " . $e->getMessage() . "\n";
}
