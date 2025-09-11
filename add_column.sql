ALTER TABLE credit_transactions ADD COLUMN payment_id VARCHAR(255) DEFAULT NULL; CREATE INDEX idx_credit_transactions_payment_id ON credit_transactions (payment_id);
