<?php
/**
 * Test Suite Index
 * Provides access to all test tools and utilities
 */

// Set headers
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Suite - Interactive Prompt Enhancement Agent</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
            text-align: center;
        }
        .container {
            max-width: 600px;
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
        }
        .header h1 {
            margin: 0;
            font-size: 2em;
            font-weight: 300;
        }
        .content {
            padding: 30px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
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
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        .btn-info:hover {
            background: #138496;
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
        .description {
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test Suite</h1>
            <p>Interactive Prompt Enhancement Agent</p>
        </div>
        <div class="content">
            <p class="description">
                Welcome to the comprehensive test suite for PictureThis.
                Choose an option below to get started.
            </p>

            <div style="text-align: left; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                <h3 style="margin-top: 0; color: #007cba;">🚀 Quick Deployment Checklist</h3>
                <ol style="margin-bottom: 0;">
                    <li>Run <a href="../web_install.php?install=confirm" style="color: #007cba;">Web Installer</a> to set up the application</li>
                    <li>Run <a href="diagnostics.php" style="color: #007cba;">Full Diagnostics</a> to verify everything works</li>
                    <li>Access your <a href="../" style="color: #007cba;">live application</a></li>
                </ol>
            </div>

            <h3 style="text-align: left; color: #333;">🔧 Diagnostic Tools</h3>
            <a href="diagnostics.php" class="btn btn-primary" style="width: 200px;">Full System Diagnostics</a>
            <a href="database.php" class="btn btn-info" style="width: 200px;">Database Tests</a>
            <a href="email.php" class="btn btn-success" style="width: 200px;">Email Tests</a>
            <a href="api.php" class="btn btn-secondary" style="width: 200px;">API Tests</a>

            <h3 style="text-align: left; color: #333; margin-top: 30px;">⚙️ Legacy Tools</h3>
            <a href="web_runner.php" class="btn btn-primary">Run All Tests</a>
            <a href="status.php" class="btn btn-info">Environment Status</a>
            <a href="update_schema.php" class="btn btn-success">Update Schema</a>

            <div style="margin-top: 30px;">
                <a href="../" class="btn btn-secondary">Back to App</a>
                <a href="README.md" class="btn btn-secondary" style="margin-left: 10px;">Documentation</a>
            </div>
        </div>
    </div>
</body>
</html>