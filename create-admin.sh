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

# Create admin user using Node.js script
NODE_SCRIPT=$(cat << EOF
// Check if bcrypt is installed, if not install it
try {
  require.resolve('bcrypt');
} catch (e) {
  console.log('Installing bcrypt...');
  require('child_process').execSync('npm install --no-save bcrypt');
  console.log('✅ Installed bcrypt');
}

const bcrypt = require('bcrypt');
const { query } = require('./backend/src/utils/database');

async function createAdminUser() {
  try {
    console.log('📊 Connected to PostgreSQL database');
    
    // Check if user already exists
    const existingUser = await query('SELECT * FROM users WHERE email = $1', ['$EMAIL']);
    if (existingUser.rows.length > 0) {
      console.log('⚠️ User with email $EMAIL already exists');
      console.log('🔄 Updating to admin with new credentials');
      
      // Hash password
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash('$PASSWORD', salt);
      
      // Update user
      await query(
        'UPDATE users SET password_hash = $1, full_name = $2, credits = $3, is_admin = TRUE, email_verified = TRUE WHERE email = $4', 
        [hashedPassword, '$FULL_NAME', $CREDITS, '$EMAIL']
      );
      
      console.log('✅ Admin user updated successfully');
    } else {
      // Hash password
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash('$PASSWORD', salt);
      
      // Create user
      await query(
        'INSERT INTO users (email, password_hash, full_name, credits, is_admin, email_verified) VALUES ($1, $2, $3, $4, TRUE, TRUE)',
        ['$EMAIL', hashedPassword, '$FULL_NAME', $CREDITS]
      );
      
      console.log('✅ Admin user created successfully');
    }
  } catch (error) {
    console.error('❌ Error creating admin user:', error);
    process.exit(1);
  }
  
  process.exit(0);
}

createAdminUser();
EOF
)

# Run the Node.js script
cd "$PROJECT_DIR" && node -e "$NODE_SCRIPT"

if [ $? -eq 0 ]; then
  echo -e "${GREEN}Admin user created successfully!${NC}"
  echo -e "${BLUE}----------------------------------${NC}"
  echo -e "${YELLOW}Login Details:${NC}"
  echo -e "  Email: ${GREEN}$EMAIL${NC}"
  echo -e "  Password: ${GREEN}$PASSWORD${NC}"
else
  echo -e "${RED}Failed to create admin user!${NC}"
fi
