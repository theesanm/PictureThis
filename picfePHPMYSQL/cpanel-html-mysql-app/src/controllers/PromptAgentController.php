<?php
require_once __DIR__ . '/../utils/CSRF.php';

class PromptAgentController {
    private $openRouterUrl;
    private $debugLogFile;

    public function __construct() {
        $this->openRouterUrl = defined('OPENROUTER_API_URL') ? OPENROUTER_API_URL : 'https://openrouter.ai/api/v1/chat/completions';
        $this->debugLogFile = __DIR__ . '/../../debug.log';
        $this->debugLog('IS_PRODUCTION defined: ' . (defined('IS_PRODUCTION') ? 'YES' : 'NO'));
        if (defined('IS_PRODUCTION')) {
            $this->debugLog('IS_PRODUCTION value: ' . (IS_PRODUCTION ? 'TRUE' : 'FALSE'));
        }
    }

    private function debugLog($message) {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] [AGENT] ' . $message . PHP_EOL;
        $logFile = $this->debugLogFile;
        
        // Ensure the log file is writable
        if (!is_writable($logFile)) {
            error_log('AGENT DEBUG: Cannot write to log file: ' . $logFile);
            return;
        }
        
        $result = file_put_contents($logFile, $logMessage, FILE_APPEND);
        if ($result === false) {
            error_log('AGENT DEBUG: Failed to write to log file: ' . $logFile);
        }
        
        if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
            error_log('AGENT DEBUG: ' . $message);
        }
    }

    /**
     * Generate unique session ID
     */
    private function generateSessionId() {
        return 'agent_' . bin2hex(random_bytes(16));
    }

    /**
     * Start a new agent session
     */
    public function startSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Handle CORS preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            exit;
        }

        $this->debugLog('Start agent session request');

        if (empty($_SESSION['user'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        // Validate CSRF token
        if (!CSRF::validateRequest()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $originalPrompt = trim($input['prompt'] ?? '');

        if (empty($originalPrompt)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Original prompt is required'], 400);
        }

        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            $userId = $_SESSION['user']['id'];
            $sessionId = $this->generateSessionId();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $this->debugLog("Session expiry set to: $expiresAt (Unix: " . strtotime($expiresAt) . ")");

            // Get enhance prompt cost from settings
            $enhanceCost = 1; // Default
            $stmt = $pdo->prepare('SELECT enhanced_prompt_cost FROM settings LIMIT 1');
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result['enhanced_prompt_cost'])) {
                $enhanceCost = (int)$result['enhanced_prompt_cost'];
            }

            // Check if user has enough credits
            $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                $this->sendJsonResponse(['success' => false, 'message' => 'User not found'], 404);
            }
            
            $userCredits = $userData['credits'] ?? 0;

            if ($userCredits < $enhanceCost) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Insufficient credits. Agent session costs ' . $enhanceCost . ' credits.'], 400);
            }

            $this->debugLog("Starting agent session for user $userId with $userCredits credits, cost: $enhanceCost");

            // End any existing active session for this user
            $pdo->prepare('UPDATE prompt_agent_sessions SET session_status = ? WHERE user_id = ? AND session_status = ?')
                ->execute(['completed', $userId, 'active']);

            // Create new session WITHOUT charging credits yet
            $stmt = $pdo->prepare('
                INSERT INTO prompt_agent_sessions
                (id, user_id, original_prompt, expires_at, total_credits_used)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([$sessionId, $userId, $originalPrompt, $expiresAt, 0]); // Start with 0 credits used

            $this->debugLog("Agent session created: $sessionId, now making first LLM call");

            // Make the first LLM call
            try {
                // Generate agent response for the original prompt
                $agentResponse = $this->generateAgentResponse($originalPrompt, [], $originalPrompt);

                $this->debugLog("First LLM call successful for session: $sessionId");

                // Now deduct credits since LLM call was successful
                $pdo->prepare('UPDATE users SET credits = credits - ? WHERE id = ?')
                    ->execute([$enhanceCost, $userId]);

                $this->debugLog("Credits deducted successfully for agent session: $sessionId");

                // Record transaction
                $this->debugLog("About to record credit transaction for session: $sessionId");
                try {
                    $stmt = $pdo->prepare('INSERT INTO credit_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, ?, ?)');
                    $result = $stmt->execute([$userId, -$enhanceCost, 'usage', 'AI Prompt Enhancement Agent Session']);
                    $this->debugLog("Credit transaction execute result: " . ($result ? 'true' : 'false'));
                    $this->debugLog("Credit transaction recorded for agent session: $sessionId, cost: $enhanceCost credits");
                } catch (Exception $e) {
                    $this->debugLog("ERROR: Failed to record credit transaction for agent session: " . $e->getMessage());
                    $this->debugLog("ERROR details: " . $e->getTraceAsString());
                    // Continue execution even if transaction recording fails
                }

                // Update session with credits used
                $pdo->prepare('UPDATE prompt_agent_sessions SET total_credits_used = ? WHERE id = ?')
                    ->execute([$enhanceCost, $sessionId]);

                // Update session with new credit balance
                $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($updatedUser) {
                    $_SESSION['user']['credits'] = $updatedUser['credits'];
                    $this->debugLog('Session credits updated to ' . $updatedUser['credits'] . ' after agent session start');
                }

                // Store the first agent response
                $pdo->prepare('
                    INSERT INTO prompt_agent_messages
                    (session_id, message_type, content, suggested_prompts)
                    VALUES (?, ?, ?, ?)
                ')->execute([$sessionId, 'agent', $agentResponse['message'], $agentResponse['refined_prompt']]);

                // Update session stats
                $pdo->prepare('
                    UPDATE prompt_agent_sessions
                    SET total_llm_calls = total_llm_calls + 1,
                        updated_at = NOW()
                    WHERE id = ?
                ')->execute([$sessionId]);

                $this->debugLog("Created new agent session with first response: $sessionId for user: $userId");

                $this->sendJsonResponse([
                    'success' => true,
                    'data' => [
                        'sessionId' => $sessionId,
                        'expiresAt' => strtotime($expiresAt), // Convert to Unix timestamp
                        'message' => $agentResponse['message'],
                        'refinedPrompt' => $this->stripPromptTags($agentResponse['refined_prompt']),
                        'updatedCredits' => $updatedUser['credits'] ?? null
                    ]
                ]);

            } catch (Exception $e) {
                $this->debugLog("First LLM call failed for session: $sessionId, error: " . $e->getMessage());
                
                // Delete the session since LLM call failed
                $pdo->prepare('DELETE FROM prompt_agent_sessions WHERE id = ?')
                    ->execute([$sessionId]);
                
                $this->sendJsonResponse(['success' => false, 'message' => 'Failed to generate AI response. Please try again.'], 500);
            }

        } catch (Exception $e) {
            $this->debugLog('Failed to start agent session: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Failed to start agent session'], 500);
        }
    }

    /**
     * Send user message to agent and get response
     */
    public function sendMessage() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            exit;
        }

        $this->debugLog('Send agent message request');

        if (empty($_SESSION['user'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!CSRF::validateRequest()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = trim($input['sessionId'] ?? '');
        $userMessage = trim($input['message'] ?? '');

        if (empty($sessionId) || empty($userMessage)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Session ID and message are required'], 400);
        }

        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            $userId = $_SESSION['user']['id'];

            // Verify session ownership and status
            $stmt = $pdo->prepare('
                SELECT * FROM prompt_agent_sessions
                WHERE id = ? AND user_id = ? AND session_status = ?
            ');
            $stmt->execute([$sessionId, $userId, 'active']);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$session) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid or expired session'], 400);
            }

            // Check session expiration
            if (strtotime($session['expires_at']) < time()) {
                $pdo->prepare('UPDATE prompt_agent_sessions SET session_status = ? WHERE id = ?')
                    ->execute(['expired', $sessionId]);
                $this->sendJsonResponse(['success' => false, 'message' => 'Session expired'], 400);
            }

            // Check rate limits (message frequency only)
            $this->checkRateLimits($pdo, $userId, $sessionId);

            // Store user message
            $pdo->prepare('
                INSERT INTO prompt_agent_messages
                (session_id, message_type, content)
                VALUES (?, ?, ?)
            ')->execute([$sessionId, 'user', $userMessage]);

            // Get conversation history for context
            $history = $this->getConversationHistory($pdo, $sessionId);

            // Generate agent response
            $agentResponse = $this->generateAgentResponse($userMessage, $history, $session['original_prompt']);

            // Store agent response
            $pdo->prepare('
                INSERT INTO prompt_agent_messages
                (session_id, message_type, content, suggested_prompts)
                VALUES (?, ?, ?, ?)
            ')->execute([$sessionId, 'agent', $agentResponse['message'], $agentResponse['refined_prompt']]);

            // Update session stats (no credit charge per message, only per session)
            $pdo->prepare('
                UPDATE prompt_agent_sessions
                SET total_llm_calls = total_llm_calls + 1,
                    updated_at = NOW()
                WHERE id = ?
            ')->execute([$sessionId]);

            $this->debugLog("Agent response generated for session: $sessionId");

            $this->sendJsonResponse([
                'success' => true,
                'data' => [
                    'message' => $agentResponse['message'],
                    'refinedPrompt' => $this->stripPromptTags($agentResponse['refined_prompt']),
                    'creditsUsed' => 0 // No credits charged per message
                ]
            ]);

        } catch (Exception $e) {
            $this->debugLog('Failed to send agent message: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Failed to process message'], 500);
        }
    }

    /**
     * Get session status and conversation history
     */
    public function getSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            exit;
        }

        $this->debugLog('Get agent session request');

        if (empty($_SESSION['user'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $sessionId = $_GET['sessionId'] ?? '';

        if (empty($sessionId)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Session ID is required'], 400);
        }

        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            $userId = $_SESSION['user']['id'];

            // Get session info
            $stmt = $pdo->prepare('
                SELECT * FROM prompt_agent_sessions
                WHERE id = ? AND user_id = ?
            ');
            $stmt->execute([$sessionId, $userId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$session) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Session not found'], 404);
            }

            // Get conversation history
            $messages = $this->getConversationHistory($pdo, $sessionId);

            $this->sendJsonResponse([
                'success' => true,
                'data' => [
                    'session' => $session,
                    'messages' => $messages
                ]
            ]);

        } catch (Exception $e) {
            $this->debugLog('Failed to get agent session: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Failed to get session'], 500);
        }
    }

    /**
     * Close agent session when image is generated
     */
    public function closeSessionOnImageGeneration() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            exit;
        }

        $this->debugLog('Close agent session on image generation request');

        if (empty($_SESSION['user'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!CSRF::validateRequest()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = trim($input['sessionId'] ?? '');
        $finalPrompt = trim($input['finalPrompt'] ?? '');

        if (empty($sessionId)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Session ID is required'], 400);
        }

        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            $userId = $_SESSION['user']['id'];

            // Verify session ownership and status
            $stmt = $pdo->prepare('
                SELECT * FROM prompt_agent_sessions
                WHERE id = ? AND user_id = ? AND session_status = ?
            ');
            $stmt->execute([$sessionId, $userId, 'active']);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$session) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid or already closed session'], 400);
            }

            // Store the final prompt used for image generation
            if (!empty($finalPrompt)) {
                $pdo->prepare('
                    INSERT INTO prompt_agent_messages
                    (session_id, message_type, content)
                    VALUES (?, ?, ?)
                ')->execute([$sessionId, 'final_prompt', $finalPrompt]);
            }

            // Mark session as completed
            $pdo->prepare('
                UPDATE prompt_agent_sessions
                SET session_status = ?, completed_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ')->execute(['completed', $sessionId]);

            // Clear user's current session
            $pdo->prepare('UPDATE users SET current_agent_session_id = NULL WHERE id = ?')
                ->execute([$userId]);

            $this->debugLog("Closed agent session on image generation: $sessionId for user: $userId");

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Agent session closed successfully',
                'data' => [
                    'sessionId' => $sessionId,
                    'creditsUsed' => $session['total_credits_used']
                ]
            ]);

        } catch (Exception $e) {
            $this->debugLog('Failed to close agent session: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Failed to close session'], 500);
        }
    }

    /**
     * End agent session
     */
    public function endSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            exit;
        }

        $this->debugLog('End agent session request');

        if (empty($_SESSION['user'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        if (!CSRF::validateRequest()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = trim($input['sessionId'] ?? '');

        if (empty($sessionId)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Session ID is required'], 400);
        }

        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            $userId = $_SESSION['user']['id'];

            // Update session status
            $stmt = $pdo->prepare('
                UPDATE prompt_agent_sessions
                SET session_status = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ');
            $stmt->execute(['completed', $sessionId, $userId]);

            // Clear user's current session
            $pdo->prepare('UPDATE users SET current_agent_session_id = NULL WHERE id = ?')
                ->execute([$userId]);

            $this->debugLog("Ended agent session: $sessionId for user: $userId");

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Agent session ended successfully'
            ]);

        } catch (Exception $e) {
            $this->debugLog('Failed to end agent session: ' . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Failed to end session'], 500);
        }
    }

    /**
     * Check rate limits (message frequency only - daily limits removed)
     */
    private function checkRateLimits($pdo, $userId, $sessionId) {
        // Check message rate limit (10 seconds between messages)
        $stmt = $pdo->prepare('
            SELECT created_at FROM prompt_agent_messages
            WHERE session_id = ? AND message_type = ?
            ORDER BY created_at DESC LIMIT 1
        ');
        $stmt->execute([$sessionId, 'user']);
        $lastMessage = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastMessage) {
            $timeDiff = time() - strtotime($lastMessage['created_at']);
            if ($timeDiff < 10) {
                throw new Exception('Please wait ' . (10 - $timeDiff) . ' seconds before sending another message');
            }
        }

        // Daily credit limits removed - unlimited agent interactions allowed
    }

    /**
     * Get conversation history for context
     */
    private function getConversationHistory($pdo, $sessionId) {
        $stmt = $pdo->prepare('
            SELECT message_type, content, suggested_prompts, created_at
            FROM prompt_agent_messages
            WHERE session_id = ?
            ORDER BY created_at ASC
        ');
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate agent response using OpenRouter API (same as image generation)
     */
    private function generateAgentResponse($userMessage, $history, $originalPrompt) {
        $this->debugLog('generateAgentResponse method called with userMessage: ' . substr($userMessage, 0, 100));

        // Use OpenRouter API like the image generation does
        return $this->enhancePromptWithOpenRouter($userMessage, $history, $originalPrompt);
    }

    /**
     * Strip <PROMPT> tags from refined prompt
     */
    private function stripPromptTags($prompt) {
        // Remove <PROMPT> and </PROMPT> tags
        $prompt = preg_replace('/<\/?PROMPT>/i', '', $prompt);
        return trim($prompt);
    }

    /**
     * Send JSON response
     */
    private function sendJsonResponse($data, $statusCode = 200) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Enhance prompt using OpenRouter API (same approach as image generation)
     */
    private function enhancePromptWithOpenRouter($userMessage, $history, $originalPrompt) {
        $this->debugLog('enhancePromptWithOpenRouter method called with prompt: ' . substr($originalPrompt, 0, 50));

        $apiKey = defined('OPENROUTER_API_KEY_RUNTIME') ? OPENROUTER_API_KEY_RUNTIME :
                  (defined('OPENROUTER_API_KEY') ? OPENROUTER_API_KEY :
                  getenv('OPENROUTER_API_KEY'));
        $this->debugLog('API Key source: ' . 
                       (defined('OPENROUTER_API_KEY_RUNTIME') ? 'OPENROUTER_API_KEY_RUNTIME' :
                       (defined('OPENROUTER_API_KEY') ? 'OPENROUTER_API_KEY' : 'getenv')));
        $this->debugLog('API Key length: ' . strlen($apiKey ?? ''));
        if (!$apiKey) {
            throw new Exception('OpenRouter API key not configured');
        }

        // Build conversation context
        $context = "Original prompt: \"$originalPrompt\"\n\n";
        $context .= "Conversation history:\n";
        foreach (array_slice($history, -5) as $msg) { // Last 5 messages for context
            $context .= ($msg['message_type'] === 'user' ? 'User' : 'Agent') . ': ' . $msg['content'] . "\n";
        }
        $context .= "\nUser's latest message: $userMessage";

        $systemPrompt = 'You are an expert AI image generation prompt engineer and creative assistant. Your role is to help users create the perfect prompt for their vision through interactive conversation.

IMPORTANT: You must ALWAYS return your response in this exact JSON format:
{
  "message": "Your conversational response to help guide the user",
  "refined_prompt": "The single, fully refined and optimized prompt wrapped in <PROMPT> tags like this: <PROMPT>Your complete optimized prompt here</PROMPT>"
}

Guidelines for your refined_prompt:
- Make it comprehensive and detailed for AI image generation
- Include all relevant technical details (lighting, style, composition, etc.)
- Keep it under 200 words but make every word count
- Focus on creating ONE excellent prompt, not multiple options
- Use professional photography/art terminology when appropriate

Your conversational style:
- Be friendly and encouraging
- Ask specific questions to understand their vision better
- Explain your suggestions and why they improve the prompt
- Guide them step by step toward their perfect image
- Be patient and iterative - they can refine multiple times

Remember: Return ONLY the JSON format above. No additional text or explanations outside the JSON structure.';

        $this->debugLog('OPENROUTER_MODEL_RUNTIME defined: ' . (defined('OPENROUTER_MODEL_RUNTIME') ? 'YES' : 'NO'));
        if (defined('OPENROUTER_MODEL_RUNTIME')) {
            $this->debugLog('OPENROUTER_MODEL_RUNTIME value: ' . OPENROUTER_MODEL_RUNTIME);
        }
        $this->debugLog('getenv(OPENROUTER_MODEL): ' . (getenv('OPENROUTER_MODEL') ?: 'NOT_SET'));

        $requestBody = [
            "model" => defined('OPENROUTER_MODEL_RUNTIME') ? OPENROUTER_MODEL_RUNTIME :
                      (getenv('OPENROUTER_MODEL') ?: "openai/gpt-oss-20b:free"),
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => $context
                ]
            ],
            "max_tokens" => 1000,
            "temperature" => 0.7
        ];

        $this->debugLog('OpenRouter Prompt Enhancement Request: ' . json_encode($requestBody));

        // Make API request (same approach as image generation)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->openRouterUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . (defined('OPENROUTER_APP_URL_RUNTIME') ? OPENROUTER_APP_URL_RUNTIME :
                               (getenv('OPENROUTER_APP_URL') ?: 'http://localhost:8000')),
            'X-Title: PictureThis Agent'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->debugLog('OpenRouter Prompt Enhancement Response - HTTP Code: ' . $httpCode .
                       ', Content-Type: ' . $contentType . ', Response Length: ' . strlen($response));

        if ($error) {
            $this->debugLog('OpenRouter API Error: ' . $error);
            throw new Exception('API request failed: ' . $error);
        }

        if ($httpCode !== 200) {
            $this->debugLog('OpenRouter API HTTP Error: ' . $httpCode . ' | Response: ' . substr($response, 0, 500));
            throw new Exception('API request failed with status: ' . $httpCode);
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['choices'][0]['message']['content'])) {
            $this->debugLog('Invalid API response structure');
            throw new Exception('Invalid API response structure');
        }

        $content = $data['choices'][0]['message']['content'];
        $this->debugLog('Raw LLM content: ' . substr($content, 0, 300));

        // Handle LLM responses wrapped in markdown code blocks
        if (preg_match('/^```json\s*\n(.*)\n```$/s', $content, $matches)) {
            $content = $matches[1];
            $this->debugLog('Extracted JSON from markdown code block');
        }

        // Parse JSON response
        $result = json_decode($content, true);
        if (!$result || !isset($result['message']) || !isset($result['refined_prompt'])) {
            $this->debugLog('JSON parsing failed. Raw content length: ' . strlen($content));
            $this->debugLog('JSON parsing failed. Raw content (first 500 chars): ' . substr($content, 0, 500));

            // Try to extract partial JSON if response was truncated
            if (preg_match('/"message"\s*:\s*"([^"]*(?:\\.[^"]*)*)/', $content, $msgMatch)) {
                $message = $msgMatch[1];
                // Try to extract refined_prompt as well
                if (preg_match('/"refined_prompt"\s*:\s*"([^"]*(?:\\.[^"]*)*)/', $content, $promptMatch)) {
                    $refinedPrompt = $promptMatch[1];
                    $this->debugLog('Extracted partial response - message and refined_prompt found');
                    return [
                        'message' => $message,
                        'refined_prompt' => $refinedPrompt
                    ];
                } else {
                    $this->debugLog('Extracted partial response - only message found, using fallback prompt');
                    return [
                        'message' => $message,
                        'refined_prompt' => '<PROMPT>' . $originalPrompt . ' with enhanced details</PROMPT>'
                    ];
                }
            }

            // Fallback response if JSON parsing fails completely
            $this->debugLog('Using complete fallback response');
            return [
                'message' => 'I understand you want to improve your prompt. Let me help you refine it. What specific aspects would you like to focus on?',
                'refined_prompt' => '<PROMPT>' . $originalPrompt . ' with enhanced details and professional styling</PROMPT>'
            ];
        }

        $this->debugLog('Successfully parsed OpenRouter response');
        return $result;
    }
}