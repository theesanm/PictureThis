#!/bin/bash

# Colors for better readability
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}==============================================${NC}"
echo -e "${YELLOW}       RESET DATABASE SCRIPT                  ${NC}"
echo -e "${YELLOW}==============================================${NC}"
echo -e "${RED}WARNING: This will DELETE ALL DATA in the database!${NC}"
echo -e "${RED}All tables will be dropped and recreated.${NC}"
echo -e ""
read -p "Are you sure you want to continue? (y/N): " confirm

if [[ $confirm != [yY] && $confirm != [yY][eE][sS] ]]; then
    echo -e "${GREEN}Database reset cancelled.${NC}"
    exit 0
fi

cd "$(dirname "$0")/backend"

# Create a temporary file with a script to drop and recreate tables
cat > reset-db.js << EOL
const { dropTables, createTables } = require('./src/utils/migrate');

async function resetDatabase() {
  try {
    console.log('🗑️  Dropping all database tables...');
    await dropTables();
    
    console.log('🏗️  Creating database tables...');
    await createTables();
    
    console.log('✅ Database reset complete!');
    process.exit(0);
  } catch (error) {
    console.error('❌ Database reset failed:', error);
    process.exit(1);
  }
}

// Load environment variables
require('dotenv').config({ path: '.env' });
resetDatabase();
EOL

# Run the script
echo -e "${YELLOW}Starting database reset...${NC}"
node reset-db.js

# Clean up
rm reset-db.js

echo -e "${GREEN}Database reset process completed.${NC}"
