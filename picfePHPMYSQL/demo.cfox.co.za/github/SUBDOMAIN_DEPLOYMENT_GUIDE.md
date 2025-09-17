# Subdomain Deployment Guide: demo.cfox.co.za

## 📁 Current Repository Structure (What You Have)
```
PictureThis/  (your cloned repo)
├── picfe/                    ← Next.js app (NOT needed)
├── backend/                  ← Node.js backend (NOT needed)
├── picfePHPMYSQL/
│   └── cpanel-html-mysql-app/ ← PHP/MySQL app (THIS is what you need)
│       ├── index.php
│       ├── config/
│       ├── src/
│       ├── public/
│       ├── uploads/
│       ├── .htaccess
│       ├── setup_database.php
│       └── ... (other PHP files)
└── ... (other files you don't need)
```

## 🎯 Target Structure for demo.cfox.co.za
```
demo.cfox.co.za/  (subdomain directory)
├── index.php              ← Main entry point
├── config/
│   └── config.php        ← Database config
├── src/
│   ├── controllers/
│   ├── views/
│   └── lib/
├── public/
│   ├── css/
│   └── uploads/
├── uploads/               ← For generated images
├── .htaccess             ← URL rewriting
├── setup_database.php    ← Database setup
├── test_database.php     ← Connection test
└── ... (other essential files)
```

## 🚀 Deployment Steps

### Step 1: Create Subdomain Directory
1. In cPanel → **Domains** → **Subdomains**
2. Create subdomain: `demo.cfox.co.za`
3. Document Root: `/home/cfoxcozj/public_html/demo` (or wherever you want it)

### Step 2: Copy Only PHP/MySQL Files
Navigate to your cloned repository and copy **ONLY** these files:

**From:** `PictureThis/picfePHPMYSQL/cpanel-html-mysql-app/`
**To:** `/home/cfoxcozj/public_html/demo/` (your subdomain directory)

### Step 3: Essential Files to Copy
```
✅ index.php
✅ config/config.php
✅ src/ (entire directory)
✅ public/ (entire directory)
✅ uploads/ (entire directory)
✅ .htaccess
✅ setup_database.php
✅ test_database.php
✅ post_deployment_check.php
```

### Step 4: Optional Files (Can Delete Later)
```
📄 CPANEL_DEPLOYMENT_GUIDE.md
📄 DATABASE_SETUP_README.md
📄 DEPLOYMENT_CHECKLIST.md
📄 README.md
📄 setup_database.sql
```

### Step 5: Files to SKIP (Not Needed)
```
❌ picfe/ (Next.js app)
❌ backend/ (Node.js backend)
❌ node_modules/
❌ .next/
❌ .env files
❌ docker files
❌ test scripts you don't need
```

## ⚙️ Post-Copy Configuration

### Update config/config.php
```php
// Change these lines:
define('APP_URL', 'https://demo.cfox.co.za');
define('OPENROUTER_APP_URL', 'https://demo.cfox.co.za');
```

### Set Permissions
```bash
chmod 755 uploads/
chmod 755 public/uploads/
chmod 644 *.php
```

## 🧪 Testing

1. Visit: `https://demo.cfox.co.za/setup_database.php`
2. Visit: `https://demo.cfox.co.za/test_database.php`
3. Visit: `https://demo.cfox.co.za` (main app)

## 🧹 Cleanup

After successful deployment, remove:
- `setup_database.php`
- `test_database.php`
- `post_deployment_check.php`
- All README files

## 📋 Quick Checklist

- [ ] Create subdomain `demo.cfox.co.za`
- [ ] Copy files from `picfePHPMYSQL/cpanel-html-mysql-app/` to subdomain directory
- [ ] Update `config/config.php` with correct domain
- [ ] Set proper file permissions
- [ ] Test database connection
- [ ] Test main application
- [ ] Clean up unnecessary files

## 🎉 Result

Your subdomain `demo.cfox.co.za` will serve the PHP/MySQL version of PictureThis with a clean, minimal structure containing only the necessary files for your application.
