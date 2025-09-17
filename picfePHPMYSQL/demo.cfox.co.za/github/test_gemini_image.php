<?php
// test_gemini_image.php - Test script to simulate image upload and LLM call for debugging

// Include config for API keys and settings
require_once 'config/config.php';

// Debug logging function
function debugLog($message) {
    $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents('debug.log', $logMessage, FILE_APPEND);
    echo $message . PHP_EOL;
}

// Simulate image upload
debugLog('=== Starting Image Upload and LLM Call Simulation ===');

// Create a test image file (small PNG for testing)
$testImagePath = 'uploads/test_image_' . time() . '.png';
$testImageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='); // 1x1 transparent PNG
file_put_contents($testImagePath, $testImageData);
debugLog('Created test image: ' . $testImagePath . ' (' . filesize($testImagePath) . ' bytes)');

// Simulate uploaded file array
$uploadedImages = [
    [
        'name' => 'test_image.png',
        'type' => 'image/png',
        'tmp_name' => $testImagePath,
        'error' => 0,
        'size' => filesize($testImagePath)
    ]
];

$prompt = 'A beautiful sunset over mountains with vibrant colors';
$userId = null;

// Find testuser
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$result = $conn->query("SELECT id FROM users WHERE email = 'testuser@example.com'");
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    debugLog('Found testuser with ID: ' . $userId);
} else {
    $result = $conn->query("SELECT id, email FROM users LIMIT 1");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        debugLog('Using user: ' . $user['email'] . ' (ID: ' . $userId . ')');
    } else {
        die('No users found in database');
    }
}

// Simulate the image generation process
debugLog('=== Simulating Image Generation Process ===');
debugLog('Prompt: ' . $prompt);
debugLog('Number of uploaded images: ' . count($uploadedImages));

// Log details of uploaded images
foreach ($uploadedImages as $index => $image) {
    debugLog('Uploaded image ' . ($index + 1) . ': ' . $image['name'] . ' (' . $image['type'] . ', ' . $image['size'] . ' bytes, error: ' . $image['error'] . ')');
}

// Build the request body for OpenRouter API
$apiKey = defined('OPENROUTER_API_KEY_RUNTIME') ? OPENROUTER_API_KEY_RUNTIME : getenv('OPENROUTER_API_KEY');
if (!$apiKey) {
    die('OpenRouter API key not configured');
}

debugLog('Using OpenRouter API key: ' . substr($apiKey, 0, 10) . '...');

// Build content array
$content = [
    [
        "type" => "text",
        "text" => $prompt
    ]
];

// Add uploaded images (max 2)
$imagesToProcess = array_slice($uploadedImages, 0, 2);
debugLog('Processing ' . count($imagesToProcess) . ' images (max 2)');

foreach ($imagesToProcess as $index => $image) {
    debugLog('Processing image ' . ($index + 1) . ': ' . $image['name'] . ' (' . $image['type'] . ', ' . $image['size'] . ' bytes)');

    $imageData = base64_encode(file_get_contents($image['tmp_name']));
    $mimeType = $image['type'];

    debugLog('Image ' . ($index + 1) . ' base64 length: ' . strlen($imageData) . ' chars');
    debugLog('Image ' . ($index + 1) . ' MIME type: ' . $mimeType);

    $content[] = [
        "type" => "image_url",
        "image_url" => [
            "url" => "data:{$mimeType};base64,{$imageData}"
        ]
    ];

    debugLog('Added image ' . ($index + 1) . ' to content array');
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

debugLog('Final model being used: ' . $requestBody['model']);
debugLog('Content array has ' . count($requestBody['messages'][0]['content']) . ' items');

// Log content structure without full base64 data
foreach ($requestBody['messages'][0]['content'] as $i => $item) {
    if ($item['type'] === 'text') {
        debugLog('Content item ' . $i . ': TEXT - ' . substr($item['text'], 0, 100) . (strlen($item['text']) > 100 ? '...' : ''));
    } elseif ($item['type'] === 'image_url') {
        $imageUrl = $item['image_url']['url'];
        debugLog('Content item ' . $i . ': IMAGE - ' . substr($imageUrl, 0, 50) . '... (length: ' . strlen($imageUrl) . ' chars)');
    }
}

// Make the API request
$openRouterUrl = defined('OPENROUTER_API_URL') ? OPENROUTER_API_URL : 'https://openrouter.ai/api/v1/chat/completions';
debugLog('API URL: ' . $openRouterUrl);

$jsonRequestBody = json_encode($requestBody);
debugLog('Request body length: ' . strlen($jsonRequestBody) . ' characters');

// Log the full request body but truncate image data for readability
$logRequestBody = $requestBody;
foreach ($logRequestBody['messages'][0]['content'] as &$item) {
    if ($item['type'] === 'image_url') {
        $imageUrl = $item['image_url']['url'];
        $item['image_url']['url'] = substr($imageUrl, 0, 100) . '...[TRUNCATED ' . (strlen($imageUrl) - 100) . ' chars]';
    }
}
debugLog('OpenRouter Image Generation Request (truncated): ' . json_encode($logRequestBody, JSON_PRETTY_PRINT));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $openRouterUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequestBody);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json',
    'HTTP-Referer: ' . (defined('OPENROUTER_APP_URL_RUNTIME') ? OPENROUTER_APP_URL_RUNTIME : (getenv('OPENROUTER_APP_URL') ?: 'http://localhost:8000')),
    'X-Title: PictureThis AI Image Generator'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

debugLog('Sending curl request to OpenRouter...');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error = curl_error($ch);
curl_close($ch);

debugLog('Curl request completed');
debugLog('OpenRouter Response - HTTP Code: ' . $httpCode . ', Content-Type: ' . $contentType . ', Response Length: ' . strlen($response));

if ($error) {
    debugLog('Curl Error: ' . $error);
}

// Log response in chunks to avoid potential issues with very long responses
$responseLength = strlen($response);
if ($responseLength > 10000) {
    debugLog('OpenRouter Full Response (truncated): ' . substr($response, 0, 5000) . '...[truncated ' . ($responseLength - 5000) . ' chars]');
} else {
    debugLog('OpenRouter Full Response: ' . $response);
}

if ($error) {
    debugLog('OpenRouter API Error: ' . $error . ' | Content-Type: ' . $contentType . ' | Response: ' . substr($response, 0, 500));
    die('API request failed: ' . $error);
}

if ($httpCode !== 200) {
    debugLog('OpenRouter API HTTP Error: ' . $httpCode . ' | Content-Type: ' . $contentType . ' | Response: ' . substr($response, 0, 500));
    die('API request failed with status: ' . $httpCode);
}

debugLog('=== API Call Simulation Complete ===');
debugLog('Check debug.log for full details of the request and response');

// Clean up test image
unlink($testImagePath);
debugLog('Cleaned up test image: ' . $testImagePath);

$conn->close();
echo 'Simulation complete. Check debug.log for detailed logging.' . PHP_EOL;
