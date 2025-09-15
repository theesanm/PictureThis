# PictureThis Production Deployment Guide

## 🚀 Simple Deployment Process

### Step 1: Edit Production Configuration
Edit `config/production.php` and replace the placeholder values:

```php
'database' => [
    'pass' => 'YOUR_ACTUAL_DB_PASSWORD', // Replace this
],
'payfast' => [
    'merchant_id' => 'YOUR_ACTUAL_PAYFAST_MERCHANT_ID', // Replace this
    'merchant_key' => 'YOUR_ACTUAL_PAYFAST_MERCHANT_KEY', // Replace this
    'passphrase' => 'YOUR_ACTUAL_PAYFAST_PASSPHRASE', // Replace this
],
'openrouter' => [
    'api_key' => 'YOUR_ACTUAL_OPENROUTER_API_KEY', // Replace this
],
'email' => [
    'smtp_password' => 'YOUR_ACTUAL_SMTP_PASSWORD', // Replace this
],
```

### Step 2: Switch to Production Mode
Edit `config/config.php`:
```php
define('IS_PRODUCTION', true); // Change to true for production
```

### Step 3: Upload Files
Upload all files from your GitHub repository to `demo.cfox.co.za`

### Step 4: Test Deployment
Visit these URLs to verify:
- `https://demo.cfox.co.za/deploy.php` - Automated deployment check
- `https://demo.cfox.co.za/post_deployment_check.php` - Full diagnostics
- `https://demo.cfox.co.za/test_database.php` - Database connection test

## ✅ What's Already Configured

- Database host, user, and name are already set
- App URL is already set to `https://demo.cfox.co.za`
- All other settings have sensible defaults
- .htaccess is clean (no environment variables)

## 🔑 Only These Need to be Set

1. **Database Password** - Replace `YOUR_DB_PASSWORD_HERE`
2. **PayFast Credentials** - Replace the three PayFast placeholders
3. **OpenRouter API Key** - Replace `YOUR_OPENROUTER_API_KEY`
4. **SMTP Password** - Replace `YOUR_SMTP_PASSWORD`

That's it! Simple direct editing of the config file.