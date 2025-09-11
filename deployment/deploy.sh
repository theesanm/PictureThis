#!/bin/bash
# PictureThis Deployment Script
echo "🚀 PictureThis Deployment Script"
echo "================================="

# Check if we're in the right directory
if [ ! -f "index.php" ]; then
    echo "❌ Error: Please run this script from the deployment directory"
    exit 1
fi

echo "✅ Deployment directory verified"

# Set proper permissions
echo "🔧 Setting file permissions..."
chmod 644 *.php
chmod 644 config/*.php
chmod 644 src/**/*.php
chmod 644 public/**/*.*
chmod 755 .
chmod 755 config/
chmod 755 src/
chmod 755 public/
chmod 755 uploads/

echo "✅ File permissions set"

# Create .htaccess if it doesn't exist
if [ ! -f ".htaccess" ]; then
    echo "📝 Creating .htaccess file..."
    cat > .htaccess << 'EOF'
# PictureThis .htaccess - Production Configuration

# Enable rewrite engine
RewriteEngine On

# Allow direct access to all files and directories first
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Route everything else to index.php
RewriteRule ^(.*)$ index.php [QSA,L]

# Security - protect config files
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

# MIME types for static files
AddType text/css .css
AddType application/javascript .js
AddType image/png .png
AddType image/jpeg .jpg
AddType image/jpeg .jpeg
AddType image/gif .gif
AddType image/x-icon .ico
AddType image/svg+xml .svg
AddType font/woff .woff
AddType font/woff2 .woff2
AddType font/ttf .ttf
AddType application/vnd.ms-fontobject .eot
EOF
    echo "✅ .htaccess created"
fi

echo ""
echo "🎉 Deployment preparation complete!"
echo ""
echo "📋 Next steps:"
echo "1. Upload all files to your cPanel public_html/demo directory"
echo "2. Run setup_database.php in your browser to create tables"
echo "3. Run create_admin.php to create admin user"
echo "4. Visit your site at https://demo.cfox.co.za/"
echo ""
echo "👤 Admin credentials (after running create_admin.php):"
echo "Email: admin@picturethis.com"
echo "Password: admin123"
