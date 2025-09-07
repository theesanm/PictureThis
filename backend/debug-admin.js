// A simple script to debug the SQL and create an admin user
require('dotenv').config();
const { Pool } = require('pg');

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
    
    // Let's try a simpler query first
    console.log('Testing simple query...');
    await pool.query('SELECT 1');
    console.log('✅ Simple query successful');
    
    // Check if user exists with explicit SQL (no parameterized query)
    console.log('Trying SELECT with full query...');
    const checkQuery = `SELECT * FROM users WHERE email = '${userEmail}'`;
    console.log('Query:', checkQuery);
    const checkResult = await pool.query(checkQuery);
    console.log('✅ User check successful, found', checkResult.rows.length, 'rows');
    
    // Create a bcrypt hash for the password
    const bcrypt = require('bcrypt');
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(userPassword, salt);
    console.log('✅ Password hash created');
    
    if (checkResult.rows.length > 0) {
      // Explicit update query
      console.log('Updating existing user...');
      const updateQuery = `
        UPDATE users 
        SET password_hash = '${hashedPassword}', 
            full_name = '${userFullName}', 
            credits = ${userCredits}, 
            is_admin = TRUE, 
            email_verified = TRUE 
        WHERE email = '${userEmail}'
      `;
      console.log('Update Query:', updateQuery);
      await pool.query(updateQuery);
      console.log('✅ Admin user updated successfully');
    } else {
      // Explicit insert query
      console.log('Creating new user...');
      const insertQuery = `
        INSERT INTO users 
        (email, password_hash, full_name, credits, is_admin, email_verified) 
        VALUES 
        ('${userEmail}', '${hashedPassword}', '${userFullName}', ${userCredits}, TRUE, TRUE)
      `;
      console.log('Insert Query:', insertQuery);
      await pool.query(insertQuery);
      console.log('✅ Admin user created successfully');
    }
    
  } catch (error) {
    console.error('❌ Error:', error);
  } finally {
    await pool.end();
  }
}

createAdminUser();
