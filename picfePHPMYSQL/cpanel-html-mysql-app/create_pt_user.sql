-- Create pt_user for development (same credentials as production logic)
-- Run this in your Docker MySQL container or via Adminer

CREATE USER IF NOT EXISTS 'pt_user'@'%' IDENTIFIED BY 'pt_pass';
GRANT ALL PRIVILEGES ON picturethis_dev.* TO 'pt_user'@'%';
GRANT ALL PRIVILEGES ON cfoxcozj_PictureThis.* TO 'pt_user'@'%';
FLUSH PRIVILEGES;

-- Verify user was created
SELECT User, Host FROM mysql.user WHERE User = 'pt_user';