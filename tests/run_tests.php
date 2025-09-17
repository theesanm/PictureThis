<?php
/**
 * Test Runner Script
 * Runs all test suites and reports results
 */

require_once __DIR__ . '/bootstrap.php';

class TestRunner {

    private $testResults = [];

    public function runUnitTests() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "RUNNING UNIT TESTS\n";
        echo str_repeat("=", 50) . "\n";

        $unitTests = [
            'PromptAgentControllerTest' => __DIR__ . '/unit/PromptAgentControllerTest.php'
        ];

        return $this->runTestSuite($unitTests, 'unit');
    }

    public function runDatabaseTests() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "RUNNING DATABASE TESTS\n";
        echo str_repeat("=", 50) . "\n";

        $databaseTests = [
            'SchemaTest' => __DIR__ . '/database/SchemaTest.php'
        ];

        return $this->runTestSuite($databaseTests, 'database');
    }

    public function runIntegrationTests() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "RUNNING INTEGRATION TESTS\n";
        echo str_repeat("=", 50) . "\n";

        $integrationTests = [
            'AgentApiTest' => __DIR__ . '/api/AgentApiTest.php'
        ];

        return $this->runTestSuite($integrationTests, 'integration');
    }

    private function runTestSuite($tests, $type) {
        $totalPassed = 0;
        $totalTests = 0;

        foreach ($tests as $testName => $testFile) {
            if (file_exists($testFile)) {
                echo "\nRunning $testName...\n";

                // Capture output and run test
                ob_start();
                include $testFile;
                $output = ob_get_clean();

                echo $output;

                // Try to extract test results from output
                if (preg_match('/(\d+)\/(\d+) tests passed/', $output, $matches)) {
                    $passed = (int) $matches[1];
                    $total = (int) $matches[2];
                    $totalPassed += $passed;
                    $totalTests += $total;
                }
            } else {
                echo "❌ $testName: Test file not found: $testFile\n";
            }
        }

        $this->testResults[$type] = [
            'passed' => $totalPassed,
            'total' => $totalTests
        ];

        return $totalTests > 0 ? ($totalPassed === $totalTests) : false;
    }

    public function runAllTests() {
        $results = [];

        $results['unit'] = $this->runUnitTests();
        $results['database'] = $this->runDatabaseTests();
        $results['integration'] = $this->runIntegrationTests();

        $this->printSummary($results);

        return !in_array(false, $results, true);
    }

    private function printSummary($results) {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat("=", 60) . "\n";

        $totalPassed = 0;
        $totalTests = 0;

        foreach ($this->testResults as $type => $stats) {
            $status = $results[$type] ? '✅ PASSED' : '❌ FAILED';
            $percentage = $stats['total'] > 0 ? round(($stats['passed'] / $stats['total']) * 100, 1) : 0;

            echo sprintf("%-15s: %s (%d/%d tests - %s%%)\n",
                ucfirst($type),
                $status,
                $stats['passed'],
                $stats['total'],
                $percentage
            );

            $totalPassed += $stats['passed'];
            $totalTests += $stats['total'];
        }

        echo str_repeat("-", 60) . "\n";
        $overallPercentage = $totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 1) : 0;
        $overallStatus = (!in_array(false, $results, true)) ? '✅ ALL TESTS PASSED' : '❌ SOME TESTS FAILED';

        echo sprintf("OVERALL: %s (%d/%d tests - %s%%)\n",
            $overallStatus,
            $totalPassed,
            $totalTests,
            $overallPercentage
        );

        echo str_repeat("=", 60) . "\n";
    }

    public function runSpecificTest($testType, $testName = null) {
        switch ($testType) {
            case 'unit':
                return $this->runUnitTests();
            case 'database':
                return $this->runDatabaseTests();
            case 'integration':
                return $this->runIntegrationTests();
            default:
                echo "❌ Invalid test type: $testType\n";
                echo "Available types: unit, database, integration\n";
                return false;
        }
    }
}

// Command line interface
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $runner = new TestRunner();

    $args = $_SERVER['argv'];
    array_shift($args); // Remove script name

    if (empty($args)) {
        // Run all tests
        $success = $runner->runAllTests();
    } elseif (count($args) === 1) {
        // Run specific test type
        $testType = $args[0];
        $success = $runner->runSpecificTest($testType);
    } else {
        echo "Usage: php run_tests.php [test_type]\n";
        echo "  test_type: unit, database, integration (optional - runs all if not specified)\n";
        echo "\nExamples:\n";
        echo "  php run_tests.php              # Run all tests\n";
        echo "  php run_tests.php unit         # Run only unit tests\n";
        echo "  php run_tests.php database     # Run only database tests\n";
        exit(1);
    }

    exit($success ? 0 : 1);
}