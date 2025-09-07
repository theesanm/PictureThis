require('dotenv').config({ path: '../.env' });
const bcrypt = require('bcrypt');
const { query } = require('../src/utils/database');

async function createAdminUser() {
  try {
    // Admin user details
    const email = 'admin@picturethis.com';
    const password = 'Admin@PictureThis2025';
    const fullName = 'PictureThis Admin';
    const initialCredits = 1000;
    
    // Check if user already exists
    const existingUser = await query('SELECT * FROM users WHERE email = $1', [email]);
    
    if (existingUser.rows.length > 0) {
      console.log('Admin user already exists. Updating to admin role and refreshing password.');
      
      // Hash password
      const saltRounds = 12;
      const passwordHash = await bcrypt.hash(password, saltRounds);
      
      // Update existing user to be admin
      await query(
        'UPDATE users SET password_hash = $1, is_admin = TRUE, email_verified = TRUE, credits = $2 WHERE email = $3',
        [passwordHash, initialCredits, email]
      );
      
      console.log('Admin user updated successfully!');
    } else {
      // Hash password
      const saltRounds = 12;
      const passwordHash = await bcrypt.hash(password, saltRounds);
      
      // Create new admin user
      await query(
        `INSERT INTO users (email, password_hash, full_name, credits, is_admin, email_verified) 
         VALUES ($1, $2, $3, $4, $5, $6)`,
        [email, passwordHash, fullName, initialCredits, true, true]
      );
      
      console.log('Admin user created successfully!');
    }
    
    console.log('\nAdmin Login Details:');
    console.log('---------------------');
    console.log('Email: admin@picturethis.com');
    console.log('Password: Admin@PictureThis2025');
    console.log('---------------------');
    console.log('Please save these details securely.');
    
  } catch (error) {
    console.error('Error creating admin user:', error);
  } finally {
    // Exit process
    process.exit(0);
  }
}

createAdminUser();
