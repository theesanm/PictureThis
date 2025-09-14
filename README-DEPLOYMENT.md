# PictureThis DeploymeThis will:
- Copy application files directly to your public_html root
- Create necessary directories (uploads, logs, tmp, cache)
- Set proper permissions
- Create production-ready .htaccess with PHP limits
- Copy production config templatede

## Overview
PictureThis is a PHP/MySQL web application for AI-powered image generation with PayFast payment integration.

## Quick Deployment

### 1. Clone Repository to cPanel
```bash
# In your cPanel public_html directory
git clone https://github.com/yourusername/PictureThis.git github
```

### 2. Run Quick Deploy Script
```bash
# Navigate to your public_html directory
cd public_html

# Run the deployment script
php deploy.php
```

This will:
- Copy application files to `picturethis/` folder
- Create necessary directories (uploads, logs, tmp, cache)
- Set proper permissions
- Create production .htaccess with PHP limits
- Copy production config template

### 3. Configure Database
Edit `picturethis/config/config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
```

### 4. Create Database Tables
Import the SQL schema:
```sql
-- Run the contents of sql/schema_mysql.sql in your MySQL database
```

### 5. Configure PayFast
Update PayFast settings in `config/config.php`:
```php
define('PAYFAST_MERCHANT_ID', 'your_merchant_id');
define('PAYFAST_MERCHANT_KEY', 'your_merchant_key');
define('PAYFAST_PASSPHRASE', 'your_passphrase');
```

### 6. Set Environment Variables
Create or update `.htaccess` in the `picturethis/` folder:
```
SetEnv APP_ENV "production"
SetEnv PAYFAST_SANDBOX "false"
```

## Manual Deployment (Alternative)

If you prefer manual deployment:

1. Copy all files from `github/picfePHPMYSQL/cpanel-html-mysql-app/` to your `public_html/` root
2. Create directories: `uploads/`, `logs/`, `tmp/`, `cache/`
3. Set permissions: 755 for directories, 644 for files
4. Copy `config/production.php` to `config/config.php`
5. Edit `config/config.php` with your settings
6. Import database schema from `sql/schema_mysql.sql`

## File Structure After Deployment
```
public_html/
├── github/           # Git repository
├── config/           # Application configuration
├── src/              # Application source code
├── uploads/          # User uploaded files
├── logs/             # Application logs
├── tmp/              # Temporary files
├── cache/            # Cache directory
├── .htaccess         # Apache configuration
├── index.php         # Main application entry point
└── deploy.php        # Deployment script
```

## PHP Requirements
- PHP 8.0+
- MySQL 5.7+
- Extensions: mysqli, curl, gd, mbstring

## PHP Configuration
The deployment script sets optimal PHP limits:
- upload_max_filesize: 20M
- post_max_size: 25M
- memory_limit: 128M
- max_execution_time: 300

## Troubleshooting

### Image Upload Issues
- Check PHP upload limits in cPanel
- Ensure `uploads/` directory is writable (755)
- Verify file permissions

### Database Connection
- Confirm database credentials in `config/config.php`
- Check if database user has proper permissions
- Verify database server is accessible

### PayFast Integration
- Ensure merchant credentials are correct
- Check ITN URL in PayFast dashboard
- Verify sandbox mode settings

## Security Notes
- Never commit sensitive data to Git
- Use strong database passwords
- Keep PayFast credentials secure
- Regularly update dependencies

## Support
For issues or questions, check the application logs in `logs/` directory.