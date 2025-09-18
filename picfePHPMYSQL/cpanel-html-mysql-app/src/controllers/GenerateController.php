<?php
require_once __DIR__ . '/../utils/CSRF.php';

class GenerateController {
    private $openRouterUrl;
    private $uploadsDir;
    private $debugLogFile;

    public function __construct() {
        $this->openRouterUrl = defined('OPENROUTER_API_URL') ? OPENROUTER_API_URL : 'https://openrouter.ai/api/v1/chat/completions';
        $this->uploadsDir = __DIR__ . '/../../uploads/';
        $this->debugLogFile = __DIR__ . '/../../debug.log';
        
        // Ensure uploads directory exists
        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
        
        // Ensure debug.log exists and is writable
        $this->ensureDebugLogExists();
    }

    private function ensureDebugLogExists() {
        $logDir = dirname($this->debugLogFile);
        
        // Ensure the directory exists and is writable
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                error_log('DEBUG: Cannot create log directory: ' . $logDir);
                return;
            }
        }
        
        // Ensure the log file exists
        if (!file_exists($this->debugLogFile)) {
            if (file_put_contents($this->debugLogFile, '') === false) {
                error_log('DEBUG: Cannot create log file: ' . $this->debugLogFile);
                return;
            }
        }
        
        // Ensure the log file is writable
        if (!is_writable($this->debugLogFile)) {
            error_log('DEBUG: Log file is not writable: ' . $this->debugLogFile);
        }
    }

    private function debugLog($message) {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        
        // Try to write to the log file
        $result = @file_put_contents($this->debugLogFile, $logMessage, FILE_APPEND);
        if ($result === false) {
            // If file writing fails, only log to PHP error log (avoid spam)
            static $loggedOnce = false;
            if (!$loggedOnce) {
                error_log('DEBUG: Cannot write to log file: ' . $this->debugLogFile . ' - logging disabled');
                $loggedOnce = true;
            }
            return;
        }
        
        // Only log to PHP error_log in development
        if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
            error_log('DEBUG: ' . $message);
        }
    }

    /**
     * Validate uploaded images for security
     * Checks file type, size, and content
     */
    private function validateUploadedImages($uploadedImages) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB

        foreach ($uploadedImages as $index => $image) {
            // Check for upload errors
            if ($image['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Upload error for image ' . ($index + 1) . ': ' . $this->getUploadErrorMessage($image['error']));
            }

            // Check file size
            if ($image['size'] > $maxFileSize) {
                throw new Exception('Image ' . ($index + 1) . ' is too large. Maximum size is 10MB.');
            }

            // Check MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $image['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimeTypes)) {
                throw new Exception('Image ' . ($index + 1) . ' has invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
            }

            // Verify MIME type matches file extension
            $extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $expectedExtensions = [
                'image/jpeg' => ['jpg', 'jpeg'],
                'image/png' => ['png'],
                'image/gif' => ['gif'],
                'image/webp' => ['webp']
            ];

            if (!isset($expectedExtensions[$mimeType]) || !in_array($extension, $expectedExtensions[$mimeType])) {
                throw new Exception('Image ' . ($index + 1) . ' file extension does not match its content type.');
            }

            // Validate image content using getimagesize
            $imageInfo = getimagesize($image['tmp_name']);
            if ($imageInfo === false) {
                throw new Exception('Image ' . ($index + 1) . ' is not a valid image file.');
            }

            // Additional check: ensure image dimensions are reasonable
            if ($imageInfo[0] > 4096 || $imageInfo[1] > 4096) {
                throw new Exception('Image ' . ($index + 1) . ' dimensions are too large. Maximum 4096x4096 pixels.');
            }

            $this->debugLog('Image ' . ($index + 1) . ' validation passed: ' . $mimeType . ', ' . $image['size'] . ' bytes, ' . $imageInfo[0] . 'x' . $imageInfo[1]);
        }
    }

    /**
     * Get human-readable upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Mark old images as soft deleted based on retention policy
     * Uses has_usage_permission = -1 to indicate soft deletion
     */
    private function markOldImagesAsDeleted($pdo, $userId) {
        $retentionDays = getenv('IMAGE_RETENTION_DAYS') ?: 7;
        $minImages = getenv('MIN_IMAGES_PER_USER') ?: 3;

        try {
            // Count current non-deleted images for this user
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM images WHERE user_id = ? AND (has_usage_permission IS NULL OR has_usage_permission != -1)');
            $stmt->execute([$userId]);
            $currentCount = $stmt->fetch()['count'];

            // If user has minimum images or fewer, don't delete any
            if ($currentCount <= $minImages) {
                return;
            }

            // Mark images older than retention period as soft deleted
            // But keep enough to maintain minimum count
            $imagesToKeep = max($minImages, $currentCount - 10); // Keep minimum + some buffer

            $stmt = $pdo->prepare("
                UPDATE images
                SET has_usage_permission = -1
                WHERE user_id = ?
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                AND (has_usage_permission IS NULL OR has_usage_permission != -1)
                ORDER BY created_at ASC
                LIMIT ?
            ");
            $stmt->execute([$userId, $retentionDays, $currentCount - $imagesToKeep]);

            $affectedRows = $stmt->rowCount();
            if ($affectedRows > 0) {
                $this->debugLog("Marked $affectedRows old images as soft deleted for user $userId");
            }
        } catch (Exception $e) {
            $this->debugLog('Error marking old images as deleted: ' . $e->getMessage());
        }
    }

    /**
     * Get recent images excluding soft deleted ones
     */
    private function getRecentImages($pdo, $userId, $limit = 5) {
        try {
            // First, mark old images as deleted if needed
            $this->markOldImagesAsDeleted($pdo, $userId);

            // Then get recent non-deleted images
            $stmt = $pdo->prepare('
                SELECT id, prompt, image_url, created_at
                FROM images
                WHERE user_id = ?
                AND (has_usage_permission IS NULL OR has_usage_permission != -1)
                ORDER BY created_at DESC
                LIMIT ?
            ');
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->debugLog('Error fetching recent images: ' . $e->getMessage());
            return [];
        }
    }

    public function index() {
        // Ensure session and auth
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
        $settings = [];
        // detect schema shape
        $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN,0);
        if (in_array('k',$cols)) {
            $rows = $pdo->query('SELECT k,v FROM settings')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $settings[$r['k']] = $r['v'];
        } else {
            $row = $pdo->query('SELECT * FROM settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $settings['credit_cost_per_image'] = $row['credit_cost_per_image'] ?? null;
                $settings['enhance_prompt_cost'] = $row['enhanced_prompt_cost'] ?? null;
                $settings['enable_enhance'] = $row['enhanced_prompt_enabled'] ?? null;
                $settings['ai_provider'] = $row['ai_provider'] ?? null;
            }
        }
        
        // Ensure we have default values
        $settings = array_merge([
            'enable_enhance' => true,
            'enhance_prompt_cost' => 1,
            'credit_cost_per_image' => 10,
            'ai_provider' => 'openrouter'
        ], $settings);

        // Get user's recent images (excluding soft deleted ones)
        $recentImages = [];
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            $userId = (int)$_SESSION['user']['id'];
            $recentImages = $this->getRecentImages($pdo, $userId, 5);
        }

        // Get current user info
        $user = null;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['user'])) {
            $user = $_SESSION['user'];
        }

        // Extract variables for view
        extract([
            'settings' => $settings,
            'user' => $user,
            'recentImages' => $recentImages
        ]);

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/generate.php';
        include __DIR__ . '/../views/footer.php';
    }

    // Handle image generation
    public function generate() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['user'])) {
            $_SESSION['generate_error'] = 'Authentication required';
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /generate');
            exit;
        }

        // Validate CSRF token
        if (!CSRF::validateRequest()) {
            $_SESSION['generate_error'] = 'Invalid request. Please try again.';
            header('Location: /generate');
            exit;
        }

        // Detect AJAX / JSON requests so we can return JSON like /api/enhance
        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        // helper to respond according to request type
        $respondJsonOrRedirect = function($success, $payload) use ($isAjax) {
            if ($isAjax) {
                header('Content-Type: application/json');
                if ($success) {
                    echo json_encode(array_merge(['success' => true], $payload));
                } else {
                    echo json_encode(['success' => false, 'message' => (is_string($payload) ? $payload : ($payload['message'] ?? 'Error'))]);
                }
                exit;
            } else {
                if ($success) {
                    // payload may contain generated_image and message
                    if (is_array($payload) && isset($payload['generated_image'])) {
                        $_SESSION['generated_image'] = $payload['generated_image'];
                    }
                    if (is_array($payload) && isset($payload['message'])) {
                        $_SESSION['generate_success'] = $payload['message'];
                    }
                } else {
                    // payload may be message string
                    $_SESSION['generate_error'] = is_string($payload) ? $payload : ($payload['message'] ?? 'Failed to generate image');
                }
                header('Location: /generate');
                exit;
            }
        };

        $this->debugLog('=== GENERATE REQUEST START ===');
        $this->debugLog('POST data received: ' . json_encode($_POST));
        $this->debugLog('FILES data received: ' . json_encode(array_map(function($file) {
            return [
                'name' => $file['name'] ?? 'none',
                'type' => $file['type'] ?? 'none',
                'size' => $file['size'] ?? 0,
                'error' => $file['error'] ?? -1,
                'tmp_name' => !empty($file['tmp_name']) ? 'present' : 'empty'
            ];
        }, $_FILES)));

        // If request is JSON (AJAX), parse JSON body to get prompt
        $prompt = '';
        if ($isAjax && !empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $inputJson = json_decode(file_get_contents('php://input'), true);
            $prompt = trim($inputJson['prompt'] ?? '');
        } else {
            $prompt = trim($_POST['prompt'] ?? '');
        }
        $userId = $_SESSION['user']['id'];

        // Validate prompt
        if (empty($prompt)) {
            $respondJsonOrRedirect(false, 'Please enter a prompt');
        }

        // Check user credits
        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $respondJsonOrRedirect(false, 'User not found');
        }

        // Get credit cost from settings
        $settings = [];
        $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN,0);
        if (in_array('k',$cols)) {
            $rows = $pdo->query('SELECT k,v FROM settings')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $settings[$r['k']] = $r['v'];
        } else {
            $row = $pdo->query('SELECT * FROM settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $settings['credit_cost_per_image'] = $row['credit_cost_per_image'] ?? 10;
            }
        }
        
        $creditCost = (int)($settings['credit_cost_per_image'] ?? 10);
        
        if ($user['credits'] < $creditCost) {
            $respondJsonOrRedirect(false, "Insufficient credits. You need {$creditCost} credits.");
        }

        // Handle uploaded images
        $uploadedImages = [];
        if (!empty($_FILES['image1']['tmp_name'])) {
            $this->debugLog('Image1 detected: ' . $_FILES['image1']['name'] . ' (' . $_FILES['image1']['size'] . ' bytes)');
            $this->debugLog('Image1 upload details: type=' . $_FILES['image1']['type'] . ', error=' . $_FILES['image1']['error'] . ', tmp_name=' . $_FILES['image1']['tmp_name']);
            $uploadedImages[] = $_FILES['image1'];
        } else {
            $this->debugLog('No image1 detected - tmp_name is empty');
            if (isset($_FILES['image1']['error']) && $_FILES['image1']['error'] !== UPLOAD_ERR_NO_FILE) {
                $this->debugLog('Image1 upload error: ' . $this->getUploadErrorMessage($_FILES['image1']['error']));
            }
        }
        if (!empty($_FILES['image2']['tmp_name'])) {
            $this->debugLog('Image2 detected: ' . $_FILES['image2']['name'] . ' (' . $_FILES['image2']['size'] . ' bytes)');
            $this->debugLog('Image2 upload details: type=' . $_FILES['image2']['type'] . ', error=' . $_FILES['image2']['error'] . ', tmp_name=' . $_FILES['image2']['tmp_name']);
            $uploadedImages[] = $_FILES['image2'];
        } else {
            $this->debugLog('No image2 detected - tmp_name is empty');
            if (isset($_FILES['image2']['error']) && $_FILES['image2']['error'] !== UPLOAD_ERR_NO_FILE) {
                $this->debugLog('Image2 upload error: ' . $this->getUploadErrorMessage($_FILES['image2']['error']));
            }
        }

        $this->debugLog('Total uploaded images detected: ' . count($uploadedImages));

        // Validate uploaded images for security
        if (!empty($uploadedImages)) {
            $this->validateUploadedImages($uploadedImages);
        }

        $this->debugLog('Image generation request for user ' . $userId . ' with prompt: ' . substr($prompt, 0, 100) . (strlen($prompt) > 100 ? '...' : ''));
        $this->debugLog('Number of uploaded images: ' . count($uploadedImages));

        // Log details of uploaded images
        foreach ($uploadedImages as $index => $image) {
            $this->debugLog('Uploaded image ' . ($index + 1) . ': ' . $image['name'] . ' (' . $image['type'] . ', ' . $image['size'] . ' bytes, error: ' . $image['error'] . ')');
        }

        // Check if images are uploaded and user has permission
        if (!empty($uploadedImages)) {
            $hasPermission = isset($_POST['hasUsagePermission']) && $_POST['hasUsagePermission'] === 'true';
            $this->debugLog('Image usage permission check: hasUsagePermission POST value = ' . ($_POST['hasUsagePermission'] ?? 'NOT SET'));
            $this->debugLog('Image usage permission: ' . ($hasPermission ? 'GRANTED' : 'DENIED'));

            if (!$hasPermission) {
                $respondJsonOrRedirect(false, 'You must confirm that you have permission to use these images');
            }
        }

        try {
            // Generate image using OpenRouter API
            $imageUrl = $this->generateImageWithGemini($prompt, $uploadedImages);
            $this->debugLog('Image generated successfully: ' . $imageUrl);

            // Check if user already has this exact image URL OR the same prompt recently (prevent duplicates)
            $stmt = $pdo->prepare('SELECT id FROM images WHERE user_id = ? AND (image_url = ? OR (prompt = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)))');
            $stmt->execute([$userId, $imageUrl, $prompt]);
            $existingImage = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingImage) {
                $this->debugLog('Duplicate image or recent same prompt detected, skipping save: ' . $imageUrl);
                $respondJsonOrRedirect(false, 'You recently generated an image with this prompt. Try a different prompt or wait a bit!');
            }

            // Save image record
            $pdo->prepare('INSERT INTO images (user_id, prompt, image_url, generation_cost, created_at) VALUES (?, ?, ?, ?, NOW())')
                ->execute([
                    $userId,
                    $prompt,
                    $imageUrl,
                    $creditCost
                ]);
            $this->debugLog('Image record saved to database for user ' . $userId);

            // Deduct credits
            $this->debugLog('Deducting ' . $creditCost . ' credits for image generation from user ' . $userId);
            $pdo->prepare('UPDATE users SET credits = credits - ? WHERE id = ?')
                ->execute([$creditCost, $userId]);

            // Record transaction
            $pdo->prepare('INSERT INTO credit_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, ?, ?)')
                ->execute([$userId, -$creditCost, 'usage', 'Image generation']);
            $this->debugLog('Credit transaction recorded for image generation');

            // Update session with new credit balance
            $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($updatedUser) {
                $_SESSION['user']['credits'] = $updatedUser['credits'];
                $this->debugLog('Session credits updated to ' . $updatedUser['credits']);
            }

            // Success — respond according to request type
            $respondJsonOrRedirect(true, [
                'generated_image' => [
                    'imageUrl' => $imageUrl,
                    'prompt' => $prompt
                ],
                'message' => 'Image generated successfully!'
            ]);

        } catch (Exception $e) {
            $this->debugLog('Image generation failed: ' . $e->getMessage());
            $respondJsonOrRedirect(false, 'Failed to generate image: ' . $e->getMessage());
        }
    }

    // Handle prompt enhancement
    public function enhance() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Handle CORS preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            exit;
        }

        $this->debugLog('Enhance API called - Request method: ' . $_SERVER['REQUEST_METHOD']);
        $this->debugLog('Session ID: ' . session_id());
        $this->debugLog('Session user data: ' . json_encode($_SESSION['user'] ?? 'No user data'));
        $this->debugLog('Raw input: ' . file_get_contents('php://input'));

        if (empty($_SESSION['user'])) {
            $this->debugLog('No user session found');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        // Validate CSRF token
        if (!CSRF::validateRequest()) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->debugLog('Parsed input: ' . json_encode($input));
        $prompt = trim($input['prompt'] ?? '');

        if (empty($prompt)) {
            $this->debugLog('Empty prompt received');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Prompt is required']);
            exit;
        }

        $this->debugLog('Processing prompt: ' . substr($prompt, 0, 50) . '...');

        // Check user credits for enhancement
        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
        $userId = $_SESSION['user']['id'];
        $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get enhancement cost from settings
        $settings = [];
        $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN,0);
        if (in_array('k',$cols)) {
            $rows = $pdo->query('SELECT k,v FROM settings')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $settings[$r['k']] = $r['v'];
        } else {
            $row = $pdo->query('SELECT * FROM settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $settings['enhance_prompt_cost'] = $row['enhanced_prompt_cost'] ?? 1;
            }
        }
        
        $enhanceCost = (int)($settings['enhance_prompt_cost'] ?? 1);
        
        if ($user && $enhanceCost > 0 && $user['credits'] < $enhanceCost) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "Insufficient credits. You need {$enhanceCost} credits."]);
            exit;
        }

        // Check if user has enough credits (but don't deduct yet)
        if ($user && $enhanceCost > 0 && $user['credits'] < $enhanceCost) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "Insufficient credits. You need {$enhanceCost} credits."]);
            exit;
        }

        try {
            // Enhance prompt using OpenRouter API
            $enhancedPrompts = $this->enhancePromptWithLLM($prompt);
            $this->debugLog('Successfully enhanced prompt. Number of prompts: ' . count($enhancedPrompts));
            $this->debugLog('Enhanced prompts: ' . json_encode($enhancedPrompts));

            // Only deduct credits AFTER successful API call
            $updatedCredits = $user['credits'] ?? 0;
            if ($user && $enhanceCost > 0) {
                $this->debugLog('Deducting ' . $enhanceCost . ' credits for prompt enhancement from user ' . $userId);
                $pdo->prepare('UPDATE users SET credits = credits - ? WHERE id = ?')
                    ->execute([$enhanceCost, $userId]);

                $pdo->prepare('INSERT INTO credit_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, ?, ?)')
                    ->execute([$userId, -$enhanceCost, 'usage', 'Prompt enhancement']);

                // Update session with new credit balance
                $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($updatedUser) {
                    $_SESSION['user']['credits'] = $updatedUser['credits'];
                    $updatedCredits = $updatedUser['credits'];
                    $this->debugLog('Credits updated to ' . $updatedCredits . ' after prompt enhancement');
                }
            }

            // Add CORS headers for successful response
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => [
                    'enhancedPrompts' => $enhancedPrompts,
                    'updatedCredits' => $updatedCredits
                ]
            ]);
            exit; // Exit here to prevent duplicate response

        } catch (Exception $e) {
            $this->debugLog('Prompt enhancement failed: ' . $e->getMessage());
            $this->debugLog('Using fallback prompts due to API error - NO CREDITS DEDUCTED');
            // Return fallback prompts WITHOUT deducting credits
            $fallbackPrompts = [
                "{$prompt}, highly detailed, photorealistic, professional photography, dramatic lighting, sharp focus, masterpiece quality",
                "{$prompt}, digital art style, vibrant colors, concept art, artstation trending, detailed textures, cinematic composition",
                "{$prompt}, oil painting style, rich colors, detailed brushwork, classical art composition, golden hour lighting"
            ];
            $this->debugLog('Fallback prompts: ' . json_encode($fallbackPrompts));

            $fallbackResponse = [
                'success' => true,
                'data' => [
                    'enhancedPrompts' => $fallbackPrompts,
                    'fallback' => true,
                    'message' => 'Using fallback prompts due to API error - no credits charged',
                    'updatedCredits' => $user['credits'] ?? 0  // No change to credits
                ]
            ];
            $this->debugLog('Returning fallback response: ' . json_encode($fallbackResponse));
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Content-Type: application/json');
            echo json_encode($fallbackResponse);
        }

        exit; // Exit after processing to prevent any further output
    }

    // Get current user credits
    public function getUserCredits() {
        // Clear any previous output
        if (ob_get_level()) {
            ob_clean();
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        // Debug logging
        error_log('getUserCredits called - Session user: ' . json_encode($_SESSION['user'] ?? 'no session'));
        error_log('getUserCredits called - Request method: ' . $_SERVER['REQUEST_METHOD']);
        error_log('getUserCredits called - Request URI: ' . $_SERVER['REQUEST_URI']);

        // Set headers first
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        if (empty($_SESSION['user'])) {
            $response = json_encode(['success' => false, 'message' => 'Authentication required']);
            error_log('getUserCredits: No user session, returning: ' . $response);
            echo $response;
            exit;
        }

        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            $userId = $_SESSION['user']['id'];

            $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $response = json_encode(['success' => true, 'credits' => (int)$user['credits']]);
                error_log('getUserCredits: Success, returning: ' . $response);
                echo $response;
            } else {
                $response = json_encode(['success' => false, 'message' => 'User not found']);
                error_log('getUserCredits: User not found, returning: ' . $response);
                echo $response;
            }
        } catch (Exception $e) {
            error_log('Error fetching user credits: ' . $e->getMessage());
            $response = json_encode(['success' => false, 'message' => 'Database error']);
            echo $response;
        }
        exit;
    }

    // Generate image using OpenRouter Gemini API
    private function generateImageWithGemini($prompt, $uploadedImages = []) {
        $apiKey = defined('OPENROUTER_API_KEY_RUNTIME') ? OPENROUTER_API_KEY_RUNTIME : getenv('OPENROUTER_API_KEY');
        if (!$apiKey) {
            throw new Exception('OpenRouter API key not configured');
        }

        $this->debugLog('Starting Gemini image generation with ' . count($uploadedImages) . ' uploaded images');
        $this->debugLog('Prompt: ' . $prompt);

        // Determine which API structure to use based on whether images are provided
        if (!empty($uploadedImages)) {
            $this->debugLog('CONFIRMED: Using image-enhanced generation mode with ' . count($uploadedImages) . ' images');

            // Use image-enhanced generation with max 2 images
            $content = [
                [
                    "type" => "text",
                    "text" => $prompt
                ]
            ];

            // Add uploaded images (max 2)
            $imagesToProcess = array_slice($uploadedImages, 0, 2);
            $this->debugLog('Processing ' . count($imagesToProcess) . ' images (max 2)');

            foreach ($imagesToProcess as $index => $image) {
                $this->debugLog('Processing image ' . ($index + 1) . ': ' . $image['name'] . ' (' . $image['type'] . ', ' . $image['size'] . ' bytes)');

                $imageData = base64_encode(file_get_contents($image['tmp_name']));
                $mimeType = $image['type'];

                $this->debugLog('Image ' . ($index + 1) . ' base64 length: ' . strlen($imageData) . ' chars');
                $this->debugLog('Image ' . ($index + 1) . ' MIME type: ' . $mimeType);

                $content[] = [
                    "type" => "image_url",
                    "image_url" => [
                        "url" => "data:{$mimeType};base64,{$imageData}"
                    ]
                ];

                $this->debugLog('Added image ' . ($index + 1) . ' to content array');
            }

            $requestBody = [
                "model" => defined('OPENROUTER_GEMINI_MODEL_RUNTIME') ? OPENROUTER_GEMINI_MODEL_RUNTIME : (getenv('OPENROUTER_GEMINI_MODEL') ?: "google/gemini-flash-1.5"),
                "messages" => [
                    [
                        "role" => "user",
                        "content" => $content
                    ]
                ],
                "generation_config" => [
                    "numberOfImages" => 1,
                    "negativePrompt" => "cartoon, drawing, watermark",
                    "seed" => rand(1, 1000),
                    "aspectRatio" => "16:9",
                    "style" => "photorealistic",
                    "sampleImageSize" => "2K"
                ]
            ];
        } else {
            $this->debugLog('CONFIRMED: Using text-only generation mode (no images provided)');

            // Use text-only generation
            $requestBody = [
                "model" => defined('OPENROUTER_GEMINI_MODEL_RUNTIME') ? OPENROUTER_GEMINI_MODEL_RUNTIME : (getenv('OPENROUTER_GEMINI_MODEL') ?: "google/gemini-flash-1.5"),
                "messages" => [
                    [
                        "role" => "user",
                        "content" => [
                            [
                                "type" => "text",
                                "text" => $prompt
                            ]
                        ]
                    ]
                ],
                "generation_config" => [
                    "numberOfImages" => 1,
                    "negativePrompt" => "cartoon, drawing, watermark",
                    "seed" => rand(1, 1000),
                    "aspectRatio" => "16:9",
                    "style" => "3D render",
                    "sampleImageSize" => "2K"
                ]
            ];
        }

        $this->debugLog('Final model being used: ' . $requestBody['model']);
        $this->debugLog('Content array has ' . count($requestBody['messages'][0]['content']) . ' items');

        // Log content structure without full base64 data
        foreach ($requestBody['messages'][0]['content'] as $i => $item) {
            if ($item['type'] === 'text') {
                $this->debugLog('Content item ' . $i . ': TEXT - ' . substr($item['text'], 0, 100) . (strlen($item['text']) > 100 ? '...' : ''));
            } elseif ($item['type'] === 'image_url') {
                $imageUrl = $item['image_url']['url'];
                $this->debugLog('Content item ' . $i . ': IMAGE - ' . substr($imageUrl, 0, 50) . '... (length: ' . strlen($imageUrl) . ' chars)');
            }
        }

        // Make API request
        $this->debugLog('Preparing OpenRouter API request...');
        $this->debugLog('API URL: ' . $this->openRouterUrl);

        $jsonRequestBody = json_encode($requestBody);
        $this->debugLog('Request body length: ' . strlen($jsonRequestBody) . ' characters');

        // Log the full request body but truncate image data for readability
        $logRequestBody = $requestBody;
        foreach ($logRequestBody['messages'][0]['content'] as &$item) {
            if ($item['type'] === 'image_url') {
                $imageUrl = $item['image_url']['url'];
                $item['image_url']['url'] = substr($imageUrl, 0, 100) . '...[TRUNCATED ' . (strlen($imageUrl) - 100) . ' chars]';
            }
        }
        $this->debugLog('OpenRouter Image Generation Request (truncated): ' . json_encode($logRequestBody, JSON_PRETTY_PRINT));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->openRouterUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequestBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . (defined('OPENROUTER_APP_URL_RUNTIME') ? OPENROUTER_APP_URL_RUNTIME : (getenv('OPENROUTER_APP_URL') ?: 'http://localhost:8000')),
            'X-Title: PictureThis'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $this->debugLog('Sending curl request to OpenRouter...');
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->debugLog('Curl request completed');
        $this->debugLog('OpenRouter Response - HTTP Code: ' . $httpCode . ', Content-Type: ' . $contentType . ', Response Length: ' . strlen($response));

        if ($error) {
            $this->debugLog('Curl Error: ' . $error);
        }

        // Log response in chunks to avoid potential issues with very long responses
        $responseLength = strlen($response);
        if ($responseLength > 10000) {
            $this->debugLog('OpenRouter Full Response (truncated): ' . substr($response, 0, 5000) . '...[truncated ' . ($responseLength - 5000) . ' chars]');
        } else {
            $this->debugLog('OpenRouter Full Response: ' . $response);
        }

        if ($error) {
            $this->debugLog('OpenRouter API Error: ' . $error . ' | Content-Type: ' . $contentType . ' | Response: ' . substr($response, 0, 500));
            throw new Exception('API request failed: ' . $error);
        }

        if ($httpCode !== 200) {
            $this->debugLog('OpenRouter API HTTP Error: ' . $httpCode . ' | Content-Type: ' . $contentType . ' | Response: ' . substr($response, 0, 500));
            throw new Exception('API request failed with status: ' . $httpCode);
        }

        $data = json_decode($response, true);
        
        // Check if response is JSON (contains image URL) or binary (direct image data)
        if ($data === null) {
            // Response is not JSON, treat as binary image data
            $this->debugLog('Received binary image response from OpenRouter (length: ' . strlen($response) . ' bytes)');
            
            $filename = 'generated_' . time() . '_' . uniqid() . '.png';
            $filepath = $this->uploadsDir . $filename;

            if (file_put_contents($filepath, $response)) {
                $this->debugLog('Binary image saved successfully: ' . $filepath);
                return '/uploads/' . $filename;
            } else {
                $this->debugLog('Failed to save binary image to: ' . $filepath);
                throw new Exception('Failed to save generated image from binary data');
            }
        }

        // Handle JSON response (existing logic)
        $this->debugLog('Received JSON response from OpenRouter');
        if (!$data) {
            throw new Exception('Invalid API response');
        }

        // Extract image from response
        if (!isset($data['choices'][0]['message']['images'][0]['image_url']['url'])) {
            throw new Exception('No image found in API response');
        }

        $imageUrl = $data['choices'][0]['message']['images'][0]['image_url']['url'];

        // If it's a base64 data URL, save it as a file
        if (strpos($imageUrl, 'data:image/') === 0) {
            $imageData = substr($imageUrl, strpos($imageUrl, ',') + 1);
            $decodedImage = base64_decode($imageData);

            $filename = 'generated_' . time() . '_' . uniqid() . '.png';
            $filepath = $this->uploadsDir . $filename;

            if (file_put_contents($filepath, $decodedImage)) {
                $this->debugLog('Base64 image saved successfully: ' . $filepath);
                return '/uploads/' . $filename;
            } else {
                $this->debugLog('Failed to save base64 image to: ' . $filepath);
                throw new Exception('Failed to save generated image');
            }
        }

        $this->debugLog('Returning external image URL: ' . $imageUrl);
        
        // Always save external images locally to ensure uniqueness and avoid caching issues
        $this->debugLog('Downloading and saving external image locally...');
        $imageData = file_get_contents($imageUrl);
        if ($imageData !== false) {
            $filename = 'generated_' . time() . '_' . uniqid() . '.png';
            $filepath = $this->uploadsDir . $filename;
            
            if (file_put_contents($filepath, $imageData)) {
                $this->debugLog('External image saved locally: ' . $filepath);
                return '/uploads/' . $filename;
            } else {
                $this->debugLog('Failed to save external image locally, returning original URL');
                return $imageUrl;
            }
        } else {
            $this->debugLog('Failed to download external image, returning original URL');
            return $imageUrl;
        }
    }

    // Enhance prompt using OpenRouter LLM
    private function enhancePromptWithLLM($userPrompt) {
        $this->debugLog('enhancePromptWithLLM method called with prompt: ' . substr($userPrompt, 0, 50));
        $apiKey = defined('OPENROUTER_API_KEY_RUNTIME') ? OPENROUTER_API_KEY_RUNTIME : getenv('OPENROUTER_API_KEY');
        if (!$apiKey) {
            throw new Exception('OpenRouter API key not configured');
        }

        $systemPrompt = 'You are a professional AI image generation prompt engineer. Your job is to take a simple user prompt and enhance it to match the tone and style, then create 5 detailed, enhanced prompts that will generate stunning, high-quality images.

Guidelines:
- Analyze the user\'s input tone, style, and intent to enhance it appropriately
- Create 5 unique, detailed prompts that expand on the user\'s concept
- Each prompt should be very detailed and descriptive (60-120 words)
- Include artistic styles, lighting, composition, colors, and mood
- Make each prompt unique while staying true to the original concept
- Focus on visual details that AI image generators understand well
- Include technical photography/art terms when appropriate
- Rate each prompt from 1-5 based on quality and potential for stunning results
- Return prompts ordered by rating (1 = best, 5 = good but less optimal)

CRITICAL REQUIREMENTS:
- Your response must be ONLY a valid JSON array
- No text before or after the JSON
- No markdown formatting, no code blocks, no explanations
- No additional commentary or notes
- Just pure JSON that can be parsed by json_decode()

Response format:
[{"rating": 1, "prompt": "most detailed and best prompt here"}, {"rating": 2, "prompt": "second best prompt here"}, {"rating": 3, "prompt": "third best prompt here"}, {"rating": 4, "prompt": "fourth best prompt here"}, {"rating": 5, "prompt": "fifth prompt here"}]';

        $requestBody = [
            "model" => defined('OPENROUTER_MODEL_RUNTIME') ? OPENROUTER_MODEL_RUNTIME : (getenv('OPENROUTER_MODEL') ?: "openai/gpt-oss-20b:free"),
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => "Original prompt: \"{$userPrompt}\"\n\nPlease enhance this prompt to match its tone and style, then create 5 detailed, enhanced prompts for AI image generation. Rate each prompt 1-5 (1 being the best) and return them ordered by rating.\n\nRemember: Return ONLY JSON, no other text."
                ]
            ],
            "max_tokens" => 4000,
            "temperature" => 0.8
        ];

        // Make API request
        $this->debugLog('OpenRouter Prompt Enhancement Request: ' . json_encode($requestBody));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->openRouterUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . (defined('OPENROUTER_APP_URL_RUNTIME') ? OPENROUTER_APP_URL_RUNTIME : (getenv('OPENROUTER_APP_URL') ?: 'http://localhost:8000')),
            'X-Title: PictureThis'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        // Log the complete API response for debugging
        $this->debugLog('About to log prompt enhancement response');
        $this->debugLog('OpenRouter Prompt Enhancement Response - HTTP Code: ' . $httpCode . ', Content-Type: ' . $contentType . ', Response Length: ' . strlen($response));
        
        // Log response in chunks to avoid potential issues with very long responses
        $responseLength = strlen($response);
        if ($responseLength > 10000) {
            $this->debugLog('OpenRouter Full Response (first 5000 chars): ' . substr($response, 0, 5000));
            $this->debugLog('OpenRouter Full Response (last 5000 chars): ' . substr($response, -5000));
            $this->debugLog('OpenRouter Full Response (middle section): ' . substr($response, 2800, 100));
        } else {
            $this->debugLog('OpenRouter Full Response: ' . $response);
        }

        if ($error) {
            $this->debugLog('OpenRouter API Error: ' . $error . ' | Content-Type: ' . $contentType . ' | Response: ' . substr($response, 0, 500));
            throw new Exception('API request failed: ' . $error);
        }

        if ($httpCode !== 200) {
            $this->debugLog('OpenRouter API HTTP Error: ' . $httpCode . ' | Content-Type: ' . $contentType . ' | Response: ' . substr($response, 0, 500));
            throw new Exception('API request failed with status: ' . $httpCode);
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['choices'][0]['message']['content'])) {
            $this->debugLog('Invalid API response structure. Response type: ' . gettype($data));
            $this->debugLog('Response keys: ' . ($data ? json_encode(array_keys($data)) : 'null'));
            $this->debugLog('Full response for debugging: ' . substr($response, 0, 1000));
            throw new Exception('Invalid API response structure');
        }

        $content = $data['choices'][0]['message']['content'];

        // Log the COMPLETE raw content for debugging
        $this->debugLog('=== LLM COMPLETE RAW RESPONSE START ===');
        $this->debugLog('LLM Raw Content (FULL): ' . $content);
        $this->debugLog('LLM Content Length: ' . strlen($content));
        $this->debugLog('=== LLM COMPLETE RAW RESPONSE END ===');

        // Parse JSON response - handle potential extra text around JSON
        $enhancedPromptsWithRatings = $this->parseJsonResponse($content);

        if (!$enhancedPromptsWithRatings || !is_array($enhancedPromptsWithRatings)) {
            $this->debugLog('CRITICAL: All JSON parsing attempts failed for LLM response');
            $this->debugLog('Raw content length: ' . strlen($content));
            $this->debugLog('Raw content preview: ' . substr($content, 0, 500));
            $this->debugLog('Raw content around error position: ' . substr($content, 2870, 20));
            $this->debugLog('Full raw content (first 2000 chars): ' . substr($content, 0, 2000));
            $this->debugLog('Full raw content (last 2000 chars): ' . substr($content, -2000));
            
            // Try one final extraction attempt with a more aggressive regex
            $this->debugLog('Attempting final extraction with aggressive regex');
            $jsonPattern = '/(\[[\s\S]*\])/';
            if (preg_match($jsonPattern, $content, $matches)) {
                $this->debugLog('Final regex match found, trying to parse: ' . substr($matches[1], 0, 100));
                $fallbackData = json_decode($matches[1], true);
                if ($fallbackData && is_array($fallbackData)) {
                    $this->debugLog('SUCCESS: Final regex extraction successful: ' . count($fallbackData) . ' items');
                    $enhancedPromptsWithRatings = $fallbackData;
                } else {
                    $this->debugLog('FAILED: Final regex extraction failed. JSON Error: ' . json_last_error_msg());
                }
            } else {
                $this->debugLog('FAILED: No JSON pattern found in final extraction attempt');
            }
            
            if (!$enhancedPromptsWithRatings || !is_array($enhancedPromptsWithRatings)) {
                $this->debugLog('CRITICAL FAILURE: Unable to extract valid JSON from LLM response after all attempts');
                throw new Exception('Invalid JSON response from API - unable to parse after multiple extraction attempts');
            }
        }

        $this->debugLog('Successfully parsed ' . count($enhancedPromptsWithRatings) . ' enhanced prompts from LLM response');

        // Sort by rating and return prompts
        usort($enhancedPromptsWithRatings, function($a, $b) {
            return $a['rating'] <=> $b['rating'];
        });

        return array_map(function($item) {
            return $item['prompt'];
        }, $enhancedPromptsWithRatings);
    }

    // Parse JSON response from LLM, handling potential extra text
    private function parseJsonResponse($content) {
        $this->debugLog('=== STARTING JSON PARSING ===');
        $this->debugLog('Content length: ' . strlen($content));
        $this->debugLog('Content preview (first 300 chars): ' . substr($content, 0, 300));
        $this->debugLog('Content preview (last 300 chars): ' . substr($content, -300));
        $this->debugLog('Content around position 2877: ' . substr($content, 2870, 20));

        // Attempt 1: Direct JSON parsing
        $this->debugLog('Attempt 1: Direct JSON parsing');
        $data = json_decode($content, true);
        $jsonError = json_last_error();
        if ($data !== null && is_array($data)) {
            $this->debugLog('SUCCESS: Direct JSON parsing successful: ' . count($data) . ' items');
            return $data;
        }
        $this->debugLog('FAILED: Direct JSON parsing failed. JSON Error: ' . json_last_error_msg() . ' (Code: ' . $jsonError . ')');

        // Attempt 2: Progressive parsing from the end
        $this->debugLog('Attempt 2: Progressive parsing from the end');
        $contentLength = strlen($content);
        for ($i = $contentLength; $i > 0; $i--) {
            $testContent = substr($content, 0, $i);
            $testData = json_decode($testContent, true);
            if ($testData !== null && is_array($testData)) {
                $this->debugLog('SUCCESS: Found valid JSON at length ' . $i . ': ' . count($testData) . ' items');
                $this->debugLog('Extra content after JSON: ' . substr($content, $i, 50));
                return $testData;
            }
        }
        $this->debugLog('FAILED: Progressive parsing found no valid JSON');

        // Attempt 3: Extract JSON array using regex
        $this->debugLog('Attempt 3: Extract JSON array using regex');
        $jsonPattern = '/\[.*\]/s';
        if (preg_match($jsonPattern, $content, $matches)) {
            $this->debugLog('Found JSON array pattern match, length: ' . strlen($matches[0]));
            $this->debugLog('Match preview: ' . substr($matches[0], 0, 200) . '...');
            $data = json_decode($matches[0], true);
            if ($data !== null && is_array($data)) {
                $this->debugLog('SUCCESS: JSON array extraction successful: ' . count($data) . ' items');
                return $data;
            }
            $this->debugLog('FAILED: JSON array extraction failed. JSON Error: ' . json_last_error_msg());
        } else {
            $this->debugLog('FAILED: No JSON array pattern found');
        }

        // Attempt 4: Extract JSON object using regex
        $this->debugLog('Attempt 4: Extract JSON object using regex');
        $jsonPattern = '/\{.*\}/s';
        if (preg_match($jsonPattern, $content, $matches)) {
            $this->debugLog('Found JSON object pattern match, length: ' . strlen($matches[0]));
            $this->debugLog('Match preview: ' . substr($matches[0], 0, 200) . '...');
            $data = json_decode($matches[0], true);
            if ($data !== null && is_array($data)) {
                $this->debugLog('SUCCESS: JSON object extraction successful: ' . count($data) . ' items');
                return $data;
            }
            $this->debugLog('FAILED: JSON object extraction failed. JSON Error: ' . json_last_error_msg());
        } else {
            $this->debugLog('FAILED: No JSON object pattern found');
        }

        // Attempt 5: Clean up common issues and try again
        $this->debugLog('Attempt 5: Clean up common issues');
        $cleanContent = trim($content);
        // Remove markdown code blocks if present
        $cleanContent = preg_replace('/^```json\s*/', '', $cleanContent);
        $cleanContent = preg_replace('/```\s*$/', '', $cleanContent);
        $cleanContent = preg_replace('/^```\s*/', '', $cleanContent);
        $cleanContent = trim($cleanContent);

        $this->debugLog('Cleaned content preview: ' . substr($cleanContent, 0, 200) . '...');
        $data = json_decode($cleanContent, true);
        if ($data !== null && is_array($data)) {
            $this->debugLog('SUCCESS: Cleaned content parsing successful: ' . count($data) . ' items');
            return $data;
        }
        $this->debugLog('FAILED: Cleaned content parsing failed. JSON Error: ' . json_last_error_msg());

        // Attempt 6: Look for JSON within quotes or other wrappers
        $this->debugLog('Attempt 6: Look for JSON within quotes or other wrappers');
        $patterns = [
            '/"(\[.*\])"/s',  // JSON array within quotes
            '/\'(\[.*\])\'/s', // JSON array within single quotes
            '/"(\{.*\})"/s',  // JSON object within quotes
            '/\'(\{.*\})\'/s', // JSON object within single quotes
        ];

        foreach ($patterns as $index => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $this->debugLog('Pattern ' . ($index + 1) . ' match found, trying to parse: ' . substr($matches[1], 0, 100) . '...');
                $data = json_decode($matches[1], true);
                if ($data !== null && is_array($data)) {
                    $this->debugLog('SUCCESS: Pattern ' . ($index + 1) . ' extraction successful: ' . count($data) . ' items');
                    return $data;
                }
                $this->debugLog('FAILED: Pattern ' . ($index + 1) . ' extraction failed. JSON Error: ' . json_last_error_msg());
            }
        }

        // Attempt 7: Try to find the largest valid JSON substring
        $this->debugLog('Attempt 7: Try to find the largest valid JSON substring');
        $maxValidLength = 0;
        $bestData = null;
        $jsonStart = strpos($content, '[');
        if ($jsonStart !== false) {
            for ($end = strrpos($content, ']'); $end > $jsonStart; $end--) {
                $testSubstring = substr($content, $jsonStart, $end - $jsonStart + 1);
                $testData = json_decode($testSubstring, true);
                if ($testData !== null && is_array($testData) && strlen($testSubstring) > $maxValidLength) {
                    $maxValidLength = strlen($testSubstring);
                    $bestData = $testData;
                }
            }
        }

        if ($bestData !== null) {
            $this->debugLog('SUCCESS: Found largest valid JSON substring: ' . count($bestData) . ' items');
            return $bestData;
        }
        $this->debugLog('FAILED: No valid JSON substrings found');

        $this->debugLog('=== ALL JSON PARSING ATTEMPTS FAILED ===');
        $this->debugLog('Final content sample: ' . substr($content, 0, 500));
        return null;
    }
}
