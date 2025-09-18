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
    elseif ($apiPath === 'prompt-agent/message') {
        require_once __DIR__ . '/../src/controllers/PromptAgentController.php';
        $controller = new PromptAgentController();
        $controller->sendMessage();
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
    // Add mapping for GenerateController endpoints so API calls like /api/generate and /api/enhance work
    elseif ($apiPath === 'generate') {
        require_once __DIR__ . '/../src/controllers/GenerateController.php';
        $controller = new GenerateController();
        $controller->generate();
    }
    elseif ($apiPath === 'enhance') {
        require_once __DIR__ . '/../src/controllers/GenerateController.php';
        $controller = new GenerateController();
        $controller->enhance();
    }
    elseif ($apiPath === 'user/credits' || $apiPath === 'user/credits/get') {
        require_once __DIR__ . '/../src/controllers/GenerateController.php';
        $controller = new GenerateController();
        $controller->getUserCredits();
    }
    // Credits / payments endpoints
    elseif ($apiPath === 'credits/initiate' || $apiPath === 'credits/initiate/') {
        require_once __DIR__ . '/../src/controllers/PricingController.php';
        $controller = new PricingController();
        $controller->initiate();
    }
    elseif ($apiPath === 'credits/payfast/notify' || $apiPath === 'credits/payfast/notify/') {
        require_once __DIR__ . '/../src/controllers/PricingController.php';
        $controller = new PricingController();
        $controller->notify();
    }
    elseif ($apiPath === 'payments/status' || $apiPath === 'payments/status/') {
        require_once __DIR__ . '/../src/controllers/PricingController.php';
        $controller = new PricingController();
        $controller->paymentStatus();
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