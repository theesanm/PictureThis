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

# Create a temporary Node.js script file in the backend directory
TEMP_SCRIPT="${PROJECT_DIR}/backend/create-admin-temp.js"

# Create the admin user script with proper variable interpolation
cat > "$TEMP_SCRIPT" << EOF
// Load environment variables
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
    
    // First, let's check if the users table exists
    const tableCheck = await pool.query(\`
      SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_name = 'users'
      );
    \`);
    
    if (!tableCheck.rows[0].exists) {
      console.error('❌ Users table does not exist. Please run migrations first.');
      return process.exit(1);
    }
    
    // Get users table schema
    console.log('Checking users table schema...');
    const columns = await pool.query(\`
      SELECT column_name
      FROM information_schema.columns 
      WHERE table_schema = 'public'
      AND table_name = 'users';
    \`);
    
    const columnNames = columns.rows.map(col => col.column_name);
    console.log('Available columns:', columnNames.join(', '));
    
    // Check if required columns exist
    const requiredColumns = ['email', 'password_hash', 'full_name', 'credits', 'is_admin', 'email_verified'];
    const missingColumns = requiredColumns.filter(col => !columnNames.includes(col));
    
    if (missingColumns.length > 0) {
      console.error(\`❌ Missing required columns: \${missingColumns.join(', ')}\`);
      return process.exit(1);
    }
    
    // Check if user already exists
    console.log('Checking if user exists...');
    const existingUser = await pool.query('SELECT * FROM users WHERE email = $1', [userEmail]);
    
    if (existingUser.rows.length > 0) {
      console.log(\`⚠️ User with email \${userEmail} already exists\`);
      console.log('🔄 Updating to admin with new credentials');
      
      // Hash password
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash(userPassword, salt);
      
      // Update user
      await pool.query(
        'UPDATE users SET password_hash = $1, full_name = $2, credits = $3, is_admin = TRUE, email_verified = TRUE WHERE email = $4', 
        [hashedPassword, userFullName, userCredits, userEmail]
      );
      
      console.log('✅ Admin user updated successfully');
    } else {
      // Hash password
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash(userPassword, salt);
      
      // Create user
      await pool.query(
        'INSERT INTO users (email, password_hash, full_name, credits, is_admin, email_verified) VALUES ($1, $2, $3, $4, TRUE, TRUE)',
        [userEmail, hashedPassword, userFullName, userCredits]
      );
      
      console.log('✅ Admin user created successfully');
    }
  } catch (error) {
    console.error('❌ Error:', error);
    return process.exit(1);
  } finally {
    await pool.end();
  }
  
  process.exit(0);
}

createAdminUser();
EOF

# Run the Node.js script from within the backend directory
cd "$PROJECT_DIR/backend" && node create-admin-temp.js
RESULT=$?

# Delete the temporary script
rm -f "$TEMP_SCRIPT"

if [ $RESULT -eq 0 ]; then
  echo -e "${GREEN}Admin user created successfully!${NC}"
  echo -e "${BLUE}----------------------------------${NC}"
  echo -e "${YELLOW}Login Details:${NC}"
  echo -e "  Email: ${GREEN}$EMAIL${NC}"
  echo -e "  Password: ${GREEN}$PASSWORD${NC}"
else
  echo -e "${RED}Failed to create admin user!${NC}"
fi
