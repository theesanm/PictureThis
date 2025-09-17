<?php
/**
 * Database Setup Script for PictureThis
 * Run this script via phpMyAdmin or directly via PHP to create all required tables
 *
 * Usage:
 * 1. Via phpMyAdmin: Copy and paste the SQL statements
 * 2. Via PHP: Upload this file and run it in your browser
 * 3. Via command line: php setup_database.php
 */

// Load configuration
require_once __DIR__ . '/config/config.php';

// Get database config from loaded configuration
$dbConfig = $config['database'];
$host = $dbConfig['host'];
$dbname = $dbConfig['name'];
$username = $dbConfig['user'];
$password = $dbConfig['pass'];

echo "<h1>PictureThis Database Setup</h1>";
echo "<pre>";
echo "🔍 Using database: $dbname on $host\n";
echo "👤 Using user: $username\n\n";

// Connect to MySQL (without selecting a database first)
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Connected to MySQL server successfully!\n\n";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage() . "\n");
}

// Create database if it doesn't exist
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$dbname' created or already exists!\n";
} catch (PDOException $e) {
    die("❌ Error creating database: " . $e->getMessage() . "\n");
}

// Select the database
$pdo->exec("USE `$dbname`");
echo "✅ Using database '$dbname'\n\n";

// SQL statements to create all tables
$sqlStatements = [

    // Users table
    "CREATE TABLE IF NOT EXISTS `users` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Images table
    "CREATE TABLE IF NOT EXISTS `images` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Credit transactions table
    "CREATE TABLE IF NOT EXISTS `credit_transactions` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Settings table
    "CREATE TABLE IF NOT EXISTS `settings` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Payments table (for PayFast integration)
    "CREATE TABLE IF NOT EXISTS `payments` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Execute each SQL statement
$tablesCreated = 0;
$tablesSkipped = 0;

foreach ($sqlStatements as $index => $sql) {
    try {
        $pdo->exec($sql);

        // Extract table name from SQL for better output
        if (preg_match('/CREATE TABLE.*?`(\w+)`/', $sql, $matches)) {
            $tableName = $matches[1];
            echo "✅ Table '$tableName' created or already exists!\n";
            $tablesCreated++;
        }
    } catch (PDOException $e) {
        echo "❌ Error creating table " . ($index + 1) . ": " . $e->getMessage() . "\n";
    }
}

// Insert default settings if settings table is empty
try {
    $settingsCount = $pdo->query("SELECT COUNT(*) as count FROM settings")->fetch()['count'];
    if ($settingsCount == 0) {
        $pdo->exec("INSERT INTO settings (k, v, credit_cost_per_image, enhanced_prompt_cost, enhanced_prompt_enabled, ai_provider) VALUES
            ('credit_cost_per_image', '10', 10, 1, TRUE, 'openrouter'),
            ('enhanced_prompt_cost', '1', 10, 1, TRUE, 'openrouter'),
            ('enhanced_prompt_enabled', 'true', 10, 1, TRUE, 'openrouter'),
            ('ai_provider', 'openrouter', 10, 1, TRUE, 'openrouter')");
        echo "\n✅ Default settings inserted!\n";
    } else {
        echo "\nℹ️  Settings table already has data, skipping default insertion.\n";
    }
} catch (PDOException $e) {
    echo "\n❌ Error inserting default settings: " . $e->getMessage() . "\n";
}

// Create a test user (optional)
try {
    $testUserExists = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@picturethis.com'")->fetch()['count'];
    if ($testUserExists == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (full_name, email, password_hash, credits, created_at) VALUES
            ('Admin User', 'admin@picturethis.com', '$hashedPassword', 100, NOW())");
        echo "✅ Test admin user created! Email: admin@picturethis.com, Password: admin123\n";
    } else {
        echo "ℹ️  Test admin user already exists.\n";
    }
} catch (PDOException $e) {
    echo "❌ Error creating test user: " . $e->getMessage() . "\n";
}

echo "\n🎉 Database setup completed!\n";
echo "📊 Summary:\n";
echo "   - Tables processed: " . count($sqlStatements) . "\n";
echo "   - Tables created/skipped: $tablesCreated\n";
echo "   - Default settings: ✅\n";
echo "   - Test user: ✅\n\n";

echo "🔗 Next steps:\n";
echo "1. Update your config/config.php with the correct database credentials\n";
echo "2. Test your application by visiting your website\n";
echo "3. Create additional users through the registration form\n\n";

echo "</pre>";
echo "<style>body{font-family:monospace;background:#f5f5f5;padding:20px;}h1{color:#333;margin-bottom:20px;}</style>";
?>
