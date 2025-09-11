<?php
/**
 * Web-Based Permissions Setup
 * Sets up file permissions and .htaccess
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Permissions Setup - PictureThis</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".info { color: #007bff; background: #cce7ff; border: 1px solid #b3d7ff; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo ".warning { color: #856404; background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px; margin: 10px 0; }";
echo "h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }";
echo ".btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #1e7e34; }";
echo ".btn-warning { background: #ffc107; color: #212529; }";
echo ".btn-warning:hover { background: #e0a800; }";
echo "pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }";
echo ".step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔐 Permissions Setup - PictureThis</h1>";

// Function to check and set permissions
function checkAndSetPermissions($path, $permission = 0755) {
    $results = [];

    if (file_exists($path)) {
        $currentPerms = fileperms($path) & 0777;
        $results['current'] = sprintf('%04o', $currentPerms);
        $results['target'] = sprintf('%04o', $permission);

        if ($currentPerms !== $permission) {
            if (chmod($path, $permission)) {
                $results['status'] = 'changed';
                $results['message'] = "Changed from {$results['current']} to {$results['target']}";
            } else {
                $results['status'] = 'error';
                $results['message'] = "Failed to change permissions";
            }
        } else {
            $results['status'] = 'ok';
            $results['message'] = "Already correct ({$results['current']})";
        }
    } else {
        $results['status'] = 'missing';
        $results['message'] = "Path does not exist";
    }

    return $results;
}

echo "<h2>File Permissions Check</h2>";
echo "<div class='info'>Setting up proper file permissions for security and functionality...</div>";

// Define paths to check
$paths = [
    'uploads/' => 0755,
    'tmp/' => 0755,
    'config/config.php' => 0644,
    '.htaccess' => 0644,
    'public/' => 0755,
    'src/' => 0755
];

echo "<ul>";
foreach ($paths as $path => $permission) {
    $result = checkAndSetPermissions($path, $permission);

    if ($result['status'] === 'ok') {
        echo "<li class='success'>✅ $path: {$result['message']}</li>";
    } elseif ($result['status'] === 'changed') {
        echo "<li class='info'>🔄 $path: {$result['message']}</li>";
    } elseif ($result['status'] === 'error') {
        echo "<li class='error'>❌ $path: {$result['message']}</li>";
    } else {
        echo "<li class='warning'>⚠️ $path: {$result['message']}</li>";
    }
}
echo "</ul>";

// Check .htaccess content
echo "<h2>.htaccess Configuration</h2>";
$htaccessPath = '.htaccess';

if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);

    echo "<div class='success'>✅ .htaccess file exists</div>";
    echo "<details>";
    echo "<summary>View .htaccess content</summary>";
    echo "<pre>" . htmlspecialchars($htaccessContent) . "</pre>";
    echo "</details>";
} else {
    echo "<div class='error'>❌ .htaccess file is missing</div>";
    echo "<div class='info'>Creating basic .htaccess file...</div>";

    $basicHtaccess = "RewriteEngine On
RewriteBase /

# Handle static files
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^(.*)$ - [L]

# Route everything else to index.php
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
<Files \"config.php\">
    Order Deny,Allow
    Deny from all
</Files>

# Prevent access to sensitive files
<FilesMatch \"\.(htaccess|htpasswd|ini|log|sh|inc|bak)$\">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Enable CORS for API
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin \"*\"
    Header set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS\"
    Header set Access-Control-Allow-Headers \"Content-Type, Authorization\"
</IfModule>";

    if (file_put_contents($htaccessPath, $basicHtaccess)) {
        chmod($htaccessPath, 0644);
        echo "<div class='success'>✅ .htaccess file created successfully</div>";
    } else {
        echo "<div class='error'>❌ Failed to create .htaccess file</div>";
    }
}

// Check PHP configuration
echo "<h2>PHP Configuration Check</h2>";
echo "<ul>";
echo "<li class='info'>PHP Version: " . phpversion() . "</li>";
echo "<li class='info'>Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</li>";
echo "<li class='info'>Error Reporting: " . error_reporting() . "</li>";
echo "<li class='info'>Max Upload Size: " . ini_get('upload_max_filesize') . "</li>";
echo "<li class='info'>Max Post Size: " . ini_get('post_max_size') . "</li>";
echo "</ul>";

// Check required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
$missingExtensions = [];

echo "<h2>PHP Extensions Check</h2>";
echo "<ul>";
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<li class='success'>✅ $ext extension loaded</li>";
    } else {
        echo "<li class='error'>❌ $ext extension missing</li>";
        $missingExtensions[] = $ext;
    }
}
echo "</ul>";

if (!empty($missingExtensions)) {
    echo "<div class='warning'>";
    echo "<strong>⚠️ Missing Extensions:</strong> " . implode(', ', $missingExtensions);
    echo "<br>Please contact your hosting provider to enable these PHP extensions.";
    echo "</div>";
}

// Final status
echo "<div class='success'>";
echo "<h2>✅ Permissions Setup Complete!</h2>";
echo "<p>All necessary file permissions have been set.</p>";
echo "<p>The application should now be ready to run.</p>";
echo "</div>";

echo "<br>";
echo "<a href='web_setup.php' class='btn'>⬅️ Back to Setup</a>";
echo "<a href='/' class='btn btn-success'>🏠 Go to Home Page</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
