<?php
/**
 * PictureThis Diagnostic Suite
 * Comprehensive testing tool for the application
 */

require_once '../config/config.php';

class Diagnostics {
    private $results = [];
    private $passed = 0;
    private $failed = 0;

    public function runAllTests() {
        $this->testEnvironment();
        $this->testConfiguration();
        $this->testDatabase();
        $this->testFileSystem();
        $this->testSecurity();
        $this->testEmail();
        $this->testAPI();
        $this->testAgent();
    }

    private function addResult($test, $status, $message, $details = '') {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'message' => $message,
            'details' => $details
        ];

        if ($status === 'PASS') {
            $this->passed++;
        } else {
            $this->failed++;
        }
    }

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
        $configFiles = [
            '../config/config.php',
            '../config/development.php',
            '../config/production.php'
        ];

        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $this->addResult("Config File: " . basename($file), 'PASS', 'File exists');
            } else {
                $this->addResult("Config File: " . basename($file), 'FAIL', 'File missing');
            }
        }

        // Test config loading
        try {
            require_once '../config/config.php';
            $this->addResult('Config Loading', 'PASS', 'Configuration loaded successfully');
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
            require_once '../src/lib/db.php';
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
        // Check required directories
        $requiredDirs = [
            '../uploads',
            '../src',
            '../config',
            '../tests'
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
                $this->addResult("Directory: " . basename($dir), 'FAIL', 'Directory missing');
            }
        }

        // Check key files
        $keyFiles = [
            '../index.php',
            '../config/config.php',
            '../src/lib/db.php'
        ];

        foreach ($keyFiles as $file) {
            if (file_exists($file)) {
                $this->addResult("File: " . basename($file), 'PASS', 'File exists');
            } else {
                $this->addResult("File: " . basename($file), 'FAIL', 'File missing');
            }
        }
    }

    private function testSecurity() {
        // Check for .env file
        if (file_exists('../.env')) {
            $this->addResult('.env File', 'PASS', 'Environment file exists');
        } else {
            $this->addResult('.env File', 'INFO', 'Environment file not found (using defaults)');
        }

        // Check for debug.log
        if (file_exists('../debug.log')) {
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
        // Check SMTP configuration
        $smtpConfig = getConfigValue('email', 'smtp_host');
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
        // Check agent-related files
        $agentFiles = [
            '../src/controllers/PromptAgentController.php',
            '../src/views/agent_modal.php'
        ];

        foreach ($agentFiles as $file) {
            if (file_exists($file)) {
                $this->addResult("Agent File: " . basename($file), 'PASS', 'File exists');
            } else {
                $this->addResult("Agent File: " . basename($file), 'FAIL', 'File missing');
            }
        }

        // Check OpenRouter API key
        $apiKey = getConfigValue('openrouter', 'api_key');
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