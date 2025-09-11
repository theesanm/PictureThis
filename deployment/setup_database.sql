-- PictureThis Database Setup SQL
-- Copy and paste these statements into phpMyAdmin SQL tab
-- Or run via command line: mysql -h localhost -P 3306 -u cfoxcozj_picThisdb -p cfoxcozj_PictureThis < setup_database.sql

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS `cfoxcozj_PictureThis` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cfoxcozj_PictureThis`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `credits` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    INDEX `users_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Images table
CREATE TABLE IF NOT EXISTS `images` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `prompt` TEXT,
    `image_url` TEXT NOT NULL,
    `generation_cost` INT NOT NULL DEFAULT 10,
    `has_usage_permission` BOOLEAN DEFAULT FALSE,
    `usage_confirmed_at` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `images_user_id_index` (`user_id`),
    CONSTRAINT `images_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Credit transactions table
CREATE TABLE IF NOT EXISTS `credit_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `amount` INT NOT NULL,
    `transaction_type` VARCHAR(50) NOT NULL DEFAULT 'usage',
    `description` TEXT,
    `payment_id` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `credit_transactions_user_id_index` (`user_id`),
    INDEX `credit_transactions_payment_id_index` (`payment_id`),
    CONSTRAINT `credit_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `k` VARCHAR(255) NOT NULL,
    `v` TEXT,
    `credit_cost_per_image` INT NOT NULL DEFAULT 10,
    `enhanced_prompt_cost` INT NOT NULL DEFAULT 1,
    `enhanced_prompt_enabled` BOOLEAN NOT NULL DEFAULT TRUE,
    `ai_provider` VARCHAR(50) NOT NULL DEFAULT 'openrouter',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `settings_k_unique` (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table (for PayFast integration)
CREATE TABLE IF NOT EXISTS `payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payment_id` VARCHAR(191) NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `package_id` VARCHAR(64) NOT NULL,
    `credits` INT NOT NULL DEFAULT 0,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `payments_payment_id_unique` (`payment_id`),
    INDEX `payments_user_id_index` (`user_id`),
    CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO `settings` (`k`, `v`, `credit_cost_per_image`, `enhanced_prompt_cost`, `enhanced_prompt_enabled`, `ai_provider`) VALUES
('credit_cost_per_image', '10', 10, 1, TRUE, 'openrouter'),
('enhanced_prompt_cost', '1', 10, 1, TRUE, 'openrouter'),
('enhanced_prompt_enabled', 'true', 10, 1, TRUE, 'openrouter'),
('ai_provider', 'openrouter', 10, 1, TRUE, 'openrouter');

-- Create test admin user (optional - remove in production)
-- Password hash for 'admin123'
INSERT IGNORE INTO `users` (`full_name`, `email`, `password_hash`, `credits`, `created_at`) VALUES
('Admin User', 'admin@picturethis.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 100, NOW());

-- Show success message
SELECT 'Database setup completed successfully!' as status;
