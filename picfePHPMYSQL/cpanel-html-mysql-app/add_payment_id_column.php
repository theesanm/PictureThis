<?php
// Add payment_id column to credit_transactions table
require_once __DIR__ . '/src/lib/db.php';

try {
    $pdo = get_db();

    // Check if column exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
                          WHERE TABLE_SCHEMA = DATABASE()
                          AND TABLE_NAME = 'credit_transactions'
                          AND COLUMN_NAME = 'payment_id'");
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo "✅ payment_id column already exists\n";
    } else {
        // Add the column
        $pdo->exec("ALTER TABLE credit_transactions ADD COLUMN payment_id VARCHAR(255) DEFAULT NULL");
        echo "✅ Added payment_id column to credit_transactions\n";

        // Add index
        $pdo->exec("CREATE INDEX idx_credit_transactions_payment_id ON credit_transactions (payment_id)");
        echo "✅ Added index on payment_id\n";
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
