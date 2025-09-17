<?php
/**
 * Subdomain Deployment Helper
 * This script helps identify which files to copy to your subdomain
 */

// Define source and destination
$sourceDir = __DIR__; // Current directory (picfePHPMYSQL/cpanel-html-mysql-app)
$subdomainPath = '/home/cfoxcozj/public_html/demo'; // Your subdomain path

echo "<h1>Subdomain Deployment Helper</h1>";
echo "<h2>demo.cfox.co.za</h2>";
echo "<pre>";

// Essential files that MUST be copied
$essentialFiles = [
    'index.php',
    'config/config.php',
    'src/',
    'public/',
    'uploads/',
    '.htaccess',
    'setup_database.php',
    'test_database.php'
];

// Optional files (can be deleted after setup)
$optionalFiles = [
    'post_deployment_check.php',
    'CPANEL_DEPLOYMENT_GUIDE.md',
    'DATABASE_SETUP_README.md',
    'DEPLOYMENT_CHECKLIST.md',
    'SUBDOMAIN_DEPLOYMENT_GUIDE.md',
    'README.md'
];

// Files to ignore (Next.js, Node.js, etc.)
$ignorePatterns = [
    'picfe/',
    'backend/',
    'node_modules/',
    '.next/',
    '.env',
    'docker',
    '.git',
    'tmp/',
    'sql/',
    'scripts/',
    'test_*.php' // except the ones we need
];

echo "📁 Current Directory: " . basename($sourceDir) . "\n";
echo "🎯 Target Subdomain: demo.cfox.co.za\n\n";

echo "✅ ESSENTIAL FILES TO COPY:\n";
foreach ($essentialFiles as $file) {
    $fullPath = $sourceDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "  ✅ $file\n";
    } else {
        echo "  ❌ $file (MISSING)\n";
    }
}

echo "\n📄 OPTIONAL FILES (delete after setup):\n";
foreach ($optionalFiles as $file) {
    $fullPath = $sourceDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "  📄 $file\n";
    }
}

echo "\n🚫 FILES TO IGNORE (not needed for PHP/MySQL):\n";
foreach ($ignorePatterns as $pattern) {
    echo "  🚫 $pattern\n";
}

echo "\n📋 COPY COMMAND:\n";
echo "cp -r " . implode(' ', $essentialFiles) . " $subdomainPath/\n";

echo "\n⚙️ POST-COPY STEPS:\n";
echo "1. Update config/config.php with your domain\n";
echo "2. Set permissions: chmod 755 uploads/ public/uploads/\n";
echo "3. Run: https://demo.cfox.co.za/setup_database.php\n";
echo "4. Test: https://demo.cfox.co.za/test_database.php\n";
echo "5. Visit: https://demo.cfox.co.za\n";

echo "\n🎉 Ready for subdomain deployment!\n";
echo "</pre>";
echo "<style>body{font-family:monospace;background:#f5f5f5;padding:20px;}h1{color:#333;}h2{color:#666;}</style>";
?>
