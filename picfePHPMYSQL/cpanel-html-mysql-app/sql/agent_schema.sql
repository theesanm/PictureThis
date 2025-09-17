-- Interactive Prompt Enhancement Agent - Database Schema
-- Phase 1: Agent Session Management Tables
-- Updated: September 16, 2025
-- MySQL 5.7 Compatible Version - Simplified

-- Add agent session tracking to users table
-- Check if column exists before adding
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'current_agent_session_id') = 0,
    'ALTER TABLE users ADD COLUMN current_agent_session_id VARCHAR(255) NULL',
    'SELECT "Column current_agent_session_id already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_current_agent_session') = 0,
    'ALTER TABLE users ADD INDEX idx_current_agent_session (current_agent_session_id)',
    'SELECT "Index idx_current_agent_session already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- New table for agent sessions
CREATE TABLE IF NOT EXISTS prompt_agent_sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INT NOT NULL,  -- Match users.id type (INT)
    original_prompt TEXT NOT NULL,
    session_status ENUM('active', 'completed', 'expired') DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    total_llm_calls INT DEFAULT 0,
    total_credits_used INT DEFAULT 0,
    last_activity_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    session_metadata TEXT NULL,  -- Use TEXT instead of JSON for MySQL 5.7 compatibility
    INDEX idx_user_id (user_id),
    INDEX idx_status (session_status),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at),
    INDEX idx_last_activity (last_activity_at),
    INDEX idx_user_status (user_id, session_status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- New table for agent conversation messages
CREATE TABLE IF NOT EXISTS prompt_agent_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    message_type ENUM('user', 'agent', 'system') NOT NULL,
    content TEXT NOT NULL,
    suggested_prompts TEXT NULL,  -- Use TEXT instead of JSON for MySQL 5.7 compatibility
    credits_used INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    message_metadata TEXT NULL,  -- Use TEXT instead of JSON for MySQL 5.7 compatibility
    INDEX idx_session_id (session_id),
    INDEX idx_message_type (message_type),
    INDEX idx_created_at (created_at),
    INDEX idx_session_created (session_id, created_at),
    FOREIGN KEY (session_id) REFERENCES prompt_agent_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update settings for agent functionality
-- Update enhanced_prompt_cost if it's 0 (default)
UPDATE settings SET enhanced_prompt_cost = 1 WHERE enhanced_prompt_cost = 0;

-- Create additional indexes (these may fail if they already exist - that's OK)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'prompt_agent_sessions' AND INDEX_NAME = 'idx_sessions_user_recent') = 0,
    'CREATE INDEX idx_sessions_user_recent ON prompt_agent_sessions (user_id, created_at)',
    'SELECT "Index idx_sessions_user_recent already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'prompt_agent_sessions' AND INDEX_NAME = 'idx_sessions_expiring') = 0,
    'CREATE INDEX idx_sessions_expiring ON prompt_agent_sessions (expires_at)',
    'SELECT "Index idx_sessions_expiring already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'prompt_agent_messages' AND INDEX_NAME = 'idx_messages_session_recent') = 0,
    'CREATE INDEX idx_messages_session_recent ON prompt_agent_messages (session_id, created_at)',
    'SELECT "Index idx_messages_session_recent already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;