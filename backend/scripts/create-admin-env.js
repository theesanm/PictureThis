require('dotenv').config({ path: '../.env' });
const bcrypt = require('bcrypt');
const { query } = require('../src/utils/database');

async function createAdminUser() {
  try {
    const email = process.env.NEW_ADMIN_EMAIL || 'admin@picturethis.com';
    const password = process.env.NEW_ADMIN_PASSWORD || 'Admin@PictureThis2025';
    const fullName = process.env.NEW_ADMIN_NAME || 'PictureThis Admin';
    const initialCredits = parseInt(process.env.NEW_ADMIN_CREDITS || '1000', 10);

    const existingUser = await query('SELECT * FROM users WHERE email = $1', [email]);
    const saltRounds = 12;
    const passwordHash = await bcrypt.hash(password, saltRounds);

    if (existingUser.rows.length > 0) {
      await query(
        'UPDATE users SET password_hash = $1, is_admin = TRUE, email_verified = TRUE, credits = $2 WHERE email = $3',
        [passwordHash, initialCredits, email]
      );
      console.log('Admin user updated successfully!');
    } else {
      await query(
        `INSERT INTO users (email, password_hash, full_name, credits, is_admin, email_verified) \
         VALUES ($1, $2, $3, $4, $5, $6)`,
        [email, passwordHash, fullName, initialCredits, true, true]
      );
      console.log('Admin user created successfully!');
    }

    console.log('\nAdmin Login Details:');
    console.log('---------------------');
    console.log('Email:', email);
    console.log('Password:', password);
    console.log('Name:', fullName);
    console.log('Credits:', initialCredits);
    console.log('---------------------');
  } catch (error) {
    console.error('Error creating admin user:', error);
  } finally {
    process.exit(0);
  }
}

createAdminUser();
