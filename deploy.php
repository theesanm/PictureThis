<?php
/**
 * PictureThis Quick Deploy Script
 *
 * Run this script to quickly deploy the application from github folder
 * Usage: php deploy.php
 */

// Configuration
define('GITHUB_FOLDER', __DIR__ . '/github');
define('WEB_ROOT', __DIR__);
define('APP_FOLDER', 'picturethis');

echo "🚀 PictureThis Quick Deploy\n";
echo "==========================\n\n";

// Check if github folder exists
if (!is_dir(GITHUB_FOLDER)) {
    echo "❌ Error: GitHub folder not found at: " . GITHUB_FOLDER . "\n";
    echo "Please clone your repository to the 'github' folder first.\n";
    exit(1);
}

echo "✅ GitHub folder found\n";

// Check if source app exists
$sourceApp = GITHUB_FOLDER . '/picfePHPMYSQL/cpanel-html-mysql-app';
if (!is_dir($sourceApp)) {
    echo "❌ Error: Application not found in GitHub folder\n";
    echo "Expected path: " . $sourceApp . "\n";
    exit(1);
}

echo "✅ Application source found\n";

// Create destination folder
$destApp = WEB_ROOT . '/' . APP_FOLDER;
if (!is_dir($destApp)) {
    if (!mkdir($destApp, 0755, true)) {
        echo "❌ Error: Cannot create destination folder: " . $destApp . "\n";
        exit(1);
    }
    echo "✅ Created destination folder: " . APP_FOLDER . "\n";
}

// Copy files
echo "📁 Copying application files...\n";
copyDirectory($sourceApp, $destApp, ['.git', '.gitignore', 'README.md', 'debug.log', '.user.ini', 'php.ini']);

echo "✅ Application files copied\n";

// Create necessary directories
$directories = ['uploads', 'logs', 'tmp', 'cache'];
foreach ($directories as $dir) {
    $dirPath = $destApp . '/' . $dir;
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0755, true);
        echo "✅ Created directory: " . $dir . "\n";
    }
}

// Set permissions
setPermissionsRecursive($destApp, 0644, 0755);

echo "✅ Permissions set\n";

// Create .htaccess
createHtaccess($destApp);

echo "✅ .htaccess created\n";

// Copy production config
$prodConfigSrc = $destApp . '/config/production.php';
$prodConfigDest = $destApp . '/config/config.php';

if (file_exists($prodConfigSrc)) {
    copy($prodConfigSrc, $prodConfigDest);
    echo "✅ Production config copied\n";
} else {
    echo "⚠️  Warning: Production config template not found\n";
}

echo "\n🎉 Deployment completed successfully!\n";
echo "📍 Application URL: https://" . $_SERVER['HTTP_HOST'] . "/" . APP_FOLDER . "\n";
echo "⚙️  Remember to configure your database settings in: " . $prodConfigDest . "\n";
echo "\nNext steps:\n";
echo "1. Edit config.php with your database credentials\n";
echo "2. Visit the application URL\n";
echo "3. Create an admin user if needed\n";
echo "4. Test the image generation functionality\n";

function copyDirectory($source, $destination, $exclude = []) {
    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..' || in_array($file, $exclude)) {
            continue;
        }

        $sourcePath = $source . '/' . $file;
        $destPath = $destination . '/' . $file;

        if (is_dir($sourcePath)) {
            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
            }
            copyDirectory($sourcePath, $destPath, $exclude);
        } else {
            copy($sourcePath, $destPath);
        }
    }
    closedir($dir);
}

function setPermissionsRecursive($path, $filePerm = 0644, $dirPerm = 0755) {
    if (is_file($path)) {
        chmod($path, $filePerm);
    } elseif (is_dir($path)) {
        chmod($path, $dirPerm);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($files as $file) {
            if ($file->isFile()) {
                chmod($file->getPathname(), $filePerm);
            } elseif ($file->isDir()) {
                chmod($file->getPathname(), $dirPerm);
            }
        }
    }
}

function createHtaccess($appPath) {
    $htaccessContent = "# PictureThis .htaccess - Production Environment
SetEnv APP_ENV \"production\"

# PHP Configuration
php_value upload_max_filesize 20M
php_value post_max_size 25M
php_value memory_limit 128M
php_value max_execution_time 300
php_value max_input_time 300

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
</IfModule>

# Prevent access to sensitive files
<FilesMatch \"\\.(htaccess|htpasswd|ini|log|sh|sql|conf)$\">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Enable rewrite engine
RewriteEngine On

# Allow direct access to files and directories
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Route everything else to index.php
RewriteRule ^(.*)$ index.php [QSA,L]
";

    file_put_contents($appPath . '/.htaccess', $htaccessContent);
}
?>