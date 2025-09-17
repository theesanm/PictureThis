<?php
/**
 * Web-Compatible Test Runner
 * Accessible via web browser for cPanel environments
 */

require_once __DIR__ . '/bootstrap.php';

// Set headers for HTML output
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

class WebTestRunner {

    private $testResults = [];
    private $output = '';

    public function __construct() {
        $this->startHtml();
    }

    private function startHtml() {
        $this->output .= '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Prompt Enhancement Agent - Test Suite</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        .content {
            padding: 30px;
        }
        .test-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .test-section {
            margin-bottom: 40px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }
        .section-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-title {
            margin: 0;
            font-size: 1.5em;
            color: #495057;
        }
        .section-status {
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-running { background: #cce5ff; color: #004085; }
        .status-passed { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .test-results {
            padding: 20px;
            background: #f8f9fa;
        }
        .test-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .test-item:last-child {
            border-bottom: none;
        }
        .test-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            font-size: 14px;
        }
        .test-passed { background: #d4edda; color: #155724; }
        .test-failed { background: #f8d7da; color: #721c24; }
        .test-name {
            flex: 1;
            font-weight: 500;
        }
        .test-duration {
            color: #6c757d;
            font-size: 0.9em;
        }
        .summary {
            background: #f8f9fa;
            padding: 30px;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }
        .summary-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat {
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #495057;
            display: block;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
        .error-details {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
        }
        .error-details h4 {
            margin: 0 0 10px 0;
            color: #721c24;
        }
        .error-details pre {
            background: white;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 0.9em;
        }
        @media (max-width: 768px) {
            .test-controls {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                text-align: center;
            }
            .summary-stats {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test Suite</h1>
            <p>Interactive Prompt Enhancement Agent</p>
        </div>
        <div class="content">';
    }

    private function endHtml() {
        $this->output .= '
        </div>
    </div>
</body>
</html>';
    }

    public function addTestControls() {
        $this->output .= '
        <div class="test-controls">
            <button class="btn btn-primary" onclick="runAllTests()">Run All Tests</button>
            <button class="btn btn-secondary" onclick="runUnitTests()">Unit Tests Only</button>
            <button class="btn btn-secondary" onclick="runDatabaseTests()">Database Tests Only</button>
            <button class="btn btn-secondary" onclick="runApiTests()">API Tests Only</button>
            <button class="btn btn-success" onclick="clearResults()">Clear Results</button>
        </div>

        <div id="test-output"></div>';
    }

    public function runUnitTests() {
        $this->output .= '<div class="test-section" id="unit-tests">';
        $this->output .= '<div class="section-header">';
        $this->output .= '<h2 class="section-title">Unit Tests</h2>';
        $this->output .= '<span class="section-status status-running">Running...</span>';
        $this->output .= '</div>';
        $this->output .= '<div class="test-results">';

        $unitTests = [
            'PromptAgentControllerTest' => __DIR__ . '/unit/PromptAgentControllerTest.php'
        ];

        $results = $this->runTestSuite($unitTests, 'unit');

        $this->output .= '</div></div>';

        return $results;
    }

    public function runDatabaseTests() {
        $this->output .= '<div class="test-section" id="database-tests">';
        $this->output .= '<div class="section-header">';
        $this->output .= '<h2 class="section-title">Database Tests</h2>';
        $this->output .= '<span class="section-status status-running">Running...</span>';
        $this->output .= '</div>';
        $this->output .= '<div class="test-results">';

        $databaseTests = [
            'SchemaTest' => __DIR__ . '/database/SchemaTest.php'
        ];

        $results = $this->runTestSuite($databaseTests, 'database');

        $this->output .= '</div></div>';

        return $results;
    }

    public function runApiTests() {
        $this->output .= '<div class="test-section" id="api-tests">';
        $this->output .= '<div class="section-header">';
        $this->output .= '<h2 class="section-title">API Integration Tests</h2>';
        $this->output .= '<span class="section-status status-running">Running...</span>';
        $this->output .= '</div>';
        $this->output .= '<div class="test-results">';

        $apiTests = [
            'AgentApiTest' => __DIR__ . '/api/AgentApiTest.php'
        ];

        $results = $this->runTestSuite($apiTests, 'api');

        $this->output .= '</div></div>';

        return $results;
    }

    private function runTestSuite($tests, $type) {
        $totalPassed = 0;
        $totalTests = 0;

        foreach ($tests as $testName => $testFile) {
            if (file_exists($testFile)) {
                $this->output .= "<div class='test-item'>";
                $this->output .= "<div class='test-icon status-running'>⏳</div>";
                $this->output .= "<span class='test-name'>Running $testName...</span>";
                $this->output .= "</div>";

                // Capture output and run test
                ob_start();
                $startTime = microtime(true);

                try {
                    // Include the test file and capture its return value
                    $testResults = include $testFile;

                    $endTime = microtime(true);
                    $duration = round(($endTime - $startTime) * 1000, 2); // ms

                    // Handle structured test results
                    if (is_array($testResults) && isset($testResults['results'])) {
                        $passed = $testResults['passed'];
                        $total = $testResults['total'];

                        // Display individual test results
                        foreach ($testResults['results'] as $result) {
                            $icon = $result['status'] === 'passed' ? '✅' : '❌';
                            $iconClass = $result['status'] === 'passed' ? 'test-passed' : 'test-failed';

                            $this->output .= "<div class='test-item'>";
                            $this->output .= "<div class='test-icon $iconClass'>$icon</div>";
                            $this->output .= "<span class='test-name'>{$result['name']}</span>";
                            $this->output .= "<span class='test-duration'>{$duration}ms</span>";
                            $this->output .= "</div>";

                            if ($result['status'] === 'failed' && isset($result['error'])) {
                                $this->output .= "<div class='error-details'>";
                                $this->output .= "<h4>Test Failed: {$result['name']}</h4>";
                                $this->output .= "<pre>" . htmlspecialchars($result['error']) . "</pre>";
                                $this->output .= "</div>";
                            }
                        }

                        $totalPassed += $passed;
                        $totalTests += $total;
                    } else {
                        // Fallback for old-style tests
                        $this->output .= "<div class='test-item'>";
                        $this->output .= "<div class='test-icon test-passed'>✅</div>";
                        $this->output .= "<span class='test-name'>$testName</span>";
                        $this->output .= "<span class='test-duration'>{$duration}ms</span>";
                        $this->output .= "</div>";
                        $totalPassed++;
                        $totalTests++;
                    }

                } catch (Exception $e) {
                    $endTime = microtime(true);
                    $duration = round(($endTime - $startTime) * 1000, 2);

                    $this->output .= "<div class='test-item'>";
                    $this->output .= "<div class='test-icon test-failed'>❌</div>";
                    $this->output .= "<span class='test-name'>$testName</span>";
                    $this->output .= "<span class='test-duration'>{$duration}ms</span>";
                    $this->output .= "</div>";

                    $this->output .= "<div class='error-details'>";
                    $this->output .= "<h4>Test Failed</h4>";
                    $this->output .= "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
                    $this->output .= "</div>";

                    $totalTests++;
                }

                ob_end_clean();
            } else {
                $this->output .= "<div class='test-item'>";
                $this->output .= "<div class='test-icon test-failed'>❌</div>";
                $this->output .= "<span class='test-name'>$testName</span>";
                $this->output .= "<span class='test-duration'>File not found</span>";
                $this->output .= "</div>";
                $totalTests++;
            }
        }

        $this->testResults[$type] = [
            'passed' => $totalPassed,
            'total' => $totalTests
        ];

        return $totalTests > 0 ? ($totalPassed === $totalTests) : false;
    }

    public function addSummary() {
        $totalPassed = 0;
        $totalTests = 0;

        foreach ($this->testResults as $type => $stats) {
            $totalPassed += $stats['passed'];
            $totalTests += $stats['total'];
        }

        $percentage = $totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 1) : 0;
        $progressWidth = $totalTests > 0 ? ($totalPassed / $totalTests) * 100 : 0;

        $this->output .= '<div class="summary">';
        $this->output .= '<div class="summary-stats">';
        $this->output .= '<div class="stat">';
        $this->output .= "<span class='stat-number'>$totalPassed</span>";
        $this->output .= '<span class="stat-label">Passed</span>';
        $this->output .= '</div>';
        $this->output .= '<div class="stat">';
        $this->output .= "<span class='stat-number'>$totalTests</span>";
        $this->output .= '<span class="stat-label">Total</span>';
        $this->output .= '</div>';
        $this->output .= '<div class="stat">';
        $this->output .= "<span class='stat-number'>{$percentage}%</span>";
        $this->output .= '<span class="stat-label">Success Rate</span>';
        $this->output .= '</div>';
        $this->output .= '</div>';

        $this->output .= '<div class="progress-bar">';
        $this->output .= "<div class='progress-fill' style='width: {$progressWidth}%'></div>";
        $this->output .= '</div>';

        if ($totalTests > 0 && $totalPassed === $totalTests) {
            $this->output .= '<p style="color: #28a745; font-weight: bold; font-size: 1.2em;">🎉 All tests passed!</p>';
        } elseif ($totalTests > 0) {
            $this->output .= '<p style="color: #dc3545; font-weight: bold; font-size: 1.2em;">⚠️ Some tests failed. Check the details above.</p>';
        } else {
            $this->output .= '<p style="color: #6c757d; font-weight: bold;">No tests were run.</p>';
        }

        $this->output .= '</div>';
    }

    public function getOutput() {
        $this->endHtml();
        return $this->output;
    }

    public function runAllTests() {
        $this->runUnitTests();
        $this->runDatabaseTests();
        $this->runApiTests();
        $this->addSummary();
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    $runner = new WebTestRunner();

    switch ($_GET['action']) {
        case 'run_unit':
            $result = $runner->runUnitTests();
            $runner->addSummary();
            break;
        case 'run_database':
            $result = $runner->runDatabaseTests();
            $runner->addSummary();
            break;
        case 'run_api':
            $result = $runner->runApiTests();
            $runner->addSummary();
            break;
        case 'run_all':
            $runner->runAllTests();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            exit;
    }

    echo json_encode([
        'html' => $runner->getOutput(),
        'success' => $result
    ]);
    exit;
}

// Display main page
$runner = new WebTestRunner();
$runner->addTestControls();

echo $runner->getOutput();
?>

<script>
// JavaScript for interactive test running
function runAllTests() {
    runTests('run_all', 'Running all tests...');
}

function runUnitTests() {
    runTests('run_unit', 'Running unit tests...');
}

function runDatabaseTests() {
    runTests('run_database', 'Running database tests...');
}

function runApiTests() {
    runTests('run_api', 'Running API tests...');
}

function runTests(action, message) {
    const outputDiv = document.getElementById('test-output');
    outputDiv.innerHTML = '<p style="text-align: center; padding: 20px;">' + message + '</p>';

    fetch(window.location.href + '?action=' + action)
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                // Extract just the test sections from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(data.html, 'text/html');
                const testSections = doc.querySelectorAll('.test-section');
                const summary = doc.querySelector('.summary');

                outputDiv.innerHTML = '';
                testSections.forEach(section => {
                    outputDiv.appendChild(section);
                });
                if (summary) {
                    outputDiv.appendChild(summary);
                }
            }
        })
        .catch(error => {
            outputDiv.innerHTML = '<p style="color: red; text-align: center; padding: 20px;">Error running tests: ' + error.message + '</p>';
        });
}

function clearResults() {
    document.getElementById('test-output').innerHTML = '';
}
</script>