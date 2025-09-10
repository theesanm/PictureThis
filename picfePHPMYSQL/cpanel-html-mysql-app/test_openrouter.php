<?php
require_once __DIR__ . '/config/config.php';

// Test OpenRouter API connection
function testOpenRouterAPI() {
    $apiKey = getenv('OPENROUTER_API_KEY');
    if (!$apiKey || $apiKey === 'sk-or-v1-your-openrouter-api-key-here') {
        echo "❌ OpenRouter API key not configured. Please set OPENROUTER_API_KEY in config.php\n";
        return false;
    }

    $url = 'https://openrouter.ai/api/v1/chat/completions';

    $requestBody = [
        "model" => getenv('OPENROUTER_MODEL') ?: "anthropic/claude-3-haiku",
        "messages" => [
            [
                "role" => "user",
                "content" => "Say 'Hello from OpenRouter API test' in exactly those words."
            ]
        ],
        "max_tokens" => 50
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'HTTP-Referer: ' . (getenv('OPENROUTER_APP_URL') ?: 'http://localhost:8000'),
        'X-Title: PictureThis AI Image Generator'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    echo "🔄 Testing OpenRouter API connection...\n";

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ API request failed: $error\n";
        return false;
    }

    if ($httpCode !== 200) {
        echo "❌ API request failed with status: $httpCode\n";
        echo "Response: $response\n";
        return false;
    }

    $data = json_decode($response, true);
    if (!$data || !isset($data['choices'][0]['message']['content'])) {
        echo "❌ Invalid API response\n";
        echo "Response: $response\n";
        return false;
    }

    $content = $data['choices'][0]['message']['content'];
    echo "✅ OpenRouter API connection successful!\n";
    echo "Response: $content\n";
    return true;
}

// Test Gemini image generation API
function testGeminiImageAPI() {
    $apiKey = getenv('OPENROUTER_API_KEY');
    if (!$apiKey || $apiKey === 'sk-or-v1-your-openrouter-api-key-here') {
        echo "❌ OpenRouter API key not configured. Please set OPENROUTER_API_KEY in config.php\n";
        return false;
    }

    $url = 'https://openrouter.ai/api/v1/images/generations';

    $requestBody = [
        "model" => getenv('OPENROUTER_GEMINI_MODEL') ?: "google/gemini-flash-1.5",
        "messages" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => "A simple test image of a blue circle"
                    ]
                ]
            ]
        ],
        "generation_config" => [
            "numberOfImages" => 1,
            "negativePrompt" => "cartoon, drawing, watermark",
            "seed" => rand(1, 1000),
            "aspectRatio" => "1:1",
            "style" => "photorealistic",
            "sampleImageSize" => "512x512"
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'HTTP-Referer: ' . (getenv('OPENROUTER_APP_URL') ?: 'http://localhost:8000'),
        'X-Title: PictureThis AI Image Generator'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    echo "🔄 Testing Gemini image generation API...\n";

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ Image API request failed: $error\n";
        return false;
    }

    if ($httpCode !== 200) {
        echo "❌ Image API request failed with status: $httpCode\n";
        echo "Response: $response\n";
        return false;
    }

    $data = json_decode($response, true);
    if (!$data || !isset($data['choices'][0]['message']['images'][0]['image_url']['url'])) {
        echo "❌ Invalid image API response\n";
        echo "Response: $response\n";
        return false;
    }

    $imageUrl = $data['choices'][0]['message']['images'][0]['image_url']['url'];
    echo "✅ Gemini image generation API connection successful!\n";
    echo "Generated image URL: $imageUrl\n";
    return true;
}

// Run tests
echo "=== OpenRouter API Test ===\n\n";

$chatTest = testOpenRouterAPI();
echo "\n";

$imageTest = testGeminiImageAPI();
echo "\n";

if ($chatTest && $imageTest) {
    echo "🎉 All API tests passed! The image generation system should work correctly.\n";
} else {
    echo "⚠️  Some API tests failed. Please check your configuration and API key.\n";
}

echo "\n=== Configuration Check ===\n";
echo "OPENROUTER_API_KEY: " . (getenv('OPENROUTER_API_KEY') ? "Set" : "Not set") . "\n";
echo "OPENROUTER_APP_URL: " . (getenv('OPENROUTER_APP_URL') ?: "Not set") . "\n";
echo "OPENROUTER_GEMINI_MODEL: " . (getenv('OPENROUTER_GEMINI_MODEL') ?: "Not set") . "\n";
echo "OPENROUTER_MODEL: " . (getenv('OPENROUTER_MODEL') ?: "Not set") . "\n";
?>
