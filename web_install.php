<?php
/**
 * PictureThis Web Installer
 * Run this script from your browser to install the application
 */

// Prevent direct access without proper setup
if (!isset($_GET['install']) || $_GET['install'] !== 'confirm') {
    die("Access denied. Use: web_install.php?install=confirm");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>PictureThis - Web Installer</title>
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
        <h1>🔧 PictureThis Web Installer</h1>
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
            'Copying files from GitHub folder...',
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
                const envCheck = await fetch('?step=check_env');
                const envResult = await envCheck.json();
                updateProgress(0, envResult.message, envResult.success ? 'success' : 'error');

                if (!envResult.success) {
                    throw new Error('Environment check failed');
                }

                // Step 2: Copy files
                updateProgress(1, steps[1]);
                const copyCheck = await fetch('?step=copy_files');
                const copyResult = await copyCheck.json();
                updateProgress(1, copyResult.message, copyResult.success ? 'success' : 'error');

                if (!copyResult.success) {
                    throw new Error('File copy failed');
                }

                // Step 3: Setup configuration
                updateProgress(2, steps[2]);
                const configCheck = await fetch('?step=setup_config');
                const configResult = await configCheck.json();
                updateProgress(2, configResult.message, configResult.success ? 'success' : 'error');

                // Step 4: Set production mode
                updateProgress(3, steps[3]);
                const prodCheck = await fetch('?step=set_production');
                const prodResult = await prodCheck.json();
                updateProgress(3, prodResult.message, prodResult.success ? 'success' : 'error');

                // Step 5: Test configuration
                updateProgress(4, steps[4]);
                const testCheck = await fetch('?step=test_config');
                const testResult = await testCheck.json();
                updateProgress(4, testResult.message, testResult.success ? 'success' : 'error');

                // Step 6: Complete
                updateProgress(5, steps[5] + '<br><br>' +
                    '<strong>Next Steps:</strong><br>' +
                    '1. Run diagnostics: <a href=\"tests/diagnostics.php\" target=\"_blank\">tests/diagnostics.php</a><br>' +
                    '2. Access your application<br>' +
                    '3. Delete this installer file for security', 'success');

            } catch (error) {
                updateProgress(currentStep, 'Installation failed: ' + error.message, 'error');
            }
        }

        // Start installation
        runInstallation();
    </script>
</body>
</html>";

// Handle AJAX requests
if (isset($_GET['step'])) {
    header('Content-Type: application/json');

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

    echo json_encode($result);
    exit;
}

function checkEnvironment() {
    $issues = [];

    // Check if GitHub folder exists
    if (!is_dir('github')) {
        $issues[] = 'GitHub folder not found';
    }

    // Check if GitHub folder has the app
    if (!is_dir('github/config') || !is_dir('github/src')) {
        $issues[] = 'GitHub folder does not contain the application';
    }

    // Check PHP version
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        $issues[] = 'PHP 8.0+ required, found ' . PHP_VERSION;
    }

    // Check write permissions
    if (!is_writable('.')) {
        $issues[] = 'Current directory is not writable';
    }

    if (empty($issues)) {
        return ['success' => true, 'message' => 'Environment check passed'];
    } else {
        return ['success' => false, 'message' => 'Environment issues: ' . implode(', ', $issues)];
    }
}

function copyFiles() {
    $source = 'github/';
    $destination = './';

    try {
        // Copy all files except certain directories
        $exclude = ['.git', 'node_modules', '.env', 'debug.log'];

        if (copyDirectory($source, $destination, $exclude)) {
            return ['success' => true, 'message' => 'Files copied successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to copy files'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Copy error: ' . $e->getMessage()];
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

        // Create tests directory
        if (!is_dir('tests')) {
            mkdir('tests', 0755, true);
        }

        // Set proper permissions
        chmod('uploads', 0755);
        chmod('tests', 0755);

        return ['success' => true, 'message' => 'Configuration setup completed'];
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

        // Test database connection
        require_once 'src/lib/db.php';
        $db = get_db();
        if (!$db) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }

        return ['success' => true, 'message' => 'Configuration test passed'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Configuration test failed: ' . $e->getMessage()];
    }
}
?>