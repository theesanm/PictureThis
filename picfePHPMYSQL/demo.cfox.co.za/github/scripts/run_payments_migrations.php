<?php
// Simple migration runner for payments-related migrations
// Usage: php scripts/run_payments_migrations.php

require_once __DIR__ . '/../src/lib/db.php';
$pdo = get_db();

$migrations = [
    __DIR__ . '/../sql/migrations/20250910_create_payments_table.sql'
];

// Apply simple SQL migrations first
foreach ($migrations as $m) {
    if (!file_exists($m)) {
        echo "Migration not found: {$m}\n";
        continue;
    }
    $sql = file_get_contents($m);
    try {
        $stmts = preg_split('/;\s*\n/', $sql);
        foreach ($stmts as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') continue;
            $pdo->exec($stmt);
        }
        echo "Applied migration: {$m}\n";
    } catch (PDOException $e) {
        echo "Error applying {$m}: " . $e->getMessage() . "\n";
    }
}

// Now handle credit_transactions.payment_id migration in a compatible way
try {
    // Check if table exists
    $tbl = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'credit_transactions'");
    $tbl->execute();
    $exists = (int)$tbl->fetchColumn();
    if (!$exists) {
        echo "Table credit_transactions does not exist; skipping payment_id migration.\n";
    } else {
        // Check if column exists
        $col = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'credit_transactions' AND COLUMN_NAME = 'payment_id'");
        $col->execute();
        $colExists = (int)$col->fetchColumn();
        if ($colExists) {
            echo "Column payment_id already exists on credit_transactions.\n";
        } else {
            // Add the column (portable ALTER)
            try {
                $pdo->exec("ALTER TABLE credit_transactions ADD COLUMN payment_id VARCHAR(255) DEFAULT NULL");
                echo "Added column payment_id to credit_transactions.\n";
            } catch (PDOException $e) {
                echo "Error adding payment_id column: " . $e->getMessage() . "\n";
            }
        }

        // Ensure index exists on payment_id
        $idx = $pdo->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'credit_transactions' AND INDEX_NAME = 'idx_credit_transactions_payment_id'");
        $idx->execute();
        $idxExists = (int)$idx->fetchColumn();
        if ($idxExists) {
            echo "Index idx_credit_transactions_payment_id already exists.\n";
        } else {
            try {
                $pdo->exec("CREATE INDEX idx_credit_transactions_payment_id ON credit_transactions (payment_id)");
                echo "Created index idx_credit_transactions_payment_id.\n";
            } catch (PDOException $e) {
                echo "Error creating index: " . $e->getMessage() . "\n";
            }
        }
    }
} catch (PDOException $e) {
    echo "Error during credit_transactions migration checks: " . $e->getMessage() . "\n";
}

echo "Migrations complete.\n";
