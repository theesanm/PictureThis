-- MySQL-compatible schema converted from backend migrations
-- Use with: mysql -u root -p picturethis_dev < sql/schema_mysql.sql

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS api_usage_logs;
DROP TABLE IF EXISTS user_permissions;
DROP TABLE IF EXISTS images;
DROP TABLE IF EXISTS credit_transactions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;

SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(255),
  credits INT DEFAULT 0,
  is_admin TINYINT(1) DEFAULT 0,
  email_verified TINYINT(1) DEFAULT 0,
  email_verification_token VARCHAR(255),
  email_verification_expires DATETIME DEFAULT NULL,
  reset_password_token VARCHAR(255),
  reset_password_expires DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE credit_transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount INT NOT NULL,
  transaction_type VARCHAR(50) NOT NULL,
  stripe_payment_id VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id),
  CONSTRAINT fk_credit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  prompt TEXT NOT NULL,
  image_url VARCHAR(500),
  image_data LONGBLOB,
  file_name VARCHAR(255),
  file_size INT,
  generation_cost INT DEFAULT 10,
  has_usage_permission TINYINT(1) DEFAULT 0,
  usage_confirmed_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id),
  CONSTRAINT fk_images_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE api_usage_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  endpoint VARCHAR(255) NOT NULL,
  method VARCHAR(10) NOT NULL,
  status_code INT,
  response_time INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id),
  CONSTRAINT fk_api_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  credit_cost_per_image INT NOT NULL DEFAULT 10,
  max_free_credits INT NOT NULL DEFAULT 50,
  stripe_enabled TINYINT(1) DEFAULT 0,
  enhanced_prompt_enabled TINYINT(1) DEFAULT 1,
  enhanced_prompt_cost INT DEFAULT 0,
  ai_provider VARCHAR(50) DEFAULT 'openrouter',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings if table is empty
INSERT INTO settings (credit_cost_per_image, max_free_credits, stripe_enabled, enhanced_prompt_enabled, enhanced_prompt_cost, ai_provider)
SELECT 10,50,0,1,0,'openrouter' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM settings);

CREATE TABLE user_permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  permission_type VARCHAR(50) NOT NULL,
  accepted TINYINT(1) DEFAULT 0,
  acceptance_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45),
  user_agent TEXT,
  INDEX (user_id),
  CONSTRAINT fk_perm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- End of schema
