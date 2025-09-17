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
        'host' => '127.0.0.1:3306',
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
        'model' => 'openai/gpt-oss-20b:free',
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
];
?>