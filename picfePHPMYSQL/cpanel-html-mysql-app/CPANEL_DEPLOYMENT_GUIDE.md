# PictureThis cPanel Deployment Guide

## 📋 Pre-Deployment Checklist

- ✅ Database created: `cfoxcozj_PictureThis`
- ✅ Database user created: `cfoxcozj_picThisdb`
- ✅ Tables created via setup script
- ✅ Domain pointed to cPanel hosting

## 🚀 Deployment Steps

### Step 1: Upload Files to cPanel

#### Option A: File Manager (Recommended)
1. Log into your cPanel
2. Go to **File Manager**
3. Navigate to `public_html/` (or your subdomain folder)
4. Click **Upload** and select all files from your project
5. Extract the uploaded ZIP file (if uploaded as ZIP)

#### Option B: FTP
1. Use FTP client (FileZilla, etc.)
2. Connect to your cPanel FTP:
   - Host: yourdomain.com
   - Username: your_cpanel_username
   - Password: your_cpanel_password
   - Port: 21
3. Upload all files to `public_html/` directory

### Step 2: File Structure Setup

Your files should be organized like this:
```
public_html/
├── index.php                 # Main router
├── config/
│   └── config.php           # Database config (already updated)
├── src/
│   ├── controllers/
│   ├── views/
│   └── lib/
├── public/
│   ├── css/
│   └── uploads/             # For generated images
├── setup_database.php       # Remove after setup
├── test_database.php        # Remove after testing
└── .htaccess               # URL rewriting (create if needed)
```

### Step 3: Create .htaccess File

Create a `.htaccess` file in your `public_html/` directory:

```apache
RewriteEngine On

# Handle PHP files without .php extension
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

# Allow access to uploads directory
<Directory "public/uploads/">
    Allow from all
</Directory>

# PHP settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
```

### Step 4: Set File Permissions

In cPanel File Manager:
1. Right-click on files/folders
2. Set permissions:

```
public_html/
├── index.php              → 644
├── config/
│   └── config.php        → 644
├── src/                   → 755
├── public/
│   ├── css/              → 755
│   └── uploads/          → 755 (writable)
├── .htaccess             → 644
```

### Step 5: Update Domain Configuration

Update your `config/config.php`:

```php
// Replace with your actual domain
define('APP_URL', 'https://yourdomain.com');
define('OPENROUTER_APP_URL', 'https://yourdomain.com');
```

### Step 6: Test Database Connection

1. Visit: `https://yourdomain.com/test_database.php`
2. Should show successful connection and table status
3. Remove `test_database.php` after successful test

### Step 7: Test Application

1. Visit your domain: `https://yourdomain.com`
2. Test user registration
3. Test login functionality
4. Test image generation (if credits available)

### Step 8: Configure Additional Settings

#### PayFast Configuration (if using payments)
Update in `config/config.php`:
```php
// Use production credentials
define('PAYFAST_MERCHANT_ID', 'your_production_merchant_id');
define('PAYFAST_MERCHANT_KEY', 'your_production_merchant_key');
define('PAYFAST_PASSPHRASE', 'your_production_passphrase');
```

#### Email Configuration (if needed)
Configure SMTP settings in cPanel:
1. Go to **Email** → **Email Accounts**
2. Create email account for notifications

### Step 9: SSL Certificate

1. Go to **SSL/TLS** in cPanel
2. Install Let's Encrypt SSL certificate
3. Ensure all URLs use `https://`

### Step 10: Backup & Security

1. **Database Backup:**
   - Go to **phpMyAdmin**
   - Export your database regularly

2. **File Backup:**
   - Use cPanel **Backup** tool
   - Download full website backup

3. **Security Measures:**
   - Remove setup files after deployment
   - Change default admin password
   - Keep PHP updated
   - Monitor error logs

## 🔧 Troubleshooting

### Common Issues:

1. **500 Internal Server Error**
   - Check file permissions
   - Verify PHP version compatibility
   - Check `.htaccess` syntax

2. **Database Connection Error**
   - Verify credentials in `config.php`
   - Check database user permissions
   - Ensure database exists

3. **Images Not Uploading**
   - Check `uploads/` directory permissions (755)
   - Verify PHP upload settings
   - Check disk space

4. **Page Not Found**
   - Ensure `.htaccess` is uploaded
   - Check URL rewriting is enabled
   - Verify `index.php` is in correct location

### Debug Mode

Add to `config.php` for debugging:
```php
define('DEBUG_MODE', true);
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 📞 Support

If you encounter issues:
1. Check cPanel error logs
2. Test database connection separately
3. Verify file permissions
4. Contact your hosting provider

## ✅ Post-Deployment Checklist

- [ ] Files uploaded successfully
- [ ] Database connection working
- [ ] User registration/login working
- [ ] Image generation functional
- [ ] SSL certificate installed
- [ ] File permissions correct
- [ ] Setup files removed
- [ ] Admin password changed
- [ ] Backups configured

Your PictureThis application should now be live and ready to use! 🎉
