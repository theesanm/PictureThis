<?php
/**
 * Web-Based Database Setup
 * Creates all necessary database tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Database Setup - PictureThis</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".info { color: #007bff; background: #cce7ff; border: 1px solid #b3d7ff; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo "h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #1e7e34; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>📊 Database Setup - PictureThis</h1>";

// Include config
require_once __DIR__ . '/config/config.php';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<div class='success'>✅ Connected to database successfully!</div>";

    // SQL statements to create tables
    $sqlStatements = [

        // Users table
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `full_name` VARCHAR(255) DEFAULT NULL,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `credits` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Images table
        "CREATE TABLE IF NOT EXISTS `images` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `prompt` TEXT,
            `image_url` VARCHAR(500),
            `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Credit transactions table
        "CREATE TABLE IF NOT EXISTS `credit_transactions` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `amount` INT NOT NULL,
            `type` ENUM('purchase', 'usage', 'bonus', 'refund') DEFAULT 'usage',
            `description` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // API usage logs
        "CREATE TABLE IF NOT EXISTS `api_usage_logs` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT UNSIGNED DEFAULT NULL,
            `endpoint` VARCHAR(255),
            `method` VARCHAR(10),
            `status_code` INT,
            `response_time` FLOAT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_endpoint` (`endpoint`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Settings table
        "CREATE TABLE IF NOT EXISTS `settings` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(255) NOT NULL UNIQUE,
            `value` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_key` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // User permissions
        "CREATE TABLE IF NOT EXISTS `user_permissions` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `permission` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_user_permission` (`user_id`, `permission`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Pictures table (legacy)
        "CREATE TABLE IF NOT EXISTS `pictures` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT UNSIGNED,
            `filename` VARCHAR(255),
            `original_name` VARCHAR(255),
            `file_path` VARCHAR(500),
            `file_size` BIGINT,
            `mime_type` VARCHAR(100),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    $createdTables = 0;
    $existingTables = 0;

    echo "<h2>Creating Database Tables...</h2>";
    echo "<ul>";

    foreach ($sqlStatements as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            echo "<li class='success'>✅ Table created/verified: " . ($tableName + 1) . "</li>";
            $createdTables++;
        } catch (PDOException $e) {
            echo "<li class='error'>❌ Error creating table " . ($tableName + 1) . ": " . htmlspecialchars($e->getMessage()) . "</li>";
        }
    }

    echo "</ul>";

    // Verify tables were created
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<div class='success'>";
    echo "<h2>✅ Database Setup Complete!</h2>";
    echo "<p><strong>Tables created:</strong> $createdTables</p>";
    echo "<p><strong>Total tables in database:</strong> " . count($tables) . "</p>";
    echo "<h3>Available Tables:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Database Setup Failed</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database credentials in config/config.php</p>";
    echo "</div>";
}

echo "<br>";
echo "<a href='web_setup.php' class='btn'>⬅️ Back to Setup</a>";
echo "<a href='web_create_admin.php' class='btn btn-success'>👤 Create Admin User</a>";
echo "<a href='/' class='btn'>🏠 Go to Home Page</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
