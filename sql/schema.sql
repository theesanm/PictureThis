-- sql/schema.sql
-- Example schema for the pictures table used by the simple cPanel PHP app
CREATE TABLE IF NOT EXISTS pictures (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  image_url VARCHAR(512),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
