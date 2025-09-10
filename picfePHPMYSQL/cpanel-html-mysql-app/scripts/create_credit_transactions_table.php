<?php
// Run this once (php scripts/create_credit_transactions_table.php) to create the credit_transactions table.
require_once __DIR__ . '/../src/lib/db.php';
$pdo = get_db();

$sql = "CREATE TABLE IF NOT EXISTS credit_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  type VARCHAR(50) NOT NULL,
  amount INT NOT NULL,
  description TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  admin_id BIGINT UNSIGNED DEFAULT NULL,
  INDEX (user_id),
  INDEX (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $pdo->exec($sql);
    echo "credit_transactions table created or already exists.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
