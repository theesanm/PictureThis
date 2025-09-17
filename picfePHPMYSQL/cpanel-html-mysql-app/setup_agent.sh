#!/bin/bash
# Setup Agent User Script for PictureThis
# This script creates the pt_agent database user with proper permissions

echo "🛠️  Setting up PictureThis Agent User..."
echo "========================================"

# Check if MySQL is running
if ! pgrep mysqld > /dev/null && ! pgrep mysql > /dev/null; then
    echo "❌ MySQL is not running locally."
    echo ""
    echo "📋 To set up the agent user, you have several options:"
    echo ""
    echo "🔧 Option 1: Local MySQL Installation"
    echo "   1. Install MySQL: brew install mysql (macOS) or apt install mysql-server (Ubuntu)"
    echo "   2. Start MySQL: brew services start mysql (macOS) or sudo systemctl start mysql (Ubuntu)"
    echo "   3. Run this script again: ./setup_agent.sh"
    echo ""
    echo "🐳 Option 2: Docker MySQL"
    echo "   1. Install Docker if not installed"
    echo "   2. Start MySQL container:"
    echo "      docker run --name mysql-dev -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \\"
    echo "                 -e MYSQL_DATABASE=picturethis_dev -p 3306:3306 -d mysql:8.0"
    echo "   3. Run setup with Docker:"
    echo "      docker exec -i mysql-dev mysql -u root < setup_agent_user.sql"
    echo ""
    echo "📝 Option 3: Manual MySQL Commands"
    echo "   Connect to your MySQL server and run:"
    echo "   mysql -u root -p < setup_agent_user.sql"
    echo ""
    exit 1
fi

# Check if we're running as root or have sudo
if [[ $EUID -eq 0 ]]; then
    MYSQL_CMD="mysql"
else
    MYSQL_CMD="mysql"
fi

echo "🔧 Creating agent user and granting permissions..."

# Run the SQL setup script
$MYSQL_CMD -u root << 'EOF'
-- Create the agent user for development
CREATE USER IF NOT EXISTS 'pt_agent'@'localhost' IDENTIFIED BY 'agent_secure_2025';
CREATE USER IF NOT EXISTS 'pt_agent'@'%' IDENTIFIED BY 'agent_secure_2025';

-- Grant all privileges on the development database
GRANT ALL PRIVILEGES ON picturethis_dev.* TO 'pt_agent'@'localhost';
GRANT ALL PRIVILEGES ON picturethis_dev.* TO 'pt_agent'@'%';

-- Grant all privileges on the production database (if it exists)
GRANT ALL PRIVILEGES ON cfoxcozj_PictureThis.* TO 'pt_agent'@'localhost';
GRANT ALL PRIVILEGES ON cfoxcozj_PictureThis.* TO 'pt_agent'@'%';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Show created users
SELECT User, Host FROM mysql.user WHERE User = 'pt_agent';
EOF

if [ $? -eq 0 ]; then
    echo "✅ Agent user setup completed successfully!"
    echo ""
    echo "📋 Agent User Details:"
    echo "   Username: pt_agent"
    echo "   Password: agent_secure_2025"
    echo "   Host: localhost and % (any host)"
    echo ""
    echo "🔍 Test the connection:"
    echo "   http://localhost:8000/test_db_simple.php"
    echo ""
    echo "🚀 Your application is now configured to use the agent user!"
else
    echo "❌ Failed to setup agent user. Please check MySQL permissions."
    exit 1
fi