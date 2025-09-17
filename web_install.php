<?php
/**
 * PictureThis PHP App Web Installer
 * Deploys and configures the PHP application for production
 */

// Handle AJAX requests first, before any HTML output
if (isset($_GET['step'])) {
    // Prevent direct access without proper setup
    if (!isset($_GET['install']) || $_GET['install'] !== 'confirm') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    header('Content-Type: application/json');
    // Suppress PHP errors/warnings that might output HTML
    error_reporting(0);
    ini_set('display_errors', 0);

    try {
        switch ($_GET['step']) {
            case 'check_env':
                $result = checkEnvironment();
                break;
            case 'copy_files':
                $result = copyFiles();
                break;
            case 'setup_config':
                $result = setupConfiguration();
                break;
            case 'set_production':
                $result = setProductionMode();
                break;
            case 'test_config':
                $result = testConfiguration();
                break;
            default:
                $result = ['success' => false, 'message' => 'Unknown step'];
        }
    } catch (Exception $e) {
        $result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    } catch (Error $e) {
        $result = ['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()];
    }

    echo json_encode($result);
    exit;
}

// Prevent direct access without proper setup for HTML page
if (!isset($_GET['install']) || $_GET['install'] !== 'confirm') {
    die("Access denied. Use: web_install.php?install=confirm");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>PictureThis - PHP App Deployment</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007cba; background: #f8f9fa; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .progress { width: 100%; height: 20px; background: #e9ecef; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-bar { height: 100%; background: #007cba; transition: width 0.3s; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        button:disabled { background: #6c757d; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 PictureThis PHP App Deployment</h1>
        <div id='progress-container'>
            <div class='progress'>
                <div class='progress-bar' id='progress-bar' style='width: 0%'></div>
            </div>
        </div>
        <div id='output'></div>
    </div>

    <script>
        let currentStep = 0;
        const steps = [
            'Checking environment...',
            'Verifying PHP app deployment...',
            'Setting up configuration...',
            'Setting production mode...',
            'Testing configuration...',
            'Installation complete!'
        ];

        function updateProgress(step, message, type = 'info') {
            const output = document.getElementById('output');
            const progressBar = document.getElementById('progress-bar');

            const percent = ((step + 1) / steps.length) * 100;
            progressBar.style.width = percent + '%';

            const stepDiv = document.createElement('div');
            stepDiv.className = 'step ' + type;
            stepDiv.innerHTML = '<strong>Step ' + (step + 1) + ':</strong> ' + message;
            output.appendChild(stepDiv);

            output.scrollTop = output.scrollHeight;
        }

        async function runInstallation() {
            try {
                // Step 1: Check environment
                updateProgress(0, steps[0]);
                const envCheck = await fetch('?install=confirm&step=check_env');
                const envResult = await envCheck.json();
                updateProgress(0, envResult.message, envResult.success ? 'success' : 'error');

                if (!envResult.success) {
                    throw new Error('Environment check failed');
                }

                // Step 2: Copy files
                updateProgress(1, steps[1]);
                const copyCheck = await fetch('?install=confirm&step=copy_files');
                const copyResult = await copyCheck.json();
                updateProgress(1, copyResult.message, copyResult.success ? 'success' : 'error');

                if (!copyResult.success) {
                    throw new Error('File copy failed');
                }

                // Step 3: Setup configuration
                updateProgress(2, steps[2]);
                const configCheck = await fetch('?install=confirm&step=setup_config');
                const configResult = await configCheck.json();
                updateProgress(2, configResult.message, configResult.success ? 'success' : 'error');

                // Step 4: Set production mode
                updateProgress(3, steps[3]);
                const prodCheck = await fetch('?install=confirm&step=set_production');
                const prodResult = await prodCheck.json();
                updateProgress(3, prodResult.message, prodResult.success ? 'success' : 'error');

                // Step 5: Test configuration
                updateProgress(4, steps[4]);
                const testCheck = await fetch('?install=confirm&step=test_config');
                const testResult = await testCheck.json();
                updateProgress(4, testResult.message, testResult.success ? 'success' : 'error');

                // Step 6: Complete
                const nextSteps = '<br><br><strong>Next Steps:</strong><br>' +
                    '1. Run diagnostics: <a href=\"tests/diagnostics.php\" target=\"_blank\">tests/diagnostics.php</a><br>' +
                    '2. Access your application<br>' +
                    '3. Delete this installer file for security';
                updateProgress(5, steps[5] + nextSteps, 'success');

            } catch (error) {
                updateProgress(currentStep, 'Installation failed: ' + error.message, 'error');
            }
        }

        // Start installation
        runInstallation();
    </script>
</body>
</html>";

function checkEnvironment() {
    $issues = [];
    $currentDir = __DIR__;

    // Check if GitHub folder exists OR if files are already deployed
    $hasGithub = @is_dir('github');
    $hasConfig = @is_dir('config') && @file_exists('config/config.php');
    $hasTests = @is_dir('tests') && @file_exists('tests/diagnostics.php');
    $hasSrc = @is_dir('src');

    $debug = "Working directory: $currentDir\n";
    $debug .= "GitHub folder exists: " . ($hasGithub ? 'YES' : 'NO') . "\n";
    $debug .= "Config in root exists: " . ($hasConfig ? 'YES' : 'NO') . "\n";
    $debug .= "Tests in root exist: " . ($hasTests ? 'YES' : 'NO') . "\n";
    $debug .= "Src in root exists: " . ($hasSrc ? 'YES' : 'NO') . "\n";

    // First priority: Check if PHP app is already deployed in root
    if ($hasConfig && $hasTests && $hasSrc) {
        $debug .= "PHP app already deployed in root directory\n";
        $message = 'Environment check passed - PHP app already deployed in root';
        return ['success' => true, 'message' => $debug . $message];
    }

    // Second priority: Check if GitHub folder has the PHP app (this is the normal case)
    if ($hasGithub) {
        $debug .= "Checking GitHub folder structure:\n";

        // Check for the complete PHP app structure in github
        $githubConfig = @is_dir('github/picfePHPMYSQL/cpanel-html-mysql-app/config') && @file_exists('github/picfePHPMYSQL/cpanel-html-mysql-app/config/config.php');
        $githubTests = @file_exists('github/picfePHPMYSQL/cpanel-html-mysql-app/tests/diagnostics.php');
        $githubSrc = @is_dir('github/picfePHPMYSQL/cpanel-html-mysql-app/src');

        $debug .= "- github/picfePHPMYSQL/cpanel-html-mysql-app/config/config.php: " . ($githubConfig ? 'FOUND' : 'MISSING') . "\n";
        $debug .= "- github/picfePHPMYSQL/cpanel-html-mysql-app/tests/diagnostics.php: " . ($githubTests ? 'FOUND' : 'MISSING') . "\n";
        $debug .= "- github/picfePHPMYSQL/cpanel-html-mysql-app/src/: " . ($githubSrc ? 'FOUND' : 'MISSING') . "\n";

        if ($githubConfig && $githubTests && $githubSrc) {
            $debug .= "Complete PHP app found in github/ folder - ready to deploy\n";
            $message = 'Environment check passed - PHP app found in github/ folder, ready to copy to root';
            return ['success' => true, 'message' => $debug . $message];
        } else {
            $issues[] = 'GitHub folder exists but missing required PHP app files';
        }
    } else {
        $issues[] = 'No github/ folder found with PHP app';
    }

    // Check PHP version
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        $issues[] = 'PHP 8.0+ required, found ' . PHP_VERSION;
    }

    // Check write permissions
    if (!@is_writable('.')) {
        $issues[] = 'Current directory is not writable';
    }

    if (empty($issues)) {
        $message = 'Environment check passed';
        if ($hasGithub) {
            $message .= ' - GitHub folder found with PHP app structure';
        } elseif ($hasConfig && $hasTests && $hasSrc) {
            $message .= ' - PHP app already deployed in root';
        }
        return ['success' => true, 'message' => $debug . $message];
    } else {
        return ['success' => false, 'message' => $debug . 'PHP app deployment issues: ' . implode(', ', $issues)];
    }
}

function copyFiles() {
    try {
        $currentDir = __DIR__;
        $message = "Working directory: $currentDir\n";

        // Check if this is already a properly deployed PHP app in root
        if (is_dir('config') && file_exists('config/config.php') &&
            is_dir('src') && is_dir('tests') && file_exists('tests/diagnostics.php')) {

            $message .= "PHP application files found in current directory\n";
            $message .= "No file copying needed - app is already deployed\n";

            return ['success' => true, 'message' => $message . 'PHP app deployment verified - ready to configure'];
        }

        // Check if github folder exists with the complete PHP app
        $source = 'github/picfePHPMYSQL/cpanel-html-mysql-app/';
        if (is_dir($source)) {
            $message .= "GitHub folder found - checking for PHP app structure\n";

        // Check for complete PHP app structure in github
            if (is_dir($source . 'config') && file_exists($source . 'config/config.php') &&
                is_dir($source . 'src') && file_exists($source . 'tests/diagnostics.php')) {

                $message .= "Complete PHP app found in github/picfePHPMYSQL/cpanel-html-mysql-app/ folder\n";
                $message .= "Copying PHP app from github/picfePHPMYSQL/cpanel-html-mysql-app/ to root directory...\n";

                // Files/folders to exclude from copying
                $exclude = ['.git', 'node_modules', '.env', 'debug.log', 'web_install.php', '.DS_Store', 'backend', 'picfe', 'sql', 'tmp', '.next', '.vite'];

                if (copyDirectory($source, './', $exclude)) {
                    $message .= "Successfully copied PHP app to root directory\n";
                    return ['success' => true, 'message' => $message . 'PHP app deployed from github/picfePHPMYSQL/cpanel-html-mysql-app/ to root directory'];
                } else {
                    return ['success' => false, 'message' => $message . 'Failed to copy PHP app from github/picfePHPMYSQL/cpanel-html-mysql-app/ to root'];
                }
            } else {
                return ['success' => false, 'message' => $message . 'GitHub folder exists but does not contain complete PHP app structure'];
            }
        }

        // If neither condition is met
        return ['success' => false, 'message' => $message . 'PHP app not found. Expected: Complete app in github/picfePHPMYSQL/cpanel-html-mysql-app/ folder'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'PHP app deployment error: ' . $e->getMessage()];
    }
}

function copyDirectory($source, $destination, $exclude = []) {
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..' || in_array($file, $exclude)) {
            continue;
        }

        $srcPath = $source . $file;
        $destPath = $destination . $file;

        if (is_dir($srcPath)) {
            copyDirectory($srcPath . '/', $destPath . '/', $exclude);
        } else {
            copy($srcPath, $destPath);
        }
    }
    closedir($dir);
    return true;
}

function setupConfiguration() {
    try {
        // Create uploads directory
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }

        // Ensure tests directory exists and has proper permissions
        if (!is_dir('tests')) {
            mkdir('tests', 0755, true);
        }

        // Set proper permissions
        if (is_dir('uploads')) {
            chmod('uploads', 0755);
        }
        if (is_dir('tests')) {
            chmod('tests', 0755);
            // Ensure test files are readable
            if (file_exists('tests/diagnostics.php')) {
                chmod('tests/diagnostics.php', 0644);
            }
            if (file_exists('tests/index.php')) {
                chmod('tests/index.php', 0644);
            }
        }

        return ['success' => true, 'message' => 'Configuration setup completed - all diagnostic tools available in tests/ folder'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Configuration setup failed: ' . $e->getMessage()];
    }
}

function setProductionMode() {
    try {
        $configFile = 'config/config.php';

        if (!file_exists($configFile)) {
            return ['success' => false, 'message' => 'Config file not found'];
        }

        $content = file_get_contents($configFile);

        // Check if IS_PRODUCTION is already defined and true
        if (strpos($content, "define('IS_PRODUCTION', true)") !== false) {
            return ['success' => true, 'message' => 'Production mode already enabled'];
        }

        // Change IS_PRODUCTION to true
        $content = preg_replace(
            "/define\('IS_PRODUCTION',\s*false\);/",
            "define('IS_PRODUCTION', true);",
            $content
        );

        file_put_contents($configFile, $content);

        return ['success' => true, 'message' => 'Production mode enabled'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to set production mode: ' . $e->getMessage()];
    }
}

function testConfiguration() {
    try {
        // Test if config loads
        if (!file_exists('config/config.php')) {
            return ['success' => false, 'message' => 'Config file missing'];
        }

        require_once 'config/config.php';

        // Debug: output the database constants and env vars
        global $envVars;
        $debug = "Env vars loaded: " . (isset($envVars) ? json_encode($envVars) : 'NONE') . "\n";
        $debug .= "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT_DEFINED') . "\n";
        $debug .= "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT_DEFINED') . "\n";
        $debug .= "DB_PASS: " . (defined('DB_PASS') ? '***' : 'NOT_DEFINED') . "\n";
        $debug .= "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT_DEFINED') . "\n";
        $debug .= "IS_PRODUCTION: " . (defined('IS_PRODUCTION') ? (IS_PRODUCTION ? 'true' : 'false') : 'NOT_DEFINED') . "\n";

        // Test database connection
        require_once 'src/lib/db.php';
        $db = get_db();
        if (!$db) {
            // Try to get more specific error information
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $debug .= "Database connection successful on retry\n";
                return ['success' => true, 'message' => 'Configuration test passed. Debug: ' . $debug];
            } catch (PDOException $e) {
                $debug .= "Database connection error: " . $e->getMessage() . "\n";
                return ['success' => false, 'message' => 'Database connection failed. Debug: ' . $debug];
            }
        }

        return ['success' => true, 'message' => 'Configuration test passed. Debug: ' . $debug];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Configuration test failed: ' . $e->getMessage()];
    }
}
?>