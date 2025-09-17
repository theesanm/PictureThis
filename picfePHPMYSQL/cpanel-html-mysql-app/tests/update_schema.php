<?php
/**
 * Schema Update Script
 * Applies agent schema changes to the database
 */

require_once __DIR__ . '/bootstrap.php';

// Set headers
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_update'])) {
    try {
        // Read the schema file
        $schemaPath = __DIR__ . '/../sql/agent_schema.sql';
        if (!file_exists($schemaPath)) {
            throw new Exception("Schema file not found: $schemaPath");
        }

        $schemaSQL = file_get_contents($schemaPath);
        if (empty($schemaSQL)) {
            throw new Exception("Schema file is empty");
        }

        // Split into individual statements
        $statements = array_filter(array_map('trim', explode(';', $schemaSQL)));

        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,  // Enable buffered queries
            ]
        );

        $executed = 0;
        $errors = [];

        foreach ($statements as $statement) {
            if (empty($statement)) continue;

            try {
                $pdo->exec($statement);
                $executed++;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                // Check if this is an acceptable error (duplicate column/index)
                $isAcceptableError = (
                    strpos($errorMessage, 'Duplicate column name') !== false ||
                    strpos($errorMessage, 'Duplicate key name') !== false ||
                    strpos($errorMessage, 'already exists') !== false ||
                    strpos($errorMessage, 'Multiple primary key') !== false
                );

                if ($isAcceptableError) {
                    $executed++; // Count as executed since it's expected
                    $errors[] = "Warning (acceptable): " . substr($statement, 0, 50) . "... - " . $errorMessage;
                } else {
                    $errors[] = "Error executing: " . substr($statement, 0, 50) . "... - " . $errorMessage;
                }
            }
        }

        if (empty($errors)) {
            $message = "Schema update completed successfully! $executed statements executed.";
            $success = true;
        } else {
            $message = "Schema update completed with errors. $executed statements executed. Errors: " . implode('<br>', $errors);
        }

    } catch (Exception $e) {
        $message = "Schema update failed: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schema Update - Interactive Prompt Enhancement Agent</title>
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
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b3d7ff;
        }
        .schema-preview {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        .schema-preview h4 {
            margin: 0 0 15px 0;
            color: #495057;
        }
        .schema-content {
            font-family: monospace;
            font-size: 0.9em;
            color: #6c757d;
            white-space: pre-wrap;
            background: white;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #28a745;
            color: white;
        }
        .btn-primary:hover {
            background: #218838;
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
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 Schema Update</h1>
            <p>Apply Agent Schema Changes</p>
        </div>
        <div class="content">

            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <div class="alert alert-info">
                    <strong>Ready to Update:</strong> This will apply the agent schema changes to your database.
                    Make sure you have a backup before proceeding.
                </div>

                <!-- Schema Preview -->
                <div class="schema-preview">
                    <h4>Schema Changes Preview</h4>
                    <div class="schema-content">
                        <?php
                        $schemaPath = __DIR__ . '/../sql/agent_schema.sql';
                        if (file_exists($schemaPath)) {
                            $content = file_get_contents($schemaPath);
                            echo htmlspecialchars(substr($content, 0, 1000));
                            if (strlen($content) > 1000) {
                                echo "\n\n... (truncated for preview)";
                            }
                        } else {
                            echo "Schema file not found: $schemaPath";
                        }
                        ?>
                    </div>
                </div>

                <!-- Update Form -->
                <form method="post">
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="confirm_update" name="confirm_update" required>
                            <label for="confirm_update">
                                I understand this will modify the database schema and have created a backup
                            </label>
                        </div>
                    </div>
                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Apply Schema Changes</button>
                        <a href="status.php" class="btn btn-secondary">Back to Status</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="actions">
                    <a href="web_runner.php" class="btn btn-primary">Run Tests</a>
                    <a href="status.php" class="btn btn-secondary">Back to Status</a>
                    <a href="../" class="btn btn-secondary">Back to App</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>