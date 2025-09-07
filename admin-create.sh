#!/bin/bash

# Define text colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the absolute path of the project directory (where this script is located)
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Print header
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   PictureThis - Create Admin User      ${NC}"
echo -e "${BLUE}========================================${NC}"

# Default admin user details
EMAIL="admin@picturethis.com"
PASSWORD="admin123"
FULL_NAME="Admin User"
CREDITS=1000

# Parse command line arguments
while [[ $# -gt 0 ]]; do
  key="$1"
  case $key in
    -e|--email)
      EMAIL="$2"
      shift
      shift
      ;;
    -p|--password)
      PASSWORD="$2"
      shift
      shift
      ;;
    -n|--name)
      FULL_NAME="$2"
      shift
      shift
      ;;
    -c|--credits)
      CREDITS="$2"
      shift
      shift
      ;;
    -h|--help)
      echo -e "Usage: $0 [options]"
      echo -e "Options:"
      echo -e "  -e, --email EMAIL      Admin email (default: admin@picturethis.com)"
      echo -e "  -p, --password PWD     Admin password (default: admin123)"
      echo -e "  -n, --name NAME        Full name (default: Admin User)"
      echo -e "  -c, --credits NUM      Initial credits (default: 1000)"
      echo -e "  -h, --help             Show this help message"
      exit 0
      ;;
    *)
      echo -e "${RED}Unknown option: $1${NC}"
      exit 1
      ;;
  esac
done

# Confirm creation
echo -e "${YELLOW}Creating admin user with the following details:${NC}"
echo -e "  Email: ${BLUE}$EMAIL${NC}"
echo -e "  Name: ${BLUE}$FULL_NAME${NC}"
echo -e "  Password: ${BLUE}$PASSWORD${NC}"
echo -e "  Credits: ${BLUE}$CREDITS${NC}"
echo
read -p "Continue? (y/N): " confirm
if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
  echo -e "${YELLOW}Operation cancelled.${NC}"
  exit 0
fi

# Create the admin user script
cat > "${PROJECT_DIR}/backend/create-admin-temp.js" << EOF
require('dotenv').config();
const { Pool } = require('pg');
const bcrypt = require('bcrypt');

// Admin user details
const userEmail = "${EMAIL}";
const userPassword = "${PASSWORD}";
const userFullName = "${FULL_NAME}";
const userCredits = ${CREDITS};

// Create a new pool using the environment variables
const pool = new Pool({
  host: process.env.DB_HOST,
  port: process.env.DB_PORT,
  database: process.env.DB_NAME,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD
});

console.log('DB Connection Details:');
console.log('- DB_HOST:', process.env.DB_HOST);
console.log('- DB_PORT:', process.env.DB_PORT);
console.log('- DB_NAME:', process.env.DB_NAME);

async function createAdminUser() {
  try {
    console.log('📊 Connecting to PostgreSQL database');
    
    // Check if user exists with direct SQL
    console.log('Checking if user exists...');
    const checkQuery = \`SELECT * FROM users WHERE email = '\${userEmail}'\`;
    const existingUser = await pool.query(checkQuery);
    
    if (existingUser.rows.length > 0) {
      console.log(\`⚠️ User with email \${userEmail} already exists\`);
      console.log('🔄 Updating to admin with new credentials');
      
      // Hash password
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash(userPassword, salt);
      
      // Update user with direct SQL
      const updateQuery = \`
        UPDATE users 
        SET password_hash = '\${hashedPassword}', 
            full_name = '\${userFullName}', 
            credits = \${userCredits}, 
            is_admin = TRUE, 
            email_verified = TRUE 
        WHERE email = '\${userEmail}'
      \`;
      await pool.query(updateQuery);
      
      console.log('✅ Admin user updated successfully');
    } else {
      // Hash password
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash(userPassword, salt);
      
      // Create user with direct SQL
      const insertQuery = \`
        INSERT INTO users 
        (email, password_hash, full_name, credits, is_admin, email_verified) 
        VALUES 
        ('\${userEmail}', '\${hashedPassword}', '\${userFullName}', \${userCredits}, TRUE, TRUE)
      \`;
      await pool.query(insertQuery);
      
      console.log('✅ Admin user created successfully');
    }
  } catch (error) {
    console.error('❌ Error creating admin user:', error);
    process.exit(1);
  } finally {
    await pool.end();
  }
  
  process.exit(0);
}

createAdminUser();
EOF

# Run the Node.js script from the backend directory
cd "$PROJECT_DIR/backend" && node create-admin-temp.js
RESULT=$?

# Delete the temporary script
rm -f "${PROJECT_DIR}/backend/create-admin-temp.js"

if [ $RESULT -eq 0 ]; then
  echo -e "${GREEN}Admin user created successfully!${NC}"
  echo -e "${BLUE}----------------------------------${NC}"
  echo -e "${YELLOW}Login Details:${NC}"
  echo -e "  Email: ${GREEN}$EMAIL${NC}"
  echo -e "  Password: ${GREEN}$PASSWORD${NC}"
else
  echo -e "${RED}Failed to create admin user!${NC}"
fi
