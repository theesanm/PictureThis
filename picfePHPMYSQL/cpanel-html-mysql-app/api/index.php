<?php
/**
 * API Router
 * Routes API requests to appropriate controllers
 */

// Load configuration first
require_once __DIR__ . '/../config/config.php';

// Get the API path
$requestUri = $_SERVER['REQUEST_URI'];
$apiPath = str_replace('/api/', '', parse_url($requestUri, PHP_URL_PATH));

try {
    // Route API requests
    if ($apiPath === 'prompt-agent/start') {
        require_once __DIR__ . '/../src/controllers/PromptAgentController.php';
        $controller = new PromptAgentController();
        $controller->startSession();
    }
    elseif ($apiPath === 'prompt-agent/continue') {
        require_once __DIR__ . '/../src/controllers/PromptAgentController.php';
        $controller = new PromptAgentController();
        $controller->continueSession();
    }
    elseif ($apiPath === 'prompt-agent/end') {
        require_once __DIR__ . '/../src/controllers/PromptAgentController.php';
        $controller = new PromptAgentController();
        $controller->endSession();
    }
    else {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found', 'path' => $apiPath]);
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => 'Fatal error',
        'message' => $e->getMessage()
    ]);
}
?>