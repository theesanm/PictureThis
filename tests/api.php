<?php
/**
 * API Test Suite
 * Tests API endpoints and functionality
 */

require_once '../config/config.php';

class APITester {
    private $baseUrl;

    public function __construct() {
        $this->baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    }

    public function runTests() {
        $results = [];

        // Test basic endpoints
        $results[] = $this->testEndpoint('/', 'GET', 'Home Page');
        $results[] = $this->testEndpoint('/generate', 'GET', 'Generate Page');

        // Test API endpoints (without authentication for basic connectivity)
        $results[] = $this->testEndpoint('/api/user/credits', 'GET', 'User Credits API');
        $results[] = $this->testEndpoint('/api/generate', 'POST', 'Generate API');
        $results[] = $this->testEndpoint('/api/prompt-agent/start', 'POST', 'Agent API');

        // Test static files
        $results[] = $this->testStaticFile('/css/style.css', 'CSS File');
        $results[] = $this->testStaticFile('/js/main.js', 'JavaScript File');

        return $results;
    }

    private function testEndpoint($path, $method = 'GET', $description = '') {
        $url = $this->baseUrl . $path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Add headers
        $headers = [
            'User-Agent: PictureThis-API-Test/1.0',
            'Accept: application/json, text/html, */*'
        ];

        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'test' => "API Endpoint: $description",
                'status' => 'FAIL',
                'message' => 'Connection failed',
                'details' => 'cURL Error: ' . $error
            ];
        }

        // Check if endpoint is accessible (not necessarily successful)
        $isAccessible = $httpCode > 0 && $httpCode < 500;

        if ($isAccessible) {
            $status = 'PASS';
            $message = "Endpoint accessible (HTTP $httpCode)";
            $details = "Content-Type: $contentType, Response size: " . strlen($response) . " bytes";
        } else {
            $status = 'FAIL';
            $message = "Endpoint not accessible (HTTP $httpCode)";
            $details = "Expected 2xx-4xx range, got $httpCode";
        }

        return [
            'test' => "API Endpoint: $description",
            'status' => $status,
            'message' => $message,
            'details' => $details
        ];
    }

    private function testStaticFile($path, $description = '') {
        $url = $this->baseUrl . $path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'test' => "Static File: $description",
                'status' => 'FAIL',
                'message' => 'Connection failed',
                'details' => 'cURL Error: ' . $error
            ];
        }

        if ($httpCode === 200) {
            return [
                'test' => "Static File: $description",
                'status' => 'PASS',
                'message' => 'File accessible',
                'details' => "HTTP 200 OK"
            ];
        } else {
            return [
                'test' => "Static File: $description",
                'status' => 'FAIL',
                'message' => 'File not accessible',
                'details' => "HTTP $httpCode"
            ];
        }
    }
}

// Run tests if requested
if (isset($_GET['run'])) {
    header('Content-Type: application/json');

    $tester = new APITester();
    $results = $tester->runTests();

    echo json_encode(['tests' => $results]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>API Test Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test { margin: 10px 0; padding: 10px; border-radius: 4px; border-left: 4px solid; }
        .test.PASS { background: #d4edda; border-left-color: #28a745; }
        .test.FAIL { background: #f8d7da; border-left-color: #dc3545; }
        .details { font-size: 0.9em; color: #666; margin-top: 5px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 API Test Suite</h1>
        <p>Test API endpoints and static file accessibility</p>

        <button onclick="runTests()">Run API Tests</button>

        <div id="results" style="margin-top: 20px;"></div>
    </div>

    <script>
        async function runTests() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Running API tests...</p>';

            try {
                const response = await fetch('?run=1');
                const data = await response.json();

                displayResults(data.tests);
            } catch (error) {
                resultsDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }

        function displayResults(tests) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '';

            tests.forEach(test => {
                const testDiv = document.createElement('div');
                testDiv.className = 'test ' + test.status;

                let icon = '';
                switch(test.status) {
                    case 'PASS': icon = '✅'; break;
                    case 'FAIL': icon = '❌'; break;
                }

                testDiv.innerHTML = `
                    <strong>${icon} ${test.test}</strong>: ${test.message}
                    ${test.details ? '<div class="details">' + test.details + '</div>' : ''}
                `;

                resultsDiv.appendChild(testDiv);
            });
        }
    </script>
</body>
</html>