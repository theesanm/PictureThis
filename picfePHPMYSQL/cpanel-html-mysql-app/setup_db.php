<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/lib/db.php';

$pdo = get_db();

try {
    // Check if images table exists
    $result = $pdo->query('SHOW TABLES LIKE "images"');
    if ($result->rowCount() == 0) {
        echo 'Images table does not exist. Creating it...' . PHP_EOL;
        $pdo->exec('
            CREATE TABLE images (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                prompt TEXT,
                image_url TEXT NOT NULL,
                generation_cost INTEGER DEFAULT 10,
                has_usage_permission BOOLEAN DEFAULT FALSE,
                usage_confirmed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ');
        echo 'Images table created successfully!' . PHP_EOL;
    } else {
        echo 'Images table already exists.' . PHP_EOL;
    }

    // Check if credit_transactions table exists
    $result = $pdo->query('SHOW TABLES LIKE "credit_transactions"');
    if ($result->rowCount() == 0) {
        echo 'Credit transactions table does not exist. Creating it...' . PHP_EOL;
        $pdo->exec('
            CREATE TABLE credit_transactions (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                amount INTEGER NOT NULL,
                transaction_type VARCHAR(50) DEFAULT "usage",
                description TEXT,
                payment_id VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ');
        echo 'Credit transactions table created successfully!' . PHP_EOL;
    } else {
        echo 'Credit transactions table already exists.' . PHP_EOL;
    }

    // Check if settings table exists and has required columns
    $result = $pdo->query('SHOW TABLES LIKE "settings"');
    if ($result->rowCount() > 0) {
        // Check if we need to add new columns
        $columns = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN, 0);

        if (!in_array('credit_cost_per_image', $columns)) {
            $pdo->exec('ALTER TABLE settings ADD COLUMN credit_cost_per_image INTEGER DEFAULT 10');
            echo 'Added credit_cost_per_image column to settings table.' . PHP_EOL;
        }

        if (!in_array('enhanced_prompt_cost', $columns)) {
            $pdo->exec('ALTER TABLE settings ADD COLUMN enhanced_prompt_cost INTEGER DEFAULT 1');
            echo 'Added enhanced_prompt_cost column to settings table.' . PHP_EOL;
        }

        if (!in_array('enhanced_prompt_enabled', $columns)) {
            $pdo->exec('ALTER TABLE settings ADD COLUMN enhanced_prompt_enabled BOOLEAN DEFAULT TRUE');
            echo 'Added enhanced_prompt_enabled column to settings table.' . PHP_EOL;
        }

        if (!in_array('ai_provider', $columns)) {
            $pdo->exec('ALTER TABLE settings ADD COLUMN ai_provider VARCHAR(50) DEFAULT "openrouter"');
            echo 'Added ai_provider column to settings table.' . PHP_EOL;
        }

        // Insert default settings if not exists
        $settingsCheck = $pdo->query('SELECT COUNT(*) as count FROM settings')->fetch(PDO::FETCH_ASSOC);
        if ($settingsCheck['count'] == 0) {
            $pdo->exec('INSERT INTO settings (credit_cost_per_image, enhanced_prompt_cost, enhanced_prompt_enabled, ai_provider) VALUES (10, 1, TRUE, "openrouter")');
            echo 'Inserted default settings.' . PHP_EOL;
        }
    } else {
        echo 'Settings table does not exist. Please create it first.' . PHP_EOL;
    }

    echo 'Database setup complete!' . PHP_EOL;

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
