<?php
/**
 * PictureThis Web Setup Script
 *
 * This script deploys the PictureThis application from the github folder
 * to the web root directory. Run this from your browser at:
 * https://yourdomain.com/setup.php
 */

// Configuration
define('GITHUB_FOLDER', __DIR__ . '/github');
define('WEB_ROOT', __DIR__);
define('APP_FOLDER', 'picturethis'); // The folder name for the deployed app

// Start session for status tracking
session_start();

// Initialize status
if (!isset($_SESSION['setup_step'])) {
    $_SESSION['setup_step'] = 0;
}
if (!isset($_SESSION['setup_log'])) {
    $_SESSION['setup_log'] = [];
}

function logMessage($message, $type = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message";
    $_SESSION['setup_log'][] = $logEntry;
    echo "<div class='log-entry log-$type'>$logEntry</div>";
}

function copyDirectory($source, $destination, $exclude = []) {
    if (!is_dir($source)) {
        logMessage("Source directory does not exist: $source", 'error');
        return false;
    }

    if (!is_dir($destination)) {
        if (!mkdir($destination, 0755, true)) {
            logMessage("Failed to create destination directory: $destination", 'error');
            return false;
        }
    }

    $dir = opendir($source);
    $success = true;

    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..' || in_array($file, $exclude)) {
            continue;
        }

        $sourcePath = $source . '/' . $file;
        $destPath = $destination . '/' . $file;

        if (is_dir($sourcePath)) {
            if (!copyDirectory($sourcePath, $destPath, $exclude)) {
                $success = false;
            }
        } else {
            if (!copy($sourcePath, $destPath)) {
                logMessage("Failed to copy file: $sourcePath -> $destPath", 'error');
                $success = false;
            } else {
                logMessage("Copied: $file", 'success');
            }
        }
    }

    closedir($dir);
    return $success;
}

function setPermissions($path, $permissions = 0644) {
    if (is_file($path)) {
        if (chmod($path, $permissions)) {
            logMessage("Set permissions on file: $path", 'success');
            return true;
        } else {
            logMessage("Failed to set permissions on file: $path", 'error');
            return false;
        }
    } elseif (is_dir($path)) {
        if (chmod($path, 0755)) {
            logMessage("Set permissions on directory: $path", 'success');

            // Set permissions on all files in directory
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            foreach ($files as $file) {
                if ($file->isFile()) {
                    chmod($file->getPathname(), $permissions);
                }
            }
            return true;
        } else {
            logMessage("Failed to set permissions on directory: $path", 'error');
            return false;
        }
    }
    return false;
}

function createDirectory($path) {
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            logMessage("Created directory: $path", 'success');
            return true;
        } else {
            logMessage("Failed to create directory: $path", 'error');
            return false;
        }
    } else {
        logMessage("Directory already exists: $path", 'info');
        return true;
    }
}

function setupDatabase($configFile) {
    if (!file_exists($configFile)) {
        logMessage("Config file not found: $configFile", 'error');
        return false;
    }

    // Include config to get database settings
    $config = include $configFile;

    if (!isset($config['DB_HOST']) || !isset($config['DB_NAME'])) {
        logMessage("Database configuration not found in config file", 'error');
        return false;
    }

    try {
        $pdo = new PDO(
            "mysql:host={$config['DB_HOST']};charset=utf8mb4",
            $config['DB_USER'] ?? '',
            $config['DB_PASS'] ?? ''
        );

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['DB_NAME']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        logMessage("Database created or already exists: {$config['DB_NAME']}", 'success');

        // Select the database
        $pdo->exec("USE `{$config['DB_NAME']}`");

        // Run schema setup
        $schemaFile = GITHUB_FOLDER . '/picfePHPMYSQL/cpanel-html-mysql-app/setup_database.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
            logMessage("Database schema setup completed", 'success');
        } else {
            logMessage("Schema file not found: $schemaFile", 'warning');
        }

        return true;
    } catch (Exception $e) {
        logMessage("Database setup failed: " . $e->getMessage(), 'error');
        return false;
    }
}

function createHtaccess($appPath) {
    $htaccessContent = "# PictureThis .htaccess - Production Environment
# Generated by setup script on " . date('Y-m-d H:i:s') . "

# Environment setting
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

# Prevent PHP parsing of static files
<FilesMatch \"\\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$\">
    SetHandler default-handler
    RemoveHandler .css .js .png .jpg .jpeg .gif .ico .svg .woff .woff2 .ttf .eot
</FilesMatch>

# Enable rewrite engine
RewriteEngine On

# Allow direct access to files and directories
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Route everything else to index.php
RewriteRule ^(.*)$ index.php [QSA,L]

# MIME types
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
";

    $htaccessPath = $appPath . '/.htaccess';
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        logMessage("Created .htaccess file: $htaccessPath", 'success');
        return true;
    } else {
        logMessage("Failed to create .htaccess file: $htaccessPath", 'error');
        return false;
    }
}

// Handle setup steps
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'reset') {
    session_destroy();
    session_start();
    $_SESSION['setup_step'] = 0;
    $_SESSION['setup_log'] = [];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PictureThis Setup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        .content {
            padding: 30px;
        }
        .step {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .step h3 {
            margin: 0 0 15px 0;
            color: #1e293b;
            font-size: 1.25em;
        }
        .step.completed {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        .step.completed h3::before {
            content: "✓ ";
            color: #16a34a;
            font-weight: bold;
        }
        .btn {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        .btn.secondary {
            background: #6b7280;
        }
        .btn.secondary:hover {
            background: #4b5563;
            box-shadow: 0 10px 20px rgba(107, 114, 128, 0.3);
        }
        .log-container {
            background: #1e293b;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        .log-entry {
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 12px;
            margin-bottom: 5px;
            padding: 5px;
            border-radius: 4px;
        }
        .log-info { color: #e2e8f0; }
        .log-success { color: #bbf7d0; background: rgba(34, 197, 94, 0.1); }
        .log-warning { color: #fed7aa; background: rgba(245, 158, 11, 0.1); }
        .log-error { color: #fecaca; background: rgba(239, 68, 68, 0.1); }
        .progress-bar {
            background: #e2e8f0;
            border-radius: 10px;
            height: 8px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .status-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .status-card h4 {
            margin: 0 0 10px 0;
            color: #374151;
        }
        .status-card .status {
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
        }
        .status.success { background: #dcfce7; color: #166534; }
        .status.error { background: #fef2f2; color: #991b1b; }
        .status.warning { background: #fef3c7; color: #92400e; }
        .status.pending { background: #f3f4f6; color: #374151; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 PictureThis Setup</h1>
            <p>Deploy your AI image generation app from GitHub</p>
        </div>

        <div class="content">
            <?php if ($step === 0): ?>
                <!-- Initial Setup Screen -->
                <div class="step">
                    <h3>Welcome to PictureThis Setup</h3>
                    <p>This setup wizard will help you deploy the PictureThis application from your GitHub folder to your web root.</p>

                    <div class="status-grid">
                        <div class="status-card">
                            <h4>GitHub Folder</h4>
                            <div class="status <?php echo is_dir(GITHUB_FOLDER) ? 'success' : 'error'; ?>">
                                <?php echo is_dir(GITHUB_FOLDER) ? 'Found' : 'Not Found'; ?>
                            </div>
                        </div>
                        <div class="status-card">
                            <h4>Web Root</h4>
                            <div class="status success">Ready</div>
                        </div>
                        <div class="status-card">
                            <h4>PHP Version</h4>
                            <div class="status success"><?php echo PHP_VERSION; ?></div>
                        </div>
                        <div class="status-card">
                            <h4>Permissions</h4>
                            <div class="status <?php echo is_writable(WEB_ROOT) ? 'success' : 'error'; ?>">
                                <?php echo is_writable(WEB_ROOT) ? 'Writable' : 'Not Writable'; ?>
                            </div>
                        </div>
                    </div>

                    <p><strong>What this setup will do:</strong></p>
                    <ul>
                        <li>Copy application files from <code>github/</code> folder</li>
                        <li>Create necessary directories (uploads, logs, etc.)</li>
                        <li>Set up proper file permissions</li>
                        <li>Create production-ready .htaccess file</li>
                        <li>Configure the application for production use</li>
                    </ul>

                    <p><strong>Before you begin:</strong></p>
                    <ul>
                        <li>Make sure your GitHub code is in the <code>github/</code> folder</li>
                        <li>Ensure you have database credentials ready</li>
                        <li>Back up any existing files if needed</li>
                    </ul>

                    <a href="?step=1" class="btn">Start Setup</a>
                    <a href="?action=reset" class="btn secondary">Reset Setup</a>
                </div>

            <?php elseif ($step === 1): ?>
                <!-- File Copy Step -->
                <div class="step">
                    <h3>Step 1: Copy Application Files</h3>
                    <p>Copying files from GitHub folder to web root...</p>

                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 25%"></div>
                    </div>

                    <?php
                    $_SESSION['setup_step'] = 1;

                    $sourcePath = GITHUB_FOLDER . '/picfePHPMYSQL/cpanel-html-mysql-app';
                    $destPath = WEB_ROOT . '/' . APP_FOLDER;

                    logMessage("Starting file copy from: $sourcePath");
                    logMessage("Destination: $destPath");

                    if (is_dir($sourcePath)) {
                        $excludeFiles = ['.git', '.gitignore', 'README.md', 'debug.log', '.user.ini', 'php.ini'];
                        if (copyDirectory($sourcePath, $destPath, $excludeFiles)) {
                            logMessage("File copy completed successfully", 'success');
                        } else {
                            logMessage("File copy had some errors", 'warning');
                        }
                    } else {
                        logMessage("Source path not found: $sourcePath", 'error');
                    }
                    ?>

                    <a href="?step=2" class="btn">Continue to Step 2</a>
                </div>

            <?php elseif ($step === 2): ?>
                <!-- Directory Creation Step -->
                <div class="step">
                    <h3>Step 2: Create Directories</h3>
                    <p>Creating necessary directories for uploads, logs, and cache...</p>

                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 50%"></div>
                    </div>

                    <?php
                    $_SESSION['setup_step'] = 2;

                    $directories = [
                        WEB_ROOT . '/' . APP_FOLDER . '/uploads',
                        WEB_ROOT . '/' . APP_FOLDER . '/logs',
                        WEB_ROOT . '/' . APP_FOLDER . '/tmp',
                        WEB_ROOT . '/' . APP_FOLDER . '/cache'
                    ];

                    foreach ($directories as $dir) {
                        createDirectory($dir);
                        setPermissions($dir, 0755);
                    }
                    ?>

                    <a href="?step=3" class="btn">Continue to Step 3</a>
                </div>

            <?php elseif ($step === 3): ?>
                <!-- Configuration Step -->
                <div class="step">
                    <h3>Step 3: Setup Configuration</h3>
                    <p>Creating production configuration and .htaccess file...</p>

                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 75%"></div>
                    </div>

                    <?php
                    $_SESSION['setup_step'] = 3;

                    $appPath = WEB_ROOT . '/' . APP_FOLDER;

                    // Create .htaccess
                    createHtaccess($appPath);

                    // Copy production config
                    $prodConfigSrc = $appPath . '/config/production.php';
                    $prodConfigDest = $appPath . '/config/config.php';

                    if (file_exists($prodConfigSrc)) {
                        if (copy($prodConfigSrc, $prodConfigDest)) {
                            logMessage("Production config copied successfully", 'success');
                        } else {
                            logMessage("Failed to copy production config", 'error');
                        }
                    } else {
                        logMessage("Production config template not found", 'warning');
                    }

                    // Set permissions on config files
                    setPermissions($appPath . '/config', 0755);
                    if (file_exists($prodConfigDest)) {
                        setPermissions($prodConfigDest, 0644);
                    }
                    ?>

                    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 15px; margin: 20px 0;">
                        <h4 style="color: #92400e; margin: 0 0 10px 0;">⚠️ Important: Configure Database</h4>
                        <p style="color: #92400e; margin: 0;">You need to edit the config file to add your database credentials:</p>
                        <code style="display: block; background: #374151; color: #fbbf24; padding: 10px; border-radius: 4px; margin: 10px 0;">
                            <?php echo htmlspecialchars($prodConfigDest); ?>
                        </code>
                        <p style="color: #92400e; margin: 5px 0 0 0;">Update the DB_HOST, DB_USER, DB_PASS, and DB_NAME values.</p>
                    </div>

                    <a href="?step=4" class="btn">Continue to Step 4</a>
                </div>

            <?php elseif ($step === 4): ?>
                <!-- Finalization Step -->
                <div class="step completed">
                    <h3>Step 4: Setup Complete!</h3>
                    <p>Application has been successfully deployed!</p>

                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 100%"></div>
                    </div>

                    <?php
                    $_SESSION['setup_step'] = 4;

                    $appUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/' . APP_FOLDER;
                    ?>

                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0;">
                        <h4 style="color: #166534; margin: 0 0 15px 0;">🎉 Deployment Successful!</h4>
                        <p style="color: #166534; margin: 0 0 10px 0;">Your PictureThis application is now available at:</p>
                        <a href="<?php echo $appUrl; ?>" target="_blank" style="color: #16a34a; font-weight: bold; font-size: 18px;">
                            <?php echo $appUrl; ?>
                        </a>

                        <h5 style="color: #166534; margin: 20px 0 10px 0;">Next Steps:</h5>
                        <ol style="color: #166534; margin: 0; padding-left: 20px;">
                            <li>Edit the config file with your database credentials</li>
                            <li>Visit the application URL to complete setup</li>
                            <li>Create an admin user if needed</li>
                            <li>Test the image generation functionality</li>
                        </ol>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo $appUrl; ?>" target="_blank" class="btn">Visit Application</a>
                        <a href="?action=reset" class="btn secondary">Run Setup Again</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Setup Log -->
            <?php if (!empty($_SESSION['setup_log'])): ?>
                <div class="log-container">
                    <h4 style="color: white; margin: 0 0 15px 0;">Setup Log</h4>
                    <?php foreach ($_SESSION['setup_log'] as $logEntry): ?>
                        <?php echo $logEntry . "<br>"; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>