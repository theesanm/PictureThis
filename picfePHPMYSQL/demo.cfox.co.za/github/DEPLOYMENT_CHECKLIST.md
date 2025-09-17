# 🚀 Quick cPanel Deployment Checklist

## ✅ Pre-Flight Check
- [ ] Database created and tables exist
- [ ] Domain DNS pointing to cPanel server
- [ ] cPanel login credentials ready

## 📁 File Upload (Choose One Method)

### Method 1: cPanel File Manager
1. [ ] Log into cPanel
2. [ ] Go to **File Manager**
3. [ ] Navigate to `public_html/`
4. [ ] Click **Upload** → Select all project files
5. [ ] Extract ZIP if uploaded as archive

### Method 2: FTP Upload
1. [ ] Use FileZilla or similar FTP client
2. [ ] Connect: `ftp.yourdomain.com`
3. [ ] Upload all files to `public_html/`

## ⚙️ Configuration Updates

### Update Domain in Config
1. [ ] Open `config/config.php`
2. [ ] Replace `https://yourdomain.com` with your actual domain
3. [ ] Update both `APP_URL` and `OPENROUTER_APP_URL`

### File Permissions
1. [ ] Set `public/uploads/` to **755** (writable)
2. [ ] Set all `.php` files to **644**
3. [ ] Set all directories to **755**

## 🧪 Testing Phase

### Test Database Connection
1. [ ] Visit: `https://yourdomain.com/test_database.php`
2. [ ] Should show ✅ successful connection
3. [ ] Remove `test_database.php` after success

### Test Application
1. [ ] Visit: `https://yourdomain.com`
2. [ ] Test user registration
3. [ ] Test login functionality
4. [ ] Test navigation menu

## 🔒 Security & Cleanup

### Remove Development Files
- [ ] Delete `setup_database.php`
- [ ] Delete `test_database.php`
- [ ] Delete `setup_database.sql`
- [ ] Delete `DATABASE_SETUP_README.md`
- [ ] Delete `CPANEL_DEPLOYMENT_GUIDE.md`

### Update Admin Credentials
- [ ] Change default admin password
- [ ] Create additional admin users if needed

## 🌐 SSL & Performance

### SSL Certificate
1. [ ] Go to cPanel → **SSL/TLS**
2. [ ] Install Let's Encrypt certificate
3. [ ] Ensure all links use `https://`

### Performance Optimization
1. [ ] Enable GZIP compression (already in .htaccess)
2. [ ] Set up browser caching (already configured)
3. [ ] Optimize images in `public/uploads/`

## 📊 Final Verification

- [ ] Homepage loads correctly
- [ ] User registration works
- [ ] Login/logout functions
- [ ] Navigation menu responsive
- [ ] Database connections working
- [ ] File uploads functional
- [ ] SSL certificate active
- [ ] No PHP errors in logs

## 🆘 Troubleshooting

If issues occur:
1. Check cPanel **Error Log**
2. Verify file permissions
3. Test database connection separately
4. Check `.htaccess` configuration
5. Contact hosting support if needed

---

**🎉 Deployment Complete!**
Your PictureThis application is now live on cPanel!
