<?php
/**
 * Database Connectivity Test
 * Tests database connection and basic operations
 */

require_once '../config/config.php';

class DatabaseTester {
    private $db;

    public function __construct() {
        require_once '../src/lib/db.php';
        $this->db = get_db();
    }

    public function runTests() {
        $results = [];

        // Connection test
        $results[] = $this->testConnection();

        // Basic query test
        $results[] = $this->testBasicQuery();

        // Table existence tests
        $results[] = $this->testTableExists('users');
        $results[] = $this->testTableExists('credit_transactions');
        $results[] = $this->testTableExists('settings');

        // Data integrity tests
        $results[] = $this->testUserTableStructure();
        $results[] = $this->testCreditTransactionsTableStructure();

        // Performance test
        $results[] = $this->testQueryPerformance();

        return $results;
    }

    private function testConnection() {
        if ($this->db) {
            return [
                'test' => 'Database Connection',
                'status' => 'PASS',
                'message' => 'Successfully connected to database',
                'details' => 'Connection established using PDO'
            ];
        } else {
            return [
                'test' => 'Database Connection',
                'status' => 'FAIL',
                'message' => 'Failed to connect to database',
                'details' => 'Check database credentials in configuration'
            ];
        }
    }

    private function testBasicQuery() {
        try {
            $stmt = $this->db->query('SELECT 1 as test_value, NOW() as current_time');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['test_value'] == 1) {
                return [
                    'test' => 'Basic Query Execution',
                    'status' => 'PASS',
                    'message' => 'Basic SELECT query executed successfully',
                    'details' => 'Current database time: ' . $result['current_time']
                ];
            } else {
                return [
                    'test' => 'Basic Query Execution',
                    'status' => 'FAIL',
                    'message' => 'Query executed but returned unexpected results',
                    'details' => 'Expected test_value=1, got: ' . ($result['test_value'] ?? 'null')
                ];
            }
        } catch (Exception $e) {
            return [
                'test' => 'Basic Query Execution',
                'status' => 'FAIL',
                'message' => 'Query execution failed',
                'details' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    private function testTableExists($tableName) {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tableName]);

            if ($stmt->rowCount() > 0) {
                return [
                    'test' => "Table Existence: $tableName",
                    'status' => 'PASS',
                    'message' => "Table '$tableName' exists",
                    'details' => 'Table found in database'
                ];
            } else {
                return [
                    'test' => "Table Existence: $tableName",
                    'status' => 'FAIL',
                    'message' => "Table '$tableName' does not exist",
                    'details' => 'Required table is missing from database'
                ];
            }
        } catch (Exception $e) {
            return [
                'test' => "Table Existence: $tableName",
                'status' => 'FAIL',
                'message' => "Error checking table '$tableName'",
                'details' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    private function testUserTableStructure() {
        try {
            $stmt = $this->db->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $requiredColumns = ['id', 'email', 'password', 'credits', 'created_at'];
            $existingColumns = array_column($columns, 'Field');

            $missingColumns = array_diff($requiredColumns, $existingColumns);

            if (empty($missingColumns)) {
                return [
                    'test' => 'Users Table Structure',
                    'status' => 'PASS',
                    'message' => 'Users table has all required columns',
                    'details' => 'Found columns: ' . implode(', ', $existingColumns)
                ];
            } else {
                return [
                    'test' => 'Users Table Structure',
                    'status' => 'FAIL',
                    'message' => 'Users table missing required columns',
                    'details' => 'Missing: ' . implode(', ', $missingColumns)
                ];
            }
        } catch (Exception $e) {
            return [
                'test' => 'Users Table Structure',
                'status' => 'FAIL',
                'message' => 'Error checking users table structure',
                'details' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    private function testCreditTransactionsTableStructure() {
        try {
            $stmt = $this->db->query("DESCRIBE credit_transactions");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $requiredColumns = ['id', 'user_id', 'amount', 'type', 'description', 'created_at'];
            $existingColumns = array_column($columns, 'Field');

            $missingColumns = array_diff($requiredColumns, $existingColumns);

            if (empty($missingColumns)) {
                return [
                    'test' => 'Credit Transactions Table Structure',
                    'status' => 'PASS',
                    'message' => 'Credit transactions table has all required columns',
                    'details' => 'Found columns: ' . implode(', ', $existingColumns)
                ];
            } else {
                return [
                    'test' => 'Credit Transactions Table Structure',
                    'status' => 'FAIL',
                    'message' => 'Credit transactions table missing required columns',
                    'details' => 'Missing: ' . implode(', ', $missingColumns)
                ];
            }
        } catch (Exception $e) {
            return [
                'test' => 'Credit Transactions Table Structure',
                'status' => 'FAIL',
                'message' => 'Error checking credit transactions table structure',
                'details' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    private function testQueryPerformance() {
        try {
            $startTime = microtime(true);

            // Run a few test queries
            for ($i = 0; $i < 10; $i++) {
                $stmt = $this->db->query('SELECT 1');
                $stmt->fetch();
            }

            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            if ($executionTime < 100) { // Less than 100ms for 10 queries
                return [
                    'test' => 'Database Performance',
                    'status' => 'PASS',
                    'message' => 'Database queries executed within acceptable time',
                    'details' => sprintf('10 queries took %.2f ms (%.2f ms per query)', $executionTime, $executionTime / 10)
                ];
            } else {
                return [
                    'test' => 'Database Performance',
                    'status' => 'WARNING',
                    'message' => 'Database queries are slower than expected',
                    'details' => sprintf('10 queries took %.2f ms (%.2f ms per query)', $executionTime, $executionTime / 10)
                ];
            }
        } catch (Exception $e) {
            return [
                'test' => 'Database Performance',
                'status' => 'FAIL',
                'message' => 'Error testing database performance',
                'details' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

// Run tests if requested
if (isset($_GET['run'])) {
    header('Content-Type: application/json');

    $tester = new DatabaseTester();
    $results = $tester->runTests();

    echo json_encode(['tests' => $results]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Test Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test { margin: 10px 0; padding: 10px; border-radius: 4px; border-left: 4px solid; }
        .test.PASS { background: #d4edda; border-left-color: #28a745; }
        .test.FAIL { background: #f8d7da; border-left-color: #dc3545; }
        .test.WARNING { background: #fff3cd; border-left-color: #ffc107; }
        .details { font-size: 0.9em; color: #666; margin-top: 5px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Test Suite</h1>
        <p>Test database connectivity and structure</p>

        <button onclick="runTests()">Run Database Tests</button>

        <div id="results" style="margin-top: 20px;"></div>
    </div>

    <script>
        async function runTests() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Running tests...</p>';

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
                    case 'WARNING': icon = '⚠️'; break;
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