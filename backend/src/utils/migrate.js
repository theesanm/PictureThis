const { query } = require('./database');

const createTables = async () => {
  try {
    // Users table
    await query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        credits INTEGER DEFAULT 0,
        is_admin BOOLEAN DEFAULT FALSE,
        email_verified BOOLEAN DEFAULT FALSE,
        email_verification_token VARCHAR(255),
        email_verification_expires TIMESTAMP,
        reset_password_token VARCHAR(255),
        reset_password_expires TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Credit transactions table
    await query(`
      CREATE TABLE IF NOT EXISTS credit_transactions (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        amount INTEGER NOT NULL,
        transaction_type VARCHAR(50) NOT NULL, -- 'purchase', 'usage', 'refund'
        stripe_payment_id VARCHAR(255),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Images table
    await query(`
      CREATE TABLE IF NOT EXISTS images (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        prompt TEXT NOT NULL,
        image_url VARCHAR(500),
        image_data BYTEA,
        file_name VARCHAR(255),
        file_size INTEGER,
        generation_cost INTEGER DEFAULT 10,
        has_usage_permission BOOLEAN DEFAULT false,
        usage_confirmed_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // API usage logs
    await query(`
      CREATE TABLE IF NOT EXISTS api_usage_logs (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        endpoint VARCHAR(255) NOT NULL,
        method VARCHAR(10) NOT NULL,
        status_code INTEGER,
        response_time INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Settings table for admin configuration
    await query(`
      CREATE TABLE IF NOT EXISTS settings (
        id SERIAL PRIMARY KEY,
        credit_cost_per_image INTEGER NOT NULL DEFAULT 10,
        max_free_credits INTEGER NOT NULL DEFAULT 50,
        stripe_enabled BOOLEAN DEFAULT FALSE,
        enhanced_prompt_enabled BOOLEAN DEFAULT TRUE,
        enhanced_prompt_cost INTEGER DEFAULT 0,
        ai_provider VARCHAR(50) DEFAULT 'openrouter',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Check if the settings table exists but has no rows
    const settingsCount = await query(`SELECT COUNT(*) FROM settings`);
    if (parseInt(settingsCount.rows[0].count) === 0) {
      console.log('✅ Creating default settings...');
      await query(`
        INSERT INTO settings (
          credit_cost_per_image, 
          max_free_credits, 
          stripe_enabled, 
          enhanced_prompt_enabled, 
          enhanced_prompt_cost,
          ai_provider
        ) VALUES (10, 50, false, true, 0, 'openrouter')
      `);
    }
    
    // User permission acceptance table
    await query(`
      CREATE TABLE IF NOT EXISTS user_permissions (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        permission_type VARCHAR(50) NOT NULL, -- e.g., 'image_usage'
        accepted BOOLEAN DEFAULT false,
        acceptance_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45),
        user_agent TEXT
      )
    `);

    // Default settings are inserted in the settings table section above

    console.log('✅ Database tables created successfully');
  } catch (error) {
    console.error('❌ Error creating tables:', error);
    throw error;
  }
};

const dropTables = async () => {
  try {
    await query('DROP TABLE IF EXISTS api_usage_logs CASCADE');
    await query('DROP TABLE IF EXISTS user_permissions CASCADE');
    await query('DROP TABLE IF EXISTS images CASCADE');
    await query('DROP TABLE IF EXISTS credit_transactions CASCADE');
    await query('DROP TABLE IF EXISTS users CASCADE');
    await query('DROP TABLE IF EXISTS settings CASCADE');
    console.log('✅ Database tables dropped successfully');
  } catch (error) {
    console.error('❌ Error dropping tables:', error);
    throw error;
  }
};

module.exports = {
  createTables,
  dropTables
};

// Run migration if this file is executed directly
if (require.main === module) {
  require('dotenv').config({ path: '../../.env' });
  createTables()
    .then(() => {
      console.log('✅ Migration completed successfully');
      process.exit(0);
    })
    .catch((error) => {
      console.error('❌ Migration failed:', error);
      process.exit(1);
    });
}
