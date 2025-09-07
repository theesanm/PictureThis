// Create admin user with explicit db connection
const path = require('path');

// Change working directory to backend
process.chdir(path.join(__dirname, 'backend'));

// Use backend's node_modules
require('dotenv').config();
const { Pool } = require('pg');
const bcrypt = require('bcrypt');

// Admin user details
const userEmail = "admin@picturethis.com";
const userPassword = "admin123";
const userFullName = "Admin User";
const userCredits = 1000;

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
    const tableCheck = await pool.query(`
      SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_name = 'users'
      );
    `);
    
    if (!tableCheck.rows[0].exists) {
      console.error('❌ Users table does not exist. Please run migrations first.');
      return process.exit(1);
    }
    
    // Get users table schema
    console.log('Checking users table schema...');
    const columns = await pool.query(`
      SELECT column_name
      FROM information_schema.columns 
      WHERE table_schema = 'public'
      AND table_name = 'users';
    `);
    
    const columnNames = columns.rows.map(col => col.column_name);
    console.log('Available columns:', columnNames.join(', '));
    
    // Check if required columns exist
    const requiredColumns = ['email', 'password_hash', 'full_name', 'credits', 'is_admin', 'email_verified'];
    const missingColumns = requiredColumns.filter(col => !columnNames.includes(col));
    
    if (missingColumns.length > 0) {
      console.error(`❌ Missing required columns: ${missingColumns.join(', ')}`);
      return process.exit(1);
    }
    
    // Check if user already exists
    console.log('Checking if user exists...');
    const existingUser = await pool.query('SELECT * FROM users WHERE email = $1', [userEmail]);
    
    if (existingUser.rows.length > 0) {
      console.log(`⚠️ User with email ${userEmail} already exists`);
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
