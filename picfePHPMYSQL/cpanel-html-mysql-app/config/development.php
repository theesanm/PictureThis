<?php
// Development Configuration
// This file contains all development-specific settings
return [
    'app' => [
        'env' => 'development',
        'url' => 'http://localhost:8000',
        'name' => 'PictureThis (Dev)',
        'debug' => true,
    ],
    'database' => [
        'host' => '127.0.0.1',
        'user' => 'pt_user',
        'pass' => 'pt_pass',
        'name' => 'picturethis_dev',
    ],
    'payfast' => [
        'merchant_id' => '10041798',
        'merchant_key' => 'vlnqle74tnkl7',
        'passphrase' => 'ThisIsATestFromPictureThis',
        'env' => 'development',
    ],
    'openrouter' => [
        'api_key' => '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',
        'app_url' => 'https://demo.cfox.co.za',
        'gemini_model' => 'google/gemini-2.5-flash-image-preview',
        'model' => '@preset/picture-this-agent',
    ],
    'email' => [
        'smtp_host' => 'metallurgix.aserv.co.za',
        'smtp_username' => 'cfoxcozj',
        'smtp_password' => 'Runx141kw007@',
        'smtp_port' => '587',
        'from_email' => 'picturethis@cfox.co.za',
    ],
    'images' => [
        'retention_days' => 7,
        'min_images_per_user' => 3,
    ],
    'agent' => [
        'session_timeout_minutes' => 60, // Change this value to set agent session timeout
    ],
];
?>
