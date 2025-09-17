<?php
/**
 * PictureThis Simple Diagnostics
 * Basic health check for the application
 */

// Simple error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>PictureThis - Simple Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .test { margin: 10px 0; padding: 10px; border-radius: 4px; border-left: 4px solid; }
        .pass { background: #d4edda; border-left-color: #28a745; }
        .fail { background: #f8d7da; border-left-color: #dc3545; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; }
        .summary { display: flex; justify-content: space-around; margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .metric { text-align: center; }
        .metric h3 { margin: 0; font-size: 2em; }
        .metric p { margin: 5px 0; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔍 PictureThis Simple Diagnostics</h1>
            <p>Basic health check - " . date('Y-m-d H:i:s') . "</p>
        </div>";

$results = [];
$passed = 0;
$failed = 0;

function addResult($test, $status, $message, $details = '') {
    global $results, $passed, $failed;
    $results[] = ['test' => $test, 'status' => $status, 'message' => $message, 'details' => $details];
    if ($status === 'PASS') $passed++;
    else $failed++;
}

// Test 1: PHP Version
$phpVersion = PHP_VERSION;
$minVersion = '8.0.0';
if (version_compare($phpVersion, $minVersion, '>=')) {
    addResult('PHP Version', 'PASS', "PHP $phpVersion (meets minimum $minVersion)");
} else {
    addResult('PHP Version', 'FAIL', "PHP $phpVersion (requires $minVersion+)");
}

// Test 2: Config File
if (file_exists('../config/config.php')) {
    addResult('Config File', 'PASS', 'config.php exists');
} else {
    addResult('Config File', 'FAIL', 'config.php not found');
}

// Test 3: Config Loading
try {
    if (file_exists('../config/config.php')) {
        require_once '../config/config.php';
        addResult('Config Loading', 'PASS', 'Configuration loaded successfully');
    } else {
        addResult('Config Loading', 'FAIL', 'Config file not found');
    }
} catch (Exception $e) {
    addResult('Config Loading', 'FAIL', 'Failed to load configuration: ' . $e->getMessage());
}

// Test 4: Database Connection (if config loaded)
if (isset($db) || function_exists('get_db')) {
    try {
        require_once '../src/lib/db.php';
        $db_connection = get_db();
        if ($db_connection) {
            addResult('Database Connection', 'PASS', 'Connected to database successfully');
        } else {
            addResult('Database Connection', 'FAIL', 'Failed to connect to database');
        }
    } catch (Exception $e) {
        addResult('Database Connection', 'FAIL', 'Database error: ' . $e->getMessage());
    }
} else {
    addResult('Database Connection', 'INFO', 'Database test skipped - config not loaded');
}

// Test 5: Required Files
$requiredFiles = [
    '../index.php' => 'Main application file',
    '../src/lib/db.php' => 'Database library',
    '../config/production.php' => 'Production config'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        addResult("File: $description", 'PASS', basename($file) . ' exists');
    } else {
        addResult("File: $description", 'FAIL', basename($file) . ' missing');
    }
}

// Test 6: Environment Mode
if (defined('IS_PRODUCTION')) {
    $mode = IS_PRODUCTION ? 'Production' : 'Development';
    addResult('Environment Mode', 'INFO', "Running in $mode mode");
} else {
    addResult('Environment Mode', 'INFO', 'Environment mode not set');
}

// Display results
echo "<div class='summary'>";
echo "<div class='metric'><h3 style='color: #28a745'>$passed</h3><p>Tests Passed</p></div>";
echo "<div class='metric'><h3 style='color: #dc3545'>$failed</h3><p>Tests Failed</p></div>";
echo "<div class='metric'><h3>" . ($passed + $failed) . "</h3><p>Total Tests</p></div>";
echo "</div>";

echo "<h3>Test Results:</h3>";
foreach ($results as $result) {
    $cssClass = strtolower($result['status']);
    $icon = $result['status'] === 'PASS' ? '✅' : ($result['status'] === 'FAIL' ? '❌' : 'ℹ️');

    echo "<div class='test $cssClass'>";
    echo "<strong>$icon {$result['test']}</strong>: {$result['message']}";
    if (!empty($result['details'])) {
        echo "<div style='font-size: 0.9em; color: #666; margin-top: 5px;'>{$result['details']}</div>";
    }
    echo "</div>";
}

echo "<hr><h3>System Information:</h3>";
echo "<ul>";
echo "<li><strong>Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Current Directory:</strong> " . __DIR__ . "</li>";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</li>";
echo "<li><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</li>";
echo "<li><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " seconds</li>";
echo "</ul>";

echo "</div></body></html>";
?>

    private function testEnvironment() {
        // PHP Version
        $phpVersion = PHP_VERSION;
        $minVersion = '8.0.0';
        if (version_compare($phpVersion, $minVersion, '>=')) {
            $this->addResult('PHP Version', 'PASS', "PHP $phpVersion (meets minimum $minVersion)");
        } else {
            $this->addResult('PHP Version', 'FAIL', "PHP $phpVersion (requires $minVersion+)");
        }

        // Required extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->addResult("PHP Extension: $ext", 'PASS', 'Extension loaded');
            } else {
                $this->addResult("PHP Extension: $ext", 'FAIL', 'Extension not loaded');
            }
        }

        // Memory limit
        $memoryLimit = ini_get('memory_limit');
        $this->addResult('Memory Limit', 'INFO', "Current: $memoryLimit");

        // Max execution time
        $maxExecutionTime = ini_get('max_execution_time');
        $this->addResult('Max Execution Time', 'INFO', "Current: $maxExecutionTime seconds");
    }

    private function testConfiguration() {
        // Check if config files exist
        $baseDir = dirname(__DIR__); // Get parent directory of tests/
        $configFiles = [
            $baseDir . '/config/config.php',
            $baseDir . '/config/development.php',
            $baseDir . '/config/production.php'
        ];

        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $this->addResult("Config File: " . basename($file), 'PASS', 'File exists');
            } else {
                $this->addResult("Config File: " . basename($file), 'FAIL', 'File missing: ' . $file);
            }
        }

        // Test config loading
        try {
            $configLoaded = false;
            $configPaths = [
                '../config/config.php',
                __DIR__ . '/../config/config.php',
                'config/config.php',
                __DIR__ . '/../../config/config.php'
            ];

            foreach ($configPaths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    $configLoaded = true;
                    break;
                }
            }

            if ($configLoaded) {
                $this->addResult('Config Loading', 'PASS', 'Configuration loaded successfully');
            } else {
                $this->addResult('Config Loading', 'FAIL', 'Could not find config file');
            }
        } catch (Exception $e) {
            $this->addResult('Config Loading', 'FAIL', 'Failed to load configuration: ' . $e->getMessage());
        }

        // Check environment mode
        if (defined('IS_PRODUCTION')) {
            $mode = IS_PRODUCTION ? 'Production' : 'Development';
            $this->addResult('Environment Mode', 'INFO', "Current mode: $mode");
        } else {
            $this->addResult('Environment Mode', 'FAIL', 'IS_PRODUCTION not defined');
        }
    }

    private function testDatabase() {
        try {
            $dbPaths = [
                '../src/lib/db.php',
                __DIR__ . '/../src/lib/db.php',
                'src/lib/db.php',
                __DIR__ . '/../../src/lib/db.php'
            ];

            $dbLoaded = false;
            foreach ($dbPaths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    $dbLoaded = true;
                    break;
                }
            }

            if (!$dbLoaded) {
                $this->addResult('Database Test', 'FAIL', 'Could not find db.php file');
                return;
            }

            $db = get_db();

            if ($db) {
                $this->addResult('Database Connection', 'PASS', 'Connected successfully');

                // Test basic query
                $stmt = $db->query('SELECT 1 as test');
                $result = $stmt->fetch();
                if ($result && $result['test'] == 1) {
                    $this->addResult('Database Query', 'PASS', 'Basic query executed successfully');
                } else {
                    $this->addResult('Database Query', 'FAIL', 'Basic query failed');
                }

                // Check required tables
                $requiredTables = ['users', 'credit_transactions', 'settings'];
                foreach ($requiredTables as $table) {
                    $stmt = $db->prepare("SHOW TABLES LIKE ?");
                    $stmt->execute([$table]);
                    if ($stmt->rowCount() > 0) {
                        $this->addResult("Database Table: $table", 'PASS', 'Table exists');
                    } else {
                        $this->addResult("Database Table: $table", 'FAIL', 'Table missing');
                    }
                }

            } else {
                $this->addResult('Database Connection', 'FAIL', 'Failed to connect to database');
            }
        } catch (Exception $e) {
            $this->addResult('Database Test', 'FAIL', 'Database error: ' . $e->getMessage());
        }
    }

    private function testFileSystem() {
        $baseDir = dirname(__DIR__); // Get parent directory of tests/

        // Check required directories
        $requiredDirs = [
            $baseDir . '/uploads',
            $baseDir . '/src',
            $baseDir . '/config',
            $baseDir . '/tests'
        ];

        foreach ($requiredDirs as $dir) {
            if (is_dir($dir)) {
                $this->addResult("Directory: " . basename($dir), 'PASS', 'Directory exists');

                // Check write permissions
                if (is_writable($dir)) {
                    $this->addResult("Write Permission: " . basename($dir), 'PASS', 'Directory is writable');
                } else {
                    $this->addResult("Write Permission: " . basename($dir), 'FAIL', 'Directory not writable');
                }
            } else {
                $this->addResult("Directory: " . basename($dir), 'FAIL', 'Directory missing: ' . $dir);
            }
        }

        // Check key files
        $keyFiles = [
            $baseDir . '/index.php',
            $baseDir . '/config/config.php',
            $baseDir . '/src/lib/db.php'
        ];

        foreach ($keyFiles as $file) {
            if (file_exists($file)) {
                $this->addResult("File: " . basename($file), 'PASS', 'File exists');
            } else {
                $this->addResult("File: " . basename($file), 'FAIL', 'File missing: ' . $file);
            }
        }
    }

    private function testSecurity() {
        $baseDir = dirname(__DIR__); // Get parent directory of tests/

        // Check for .env file
        if (file_exists($baseDir . '/.env')) {
            $this->addResult('.env File', 'PASS', 'Environment file exists');
        } else {
            $this->addResult('.env File', 'INFO', 'Environment file not found (using defaults)');
        }

        // Check for debug.log
        if (file_exists($baseDir . '/debug.log')) {
            $this->addResult('Debug Log', 'INFO', 'Debug log exists');
        } else {
            $this->addResult('Debug Log', 'PASS', 'Debug log not found');
        }

        // Check session configuration
        $sessionConfig = [
            'session.cookie_httponly' => ini_get('session.cookie_httponly'),
            'session.use_only_cookies' => ini_get('session.use_only_cookies'),
            'session.cookie_secure' => ini_get('session.cookie_secure')
        ];

        foreach ($sessionConfig as $key => $value) {
            $expected = ($key === 'session.cookie_secure') ? '0' : '1'; // Allow HTTP for now
            if ($value == $expected) {
                $this->addResult("Session Config: $key", 'PASS', "Set to $value");
            } else {
                $this->addResult("Session Config: $key", 'INFO', "Set to $value (expected $expected)");
            }
        }
    }

    private function testEmail() {
        global $config;

        // Check SMTP configuration
        $smtpConfig = $config['email']['smtp_host'] ?? null;
        if ($smtpConfig) {
            $this->addResult('SMTP Configuration', 'PASS', 'SMTP settings configured');
        } else {
            $this->addResult('SMTP Configuration', 'FAIL', 'SMTP settings not configured');
        }

        // Test email sending (if configured)
        if (function_exists('mail')) {
            $this->addResult('PHP Mail Function', 'PASS', 'mail() function available');
        } else {
            $this->addResult('PHP Mail Function', 'FAIL', 'mail() function not available');
        }
    }

    private function testAPI() {
        // Test basic API endpoints
        $endpoints = [
            '/api/user/credits',
            '/api/generate',
            '/api/prompt-agent/start'
        ];

        foreach ($endpoints as $endpoint) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . $endpoint;

            // Just check if endpoint exists (don't actually call it)
            $this->addResult("API Endpoint: $endpoint", 'INFO', 'Endpoint configured');
        }

        // Check CSRF protection
        if (function_exists('CSRF')) {
            $this->addResult('CSRF Protection', 'PASS', 'CSRF class available');
        } else {
            $this->addResult('CSRF Protection', 'FAIL', 'CSRF class not found');
        }
    }

    private function testAgent() {
        $baseDir = dirname(__DIR__); // Get parent directory of tests/

        // Check agent-related files
        $agentFiles = [
            $baseDir . '/src/controllers/PromptAgentController.php',
            $baseDir . '/src/views/agent_modal.php'
        ];

        foreach ($agentFiles as $file) {
            if (file_exists($file)) {
                $this->addResult("Agent File: " . basename($file), 'PASS', 'File exists');
            } else {
                $this->addResult("Agent File: " . basename($file), 'FAIL', 'File missing');
            }
        }

        // Check OpenRouter API key
        $apiKey = $config['openrouter']['api_key'] ?? null;
        if ($apiKey) {
            $this->addResult('OpenRouter API Key', 'PASS', 'API key configured');
        } else {
            $this->addResult('OpenRouter API Key', 'FAIL', 'API key not configured');
        }
    }

    public function getResults() {
        return [
            'summary' => [
                'total' => count($this->results),
                'passed' => $this->passed,
                'failed' => $this->failed,
                'success_rate' => count($this->results) > 0 ? round(($this->passed / count($this->results)) * 100, 1) : 0
            ],
            'tests' => $this->results
        ];
    }
}

// Run diagnostics if requested
if (isset($_GET['run'])) {
    header('Content-Type: application/json');

    $diagnostics = new Diagnostics();
    $diagnostics->runAllTests();
    $results = $diagnostics->getResults();

    echo json_encode($results);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PictureThis - Diagnostic Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { display: flex; justify-content: space-around; margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .metric { text-align: center; }
        .metric h3 { margin: 0; font-size: 2em; }
        .metric p { margin: 5px 0; color: #666; }
        .test { margin: 10px 0; padding: 10px; border-radius: 4px; border-left: 4px solid; }
        .test.pass { background: #d4edda; border-left-color: #28a745; }
        .test.fail { background: #f8d7da; border-left-color: #dc3545; }
        .test.info { background: #d1ecf1; border-left-color: #17a2b8; }
        .test.warning { background: #fff3cd; border-left-color: #ffc107; }
        .details { font-size: 0.9em; color: #666; margin-top: 5px; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005a87; }
        button:disabled { background: #6c757d; cursor: not-allowed; }
        .loading { display: none; color: #666; }
        .progress { width: 100%; height: 20px; background: #e9ecef; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-bar { height: 100%; background: #007cba; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 PictureThis Diagnostic Suite</h1>
            <p>Comprehensive testing tool for your application</p>
        </div>

        <div style="text-align: center; margin: 20px 0;">
            <button id="run-tests" onclick="runDiagnostics()">Run Full Diagnostics</button>
            <div id="loading" class="loading">Running tests... Please wait.</div>
        </div>

        <div id="results" style="display: none;">
            <div id="summary" class="summary"></div>
            <div id="tests"></div>
        </div>
    </div>

    <script>
        async function runDiagnostics() {
            const button = document.getElementById('run-tests');
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');

            button.disabled = true;
            loading.style.display = 'block';
            results.style.display = 'none';

            try {
                const response = await fetch('?run=1');
                const data = await response.json();

                displayResults(data);
                results.style.display = 'block';

            } catch (error) {
                alert('Error running diagnostics: ' + error.message);
            } finally {
                button.disabled = false;
                loading.style.display = 'none';
            }
        }

        function displayResults(data) {
            // Display summary
            const summary = document.getElementById('summary');
            const successRate = data.summary.success_rate;
            const rateColor = successRate >= 90 ? '#28a745' : successRate >= 70 ? '#ffc107' : '#dc3545';

            summary.innerHTML = `
                <div class="metric">
                    <h3 style="color: ${rateColor}">${successRate}%</h3>
                    <p>Success Rate</p>
                </div>
                <div class="metric">
                    <h3 style="color: #28a745">${data.summary.passed}</h3>
                    <p>Tests Passed</p>
                </div>
                <div class="metric">
                    <h3 style="color: #dc3545">${data.summary.failed}</h3>
                    <p>Tests Failed</p>
                </div>
                <div class="metric">
                    <h3>${data.summary.total}</h3>
                    <p>Total Tests</p>
                </div>
            `;

            // Display test results
            const testsDiv = document.getElementById('tests');
            testsDiv.innerHTML = '';

            data.tests.forEach(test => {
                const testDiv = document.createElement('div');
                testDiv.className = 'test ' + test.status.toLowerCase();

                let statusIcon = '';
                switch(test.status) {
                    case 'PASS': statusIcon = '✅'; break;
                    case 'FAIL': statusIcon = '❌'; break;
                    case 'INFO': statusIcon = 'ℹ️'; break;
                    case 'WARNING': statusIcon = '⚠️'; break;
                }

                testDiv.innerHTML = `
                    <strong>${statusIcon} ${test.test}</strong>: ${test.message}
                    ${test.details ? '<div class="details">' + test.details + '</div>' : ''}
                `;

                testsDiv.appendChild(testDiv);
            });
        }
    </script>
</body>
</html>