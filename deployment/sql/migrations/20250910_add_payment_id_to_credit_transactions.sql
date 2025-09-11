-- Migration: 20250910_add_payment_id_to_credit_transactions.sql
-- Adds a nullable payment_id column and index to credit_transactions so ITN idempotency checks work
-- Run: php scripts/run_payments_migrations.php  (recommended) or mysql -u <user> -p <database> < 20250910_add_payment_id_to_credit_transactions.sql

-- Add column if it does not exist (MySQL safe-ish way)
ALTER TABLE credit_transactions 
  ADD COLUMN IF NOT EXISTS payment_id VARCHAR(255) DEFAULT NULL;

-- Create index if not exists (MySQL 8 syntax)
CREATE INDEX IF NOT EXISTS idx_credit_transactions_payment_id ON credit_transactions (payment_id);

-- If your MySQL version doesn't support IF NOT EXISTS for ALTER/CREATE INDEX, run the following manually:
-- ALTER TABLE credit_transactions ADD COLUMN payment_id VARCHAR(255) DEFAULT NULL;
-- CREATE INDEX idx_credit_transactions_payment_id ON credit_transactions(payment_id);
