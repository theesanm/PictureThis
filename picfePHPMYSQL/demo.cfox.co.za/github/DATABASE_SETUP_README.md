# Database Setup Guide

This guide will help you set up the PictureThis database on your cPanel/MySQL server.

## Files Created

1. **`setup_database.php`** - PHP script to create all tables
2. **`setup_database.sql`** - SQL statements for phpMyAdmin
3. **`test_database.php`** - Test script to verify connection

## Database Credentials (Already Configured)

- **Host:** localhost:3306
- **Database:** cfoxcozj_PictureThis
- **Username:** cfoxcozj_picThisdb
- **Password:** LfUYHI%]{sjb5A*u

## Method 1: Using phpMyAdmin (Recommended)

1. Log into your cPanel
2. Open **phpMyAdmin**
3. Select your database: `cfoxcozj_PictureThis`
4. Click on the **SQL** tab
5. Copy and paste the contents of `setup_database.sql`
6. Click **Go** to execute

## Method 2: Using PHP Script

1. Upload `setup_database.php` to your web server
2. Visit the file in your browser: `https://yourdomain.com/setup_database.php`
3. The script will automatically create all tables

## Method 3: Using Command Line

```bash
# Upload setup_database.php to your server
# SSH into your server and run:
php setup_database.php
# Or via command line MySQL client:
mysql -h localhost -P 3306 -u cfoxcozj_picThisdb -p cfoxcozj_PictureThis < setup_database.sql
```

## Tables Created

- **`users`** - User accounts and authentication
- **`images`** - Generated images and metadata
- **`credit_transactions`** - Credit usage tracking
- **`settings`** - Application configuration
- **`payments`** - PayFast payment records

## Test Your Setup

1. Upload `test_database.php` to your server
2. Visit: `https://yourdomain.com/test_database.php`
3. Check that all tables exist and connection works

## Default Data

The setup script creates:
- Default application settings
- Test admin user: `admin@picturethis.com` / `admin123`

## Security Notes

- Change the test admin password after setup
- Remove setup files from your server after successful setup
- The database user should have proper permissions (SELECT, INSERT, UPDATE, DELETE)

## Troubleshooting

If you encounter connection errors:
1. Verify database credentials
2. Check user permissions in cPanel
3. Ensure database exists
4. Check MySQL server status

## Next Steps

After database setup:
1. Update `config/config.php` with your domain
2. Test user registration and login
3. Test image generation functionality
4. Configure PayFast for payments
