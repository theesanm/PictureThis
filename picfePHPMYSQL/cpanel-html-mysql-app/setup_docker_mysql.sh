#!/bin/bash
# Docker MySQL Setup Script for PictureThis Agent User
# This script sets up MySQL in Docker and creates the agent user

echo "🐳 Setting up Docker MySQL for PictureThis..."
echo "=============================================="

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed."
    echo "📋 Install Docker:"
    echo "   macOS: https://docs.docker.com/desktop/install/mac/"
    echo "   Ubuntu: sudo apt update && sudo apt install docker.io"
    exit 1
fi

# Check if Docker is running
if ! docker info &> /dev/null; then
    echo "❌ Docker is not running. Please start Docker Desktop."
    exit 1
fi

echo "🔧 Starting MySQL container..."
docker run --name mysql-dev \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    -e MYSQL_DATABASE=picturethis_dev \
    -p 3306:3306 \
    -d mysql:8.0

if [ $? -ne 0 ]; then
    echo "❌ Failed to start MySQL container. It might already exist."
    echo "🔄 Trying to start existing container..."
    docker start mysql-dev
fi

echo "⏳ Waiting for MySQL to be ready..."
sleep 10

echo "🔧 Setting up agent user..."
docker exec -i mysql-dev mysql -u root << 'EOF'
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
    echo "✅ Docker MySQL setup completed successfully!"
    echo ""
    echo "📋 Connection Details:"
    echo "   Host: 127.0.0.1:3306"
    echo "   Username: pt_agent"
    echo "   Password: agent_secure_2025"
    echo "   Database: picturethis_dev"
    echo ""
    echo "🔍 Test the connection:"
    echo "   http://localhost:8000/test_db_simple.php"
    echo ""
    echo "🐳 Docker Commands:"
    echo "   View logs: docker logs mysql-dev"
    echo "   Stop: docker stop mysql-dev"
    echo "   Start: docker start mysql-dev"
    echo "   Remove: docker rm mysql-dev"
else
    echo "❌ Failed to setup agent user in Docker MySQL."
    exit 1
fi