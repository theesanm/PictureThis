<?php
/**
 * Test Helper Class
 * Provides common testing utilities and assertions
 */

class TestHelper {

    /**
     * Assert that a condition is true
     */
    public static function assert($condition, $message = '') {
        if (!$condition) {
            throw new Exception("Assertion failed: " . $message);
        }
    }

    /**
     * Assert that two values are equal
     */
    public static function assertEquals($expected, $actual, $message = '') {
        if ($expected != $actual) {
            throw new Exception("Assertion failed: expected '$expected', got '$actual'. " . $message);
        }
    }

    /**
     * Assert that a value is not null
     */
    public static function assertNotNull($value, $message = '') {
        if ($value === null) {
            throw new Exception("Assertion failed: value is null. " . $message);
        }
    }

    /**
     * Assert that an array contains a key
     */
    public static function assertArrayHasKey($key, $array, $message = '') {
        if (!is_array($array) || !array_key_exists($key, $array)) {
            throw new Exception("Assertion failed: array does not contain key '$key'. " . $message);
        }
    }

    /**
     * Assert that a string contains a substring
     */
    public static function assertStringContains($needle, $haystack, $message = '') {
        if (strpos($haystack, $needle) === false) {
            throw new Exception("Assertion failed: string does not contain '$needle'. " . $message);
        }
    }

    /**
     * Assert that a value is of a certain type
     */
    public static function assertInstanceOf($expectedClass, $actual, $message = '') {
        if (!($actual instanceof $expectedClass)) {
            $actualClass = is_object($actual) ? get_class($actual) : gettype($actual);
            throw new Exception("Assertion failed: expected instance of '$expectedClass', got '$actualClass'. " . $message);
        }
    }

    /**
     * Generate a test session ID
     */
    public static function generateTestSessionId() {
        return 'test_' . bin2hex(random_bytes(8));
    }

    /**
     * Generate test user data
     */
    public static function generateTestUser() {
        return [
            'id' => rand(10000, 99999),
            'email' => 'test_' . uniqid() . '@example.com',
            'credits' => 100
        ];
    }

    /**
     * Mock HTTP request for testing
     */
    public static function mockHttpRequest($method = 'GET', $uri = '/', $postData = null) {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;

        if ($postData && $method === 'POST') {
            $_POST = $postData;
        }

        return [
            'method' => $method,
            'uri' => $uri,
            'post' => $_POST
        ];
    }

    /**
     * Reset global state between tests
     */
    public static function resetGlobals() {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        $_SERVER = [];
    }

    /**
     * Capture output from a function
     */
    public static function captureOutput($callback) {
        ob_start();
        $callback();
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Run a test and report results (web-compatible)
     */
    public static function runTest($testName, $testCallback) {
        try {
            $testCallback();
            return ['status' => 'passed', 'name' => $testName];
        } catch (Exception $e) {
            return ['status' => 'failed', 'name' => $testName, 'error' => $e->getMessage()];
        }
    }

    /**
     * Run a test and report results (console output for CLI)
     */
    public static function runTestCli($testName, $testCallback) {
        echo "Running test: $testName... ";

        try {
            $testCallback();
            echo "✅ PASSED\n";
            return true;
        } catch (Exception $e) {
            echo "❌ FAILED: " . $e->getMessage() . "\n";
            return false;
        }
    }
}