-- Database Setup Script for PictureThis Agent User
-- This script creates the pt_agent user with proper permissions

-- Create the agent user for development
CREATE USER IF NOT EXISTS 'pt_agent'@'localhost' IDENTIFIED BY 'agent_secure_2025';
CREATE USER IF NOT EXISTS 'pt_agent'@'%' IDENTIFIED BY 'agent_secure_2025';

-- Grant all privileges on the development database
GRANT ALL PRIVILEGES ON picturethis_dev.* TO 'pt_agent'@'localhost';
GRANT ALL PRIVILEGES ON picturethis_dev.* TO 'pt_agent'@'%';

-- Grant all privileges on the production database (if it exists)
GRANT ALL PRIVILEGES ON cfoxcozj_PictureThis.* TO 'pt_agent'@'localhost';
GRANT ALL PRIVILEGES ON cfoxcozj_PictureThis.* TO 'pt_agent'@'%';

-- Grant specific permissions for security (alternative to ALL PRIVILEGES)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON picturethis_dev.* TO 'pt_agent'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON picturethis_dev.* TO 'pt_agent'@'%';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON cfoxcozj_PictureThis.* TO 'pt_agent'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON cfoxcozj_PictureThis.* TO 'pt_agent'@'%';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Show created users
SELECT User, Host FROM mysql.user WHERE User = 'pt_agent';