<?php
/**
 * Test Environment Status Page
 * Shows the current state of the testing environment
 */

require_once __DIR__ . '/bootstrap.php';

// Set headers
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Environment Status - Interactive Prompt Enhancement Agent</title>
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
            max-width: 800px;
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
            font-size: 2em;
            font-weight: 300;
        }
        .content {
            padding: 30px;
        }
        .status-section {
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }
        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-title {
            margin: 0;
            font-size: 1.2em;
            color: #495057;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-success { background: #d4edda; color: #155724; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-info { background: #cce5ff; color: #004085; }
        .section-content {
            padding: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            display: block;
            margin-bottom: 5px;
        }
        .info-value {
            color: #6c757d;
            font-family: monospace;
            font-size: 0.9em;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px 10px 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
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
        .test-files {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        .test-files h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .file-item {
            background: white;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            font-family: monospace;
            font-size: 0.9em;
        }
        .file-found { color: #28a745; }
        .file-missing { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test Environment Status</h1>
            <p>Interactive Prompt Enhancement Agent</p>
        </div>
        <div class="content">

            <!-- PHP Environment -->
            <div class="status-section">
                <div class="section-header">
                    <h2 class="section-title">PHP Environment</h2>
                    <span class="status-badge status-success">Active</span>
                </div>
                <div class="section-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">PHP Version</span>
                            <span class="info-value"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Server Software</span>
                            <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Operating System</span>
                            <span class="info-value"><?php echo PHP_OS; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Memory Limit</span>
                            <span class="info-value"><?php echo ini_get('memory_limit'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Connection -->
            <div class="status-section">
                <div class="section-header">
                    <h2 class="section-title">Database Connection</h2>
                    <?php
                    $dbStatus = 'error';
                    $dbMessage = 'Not Connected';
                    try {
                        $pdo = new PDO(
                            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                            DB_USER,
                            DB_PASS,
                            [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES => false,
                            ]
                        );
                        $dbStatus = 'success';
                        $dbMessage = 'Connected';
                    } catch (Exception $e) {
                        $dbMessage = 'Connection Failed';
                    }
                    ?>
                    <span class="status-badge status-<?php echo $dbStatus; ?>"><?php echo $dbMessage; ?></span>
                </div>
                <div class="section-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Host</span>
                            <span class="info-value"><?php echo DB_HOST; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Database</span>
                            <span class="info-value"><?php echo DB_NAME; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">User</span>
                            <span class="info-value"><?php echo DB_USER; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">PDO Available</span>
                            <span class="info-value"><?php echo extension_loaded('pdo_mysql') ? 'Yes' : 'No'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Files Status -->
            <div class="status-section">
                <div class="section-header">
                    <h2 class="section-title">Test Files</h2>
                    <span class="status-badge status-info">Checking...</span>
                </div>
                <div class="section-content">
                    <div class="test-files">
                        <h4>Test File Status</h4>
                        <div class="file-list">
                            <?php
                            $testFiles = [
                                'bootstrap.php',
                                'web_runner.php',
                                'run_tests.php',
                                'unit/PromptAgentControllerTest.php',
                                'database/SchemaTest.php',
                                'api/AgentApiTest.php',
                                'utils/TestHelper.php',
                                'utils/DatabaseTestHelper.php'
                            ];

                            foreach ($testFiles as $file) {
                                $filePath = __DIR__ . '/' . $file;
                                $exists = file_exists($filePath);
                                $class = $exists ? 'file-found' : 'file-missing';
                                $icon = $exists ? '✅' : '❌';
                                echo "<div class='file-item $class'>$icon $file</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions">
                <a href="web_runner.php" class="btn btn-primary">Run Tests</a>
                <a href="update_schema.php" class="btn btn-secondary">Update Schema</a>
                <a href="../" class="btn btn-secondary">Back to App</a>
            </div>

        </div>
    </div>
</body>
</html>