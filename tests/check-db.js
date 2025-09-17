const path = require('path');

// Check if dotenv is installed, if not install it
try {
  require.resolve('dotenv');
} catch (e) {
  console.log('Installing dotenv...');
  require('child_process').execSync('npm install --no-save dotenv');
  console.log('✅ Installed dotenv');
}

const dotenv = require('dotenv');
// Load .env from the backend directory
dotenv.config({ path: path.join(__dirname, 'backend', '.env') });

// Check if pg is installed, if not install it
try {
  require.resolve('pg');
} catch (e) {
  console.log('Installing pg...');
  require('child_process').execSync('npm install --no-save pg');
  console.log('✅ Installed pg');
}

const { Pool } = require('pg');

// Create a new pool using the environment variables
const pool = new Pool({
  host: process.env.DB_HOST,
  port: process.env.DB_PORT,
  database: process.env.DB_NAME,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD
});

async function checkDatabase() {
  try {
    console.log('DB Connection Details:');
    console.log('- DB_HOST:', process.env.DB_HOST);
    console.log('- DB_PORT:', process.env.DB_PORT);
    console.log('- DB_NAME:', process.env.DB_NAME);
    
    console.log('📊 Connecting to PostgreSQL database');
    
    // Check if users table exists
    const tablesResult = await pool.query(`
      SELECT table_name 
      FROM information_schema.tables 
      WHERE table_schema = 'public' 
      ORDER BY table_name;
    `);
    
    console.log('Available tables:');
    tablesResult.rows.forEach(row => {
      console.log('- ' + row.table_name);
    });
    
    // Check for users table
    const userTable = tablesResult.rows.find(row => row.table_name === 'users');
    if (userTable) {
      console.log('\nQuerying users table schema:');
      
      const tableSchema = await pool.query(`
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'users';
      `);
      
      tableSchema.rows.forEach(col => {
        console.log(`- ${col.column_name} (${col.data_type}, nullable: ${col.is_nullable})`);
      });
    } else {
      console.log('\nUsers table does not exist. You may need to run migrations.');
    }
  } catch (error) {
    console.error('❌ Database error:', error);
  } finally {
    await pool.end();
  }
}

checkDatabase();
