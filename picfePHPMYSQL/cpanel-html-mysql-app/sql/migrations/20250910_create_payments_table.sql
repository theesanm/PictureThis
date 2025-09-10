-- Migration: 20250910_create_payments_table.sql
-- Creates a payments table to store pending and processed PayFast payments
-- Run: php scripts/run_payments_migrations.php  (recommended) or mysql -u <user> -p <database> < 20250910_create_payments_table.sql

CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` VARCHAR(191) NOT NULL,
  `user_id` INT NOT NULL,
  `package_id` VARCHAR(64) NOT NULL,
  `credits` INT NOT NULL DEFAULT 0,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payment_id` (`payment_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: add foreign key if you want strict relational integrity (uncomment if users.id exists and you want FK)
-- ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
