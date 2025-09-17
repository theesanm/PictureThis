<?php
/**
 * Email/SMTP Test Suite
 * Tests email configuration and sending capabilities
 */

require_once '../config/config.php';

class EmailTester {
    public function runTests() {
        $results = [];

        // Configuration tests
        $results[] = $this->testSMTPConfiguration();
        $results[] = $this->testPHPMailFunction();

        // If SMTP is configured, test connection
        $smtpHost = getConfigValue('email', 'smtp_host');
        if ($smtpHost) {
            $results[] = $this->testSMTPConnection();
        }

        // Test email sending (if safe to do so)
        if ($this->isTestEmailSafe()) {
            $results[] = $this->testEmailSending();
        } else {
            $results[] = [
                'test' => 'Email Sending Test',
                'status' => 'INFO',
                'message' => 'Email sending test skipped (not in test environment)',
                'details' => 'Configure test email settings to enable sending tests'
            ];
        }

        return $results;
    }

    private function testSMTPConfiguration() {
        $smtpConfig = [
            'host' => getConfigValue('email', 'smtp_host'),
            'username' => getConfigValue('email', 'smtp_username'),
            'password' => getConfigValue('email', 'smtp_password'),
            'port' => getConfigValue('email', 'smtp_port'),
            'from_email' => getConfigValue('email', 'from_email')
        ];

        $missing = [];
        foreach ($smtpConfig as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
            }
        }

        if (empty($missing)) {
            return [
                'test' => 'SMTP Configuration',
                'status' => 'PASS',
                'message' => 'All SMTP settings are configured',
                'details' => 'Host: ' . $smtpConfig['host'] . ', Port: ' . $smtpConfig['port']
            ];
        } else {
            return [
                'test' => 'SMTP Configuration',
                'status' => 'FAIL',
                'message' => 'SMTP configuration incomplete',
                'details' => 'Missing: ' . implode(', ', $missing)
            ];
        }
    }

    private function testPHPMailFunction() {
        if (function_exists('mail')) {
            return [
                'test' => 'PHP mail() Function',
                'status' => 'PASS',
                'message' => 'PHP mail() function is available',
                'details' => 'Function can be used as fallback for email sending'
            ];
        } else {
            return [
                'test' => 'PHP mail() Function',
                'status' => 'WARNING',
                'message' => 'PHP mail() function is not available',
                'details' => 'SMTP will be required for all email functionality'
            ];
        }
    }

    private function testSMTPConnection() {
        $host = getConfigValue('email', 'smtp_host');
        $port = getConfigValue('email', 'smtp_port') ?: 587;

        if (!$host) {
            return [
                'test' => 'SMTP Connection',
                'status' => 'FAIL',
                'message' => 'SMTP host not configured',
                'details' => 'Cannot test connection without host'
            ];
        }

        $connection = @fsockopen($host, $port, $errno, $errstr, 10);

        if ($connection) {
            fclose($connection);
            return [
                'test' => 'SMTP Connection',
                'status' => 'PASS',
                'message' => 'Successfully connected to SMTP server',
                'details' => "Host: $host, Port: $port"
            ];
        } else {
            return [
                'test' => 'SMTP Connection',
                'status' => 'FAIL',
                'message' => 'Failed to connect to SMTP server',
                'details' => "Host: $host, Port: $port, Error: $errstr ($errno)"
            ];
        }
    }

    private function isTestEmailSafe() {
        // Only allow test emails in development mode
        return !defined('IS_PRODUCTION') || !IS_PRODUCTION;
    }

    private function testEmailSending() {
        $to = getConfigValue('email', 'from_email'); // Send to ourselves for testing
        $subject = 'PictureThis Email Test';
        $message = "This is a test email from PictureThis.\n\nSent at: " . date('Y-m-d H:i:s');
        $headers = 'From: ' . getConfigValue('email', 'from_email') . "\r\n" .
                   'Reply-To: ' . getConfigValue('email', 'from_email') . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        if (empty($to)) {
            return [
                'test' => 'Email Sending Test',
                'status' => 'FAIL',
                'message' => 'No recipient email configured for testing',
                'details' => 'Configure from_email in settings to enable test'
            ];
        }

        // Try to send email
        $result = mail($to, $subject, $message, $headers);

        if ($result) {
            return [
                'test' => 'Email Sending Test',
                'status' => 'PASS',
                'message' => 'Test email sent successfully',
                'details' => 'Email sent to: ' . $to
            ];
        } else {
            return [
                'test' => 'Email Sending Test',
                'status' => 'FAIL',
                'message' => 'Failed to send test email',
                'details' => 'Check SMTP configuration and server logs'
            ];
        }
    }
}

// Run tests if requested
if (isset($_GET['run'])) {
    header('Content-Type: application/json');

    $tester = new EmailTester();
    $results = $tester->runTests();

    echo json_encode(['tests' => $results]);
    exit;
}

// Handle email test sending
if (isset($_POST['send_test_email'])) {
    header('Content-Type: application/json');

    $to = $_POST['test_email'] ?? '';
    $subject = 'PictureThis Manual Email Test';
    $message = "This is a manual test email from PictureThis.\n\nSent at: " . date('Y-m-d H:i:s');
    $headers = 'From: ' . getConfigValue('email', 'from_email') . "\r\n" .
               'Reply-To: ' . getConfigValue('email', 'from_email') . "\r\n" .
               'X-Mailer: PHP/' . phpversion();

    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    $result = mail($to, $subject, $message, $headers);

    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Test email sent successfully' : 'Failed to send test email'
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Test Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test { margin: 10px 0; padding: 10px; border-radius: 4px; border-left: 4px solid; }
        .test.PASS { background: #d4edda; border-left-color: #28a745; }
        .test.FAIL { background: #f8d7da; border-left-color: #dc3545; }
        .test.WARNING { background: #fff3cd; border-left-color: #ffc107; }
        .test.INFO { background: #d1ecf1; border-left-color: #17a2b8; }
        .details { font-size: 0.9em; color: #666; margin-top: 5px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #005a87; }
        input[type="email"] { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 250px; }
        .email-test { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Test Suite</h1>
        <p>Test email/SMTP configuration and functionality</p>

        <button onclick="runTests()">Run Email Tests</button>

        <div class="email-test">
            <h3>Manual Email Test</h3>
            <p>Send a test email to verify SMTP configuration:</p>
            <input type="email" id="test-email" placeholder="Enter test email address" value="<?php echo htmlspecialchars(getConfigValue('email', 'from_email')); ?>">
            <button onclick="sendTestEmail()">Send Test Email</button>
            <div id="email-result" style="margin-top: 10px;"></div>
        </div>

        <div id="results" style="margin-top: 20px;"></div>
    </div>

    <script>
        async function runTests() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Running email tests...</p>';

            try {
                const response = await fetch('?run=1');
                const data = await response.json();

                displayResults(data.tests);
            } catch (error) {
                resultsDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }

        async function sendTestEmail() {
            const emailInput = document.getElementById('test-email');
            const resultDiv = document.getElementById('email-result');
            const email = emailInput.value.trim();

            if (!email) {
                resultDiv.innerHTML = '<span style="color: red;">Please enter an email address</span>';
                return;
            }

            resultDiv.innerHTML = 'Sending test email...';

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'send_test_email=1&test_email=' + encodeURIComponent(email)
                });

                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = '<span style="color: green;">' + data.message + '</span>';
                } else {
                    resultDiv.innerHTML = '<span style="color: red;">' + data.message + '</span>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<span style="color: red;">Error: ' + error.message + '</span>';
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
                    case 'INFO': icon = 'ℹ️'; break;
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