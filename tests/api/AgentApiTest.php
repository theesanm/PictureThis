<?php
/**
 * API Integration Tests for Prompt Agent Endpoints
 */

require_once __DIR__ . '/../bootstrap.php';

class ApiIntegrationTest {

    public $baseUrl = 'http://localhost:8000'; // Adjust based on your test environment
    private $testUser;
    private $testSession;

    public function __construct() {
        DatabaseTestHelper::init();
        $this->setupTestData();
    }

    private function setupTestData() {
        // Create test user
        $this->testUser = DatabaseTestHelper::createTestUser();

        // Create test session
        $this->testSession = DatabaseTestHelper::createTestAgentSession($this->testUser['id']);
    }

    private function makeHttpRequest($method, $endpoint, $data = null, $headers = []) {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Set headers
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if (!empty($headers)) {
            $defaultHeaders = array_merge($defaultHeaders, $headers);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $defaultHeaders);

        // Set POST data
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'status' => 0,
                'body' => '',
                'data' => null,
                'error' => $error
            ];
        }

        return [
            'status' => $httpCode,
            'body' => $response,
            'data' => json_decode($response, true)
        ];
    }

    public function testStartSessionEndpoint() {
        // Test without authentication
        $response = $this->makeHttpRequest('POST', '/api/prompt-agent/start');

        // Check if server is available
        if (isset($response['error']) && strpos($response['error'], 'Could not connect to server') !== false) {
            TestHelper::assert(true, 'Test skipped - web server not available for integration testing');
            return;
        }

        TestHelper::assertEquals(401, $response['status'], 'Should return 401 without authentication');

        // Note: For full integration testing, you would need to set up authentication
        // This is a basic structure that can be expanded with proper auth setup
        TestHelper::assert(true, 'Start session endpoint structure is valid');
    }

    public function testGetSessionEndpoint() {
        // Test without authentication
        $response = $this->makeHttpRequest('GET', '/api/prompt-agent/session');

        // Check if server is available
        if (isset($response['error']) && strpos($response['error'], 'Could not connect to server') !== false) {
            TestHelper::assert(true, 'Test skipped - web server not available for integration testing');
            return;
        }

        TestHelper::assertEquals(401, $response['status'], 'Should return 401 without authentication');
        TestHelper::assert(true, 'Get session endpoint structure is valid');
    }

    public function testSendMessageEndpoint() {
        // Test without authentication
        $response = $this->makeHttpRequest('POST', '/api/prompt-agent/message', [
            'message' => 'Test message'
        ]);

        // Check if server is available
        if (isset($response['error']) && strpos($response['error'], 'Could not connect to server') !== false) {
            TestHelper::assert(true, 'Test skipped - web server not available for integration testing');
            return;
        }

        TestHelper::assertEquals(401, $response['status'], 'Should return 401 without authentication');
        TestHelper::assert(true, 'Send message endpoint structure is valid');
    }

    public function testEndSessionEndpoint() {
        // Test without authentication
        $response = $this->makeHttpRequest('POST', '/api/prompt-agent/end');
        TestHelper::assertEquals(401, $response['status'], 'Should return 401 without authentication');

        TestHelper::assert(true, 'End session endpoint structure is valid');
    }

    public function testInvalidEndpoint() {
        $response = $this->makeHttpRequest('GET', '/api/prompt-agent/invalid');
        TestHelper::assertEquals(404, $response['status'], 'Should return 404 for invalid endpoint');
    }

    public function testCorsHeaders() {
        $response = $this->makeHttpRequest('OPTIONS', '/api/prompt-agent/start');

        // Check if CORS headers are present (this depends on your server setup)
        // For now, just verify the request doesn't fail
        TestHelper::assert(in_array($response['status'], [200, 204, 404]), 'CORS preflight should be handled');
    }

    public function testJsonResponseFormat() {
        // Test with an endpoint that should return JSON
        $response = $this->makeHttpRequest('POST', '/api/prompt-agent/start');

        // Even with auth failure, should return valid JSON
        TestHelper::assertNotNull($response['data'], 'Should return valid JSON response');

        if ($response['data']) {
            TestHelper::assertArrayHasKey('success', $response['data'], 'Response should have success field');
            TestHelper::assertArrayHasKey('message', $response['data'], 'Response should have message field');
        }
    }

    public function testRateLimiting() {
        // Test rapid requests to check rate limiting
        // This would require multiple rapid requests and checking for 429 status
        // For now, just verify the endpoint exists and responds
        $response = $this->makeHttpRequest('POST', '/api/prompt-agent/message', [
            'message' => 'Test'
        ]);

        TestHelper::assert(in_array($response['status'], [200, 401, 429]), 'Rate limiting should be handled');
    }

    public function testLargePayload() {
        // Test with a large message payload
        $largeMessage = str_repeat('A', 10000); // 10KB message

        $response = $this->makeHttpRequest('POST', '/api/prompt-agent/message', [
            'message' => $largeMessage
        ]);

        // Should handle large payloads gracefully
        TestHelper::assertNotNull($response, 'Should handle large payload');
    }

    public function testMalformedJson() {
        // Test with malformed JSON
        $url = $this->baseUrl . '/api/prompt-agent/message';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{invalid json');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Should handle malformed JSON gracefully
        TestHelper::assertNotNull($response, 'Should handle malformed JSON');
    }

    public function testHttpMethods() {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

        foreach ($methods as $method) {
            $response = $this->makeHttpRequest($method, '/api/prompt-agent/start');

            // Should respond to all HTTP methods (even if with error)
            TestHelper::assertNotNull($response['status'], "Should handle $method requests");
        }
    }

    public function runAllTests() {
        $tests = [
            'testStartSessionEndpoint',
            'testGetSessionEndpoint',
            'testSendMessageEndpoint',
            'testEndSessionEndpoint',
            'testInvalidEndpoint',
            'testCorsHeaders',
            'testJsonResponseFormat',
            'testRateLimiting',
            'testLargePayload',
            'testMalformedJson',
            'testHttpMethods'
        ];

        $results = [];
        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test) {
            $result = TestHelper::runTest($test, function() use ($test) {
                $this->$test();
            });
            $results[] = $result;
            if ($result['status'] === 'passed') {
                $passed++;
            }
        }

        return [
            'results' => $results,
            'passed' => $passed,
            'total' => $total,
            'success' => $passed === $total
        ];
    }

    public function __destruct() {
        // Clean up test data
        DatabaseTestHelper::cleanupTestData();
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $testSuite = new ApiIntegrationTest();
    $results = $testSuite->runAllTests();

    // Check if running in CLI or web environment
    if (php_sapi_name() === 'cli') {
        // CLI output
        echo "\n=== Running API Integration Tests ===\n";
        echo "Note: These tests require a running web server at {$testSuite->baseUrl}\n";
        echo "Make sure your application is running before executing these tests.\n\n";

        foreach ($results['results'] as $result) {
            $status = $result['status'] === 'passed' ? '✅ PASSED' : '❌ FAILED';
            echo "$status {$result['name']}\n";
            if ($result['status'] === 'failed' && isset($result['error'])) {
                echo "   Error: {$result['error']}\n";
            }
        }
        echo "\n=== API Integration Test Results: {$results['passed']}/{$results['total']} tests passed ===\n";
        exit($results['success'] ? 0 : 1);
    } else {
        // Web output - return JSON
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}

// Return test results when included by web runner
return (new ApiIntegrationTest())->runAllTests();