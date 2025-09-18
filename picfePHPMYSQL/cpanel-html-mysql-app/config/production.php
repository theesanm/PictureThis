<?php
// Production Configuration
// This file contains all production-specific settings
return [
    'app' => [
        'env' => 'production',
        'url' => 'https://demo.cfox.co.za',
        'name' => 'PictureThis',
        'debug' => false,
    ],
    'database' => [
        'host' => '127.0.0.1',
        'user' => 'your_db_user',
        'pass' => 'your_db_password',
        'name' => 'your_db_name',
    ],
    'payfast' => [
        'merchant_id' => '10041798',
        'merchant_key' => 'vlnqle74tnkl7',
        'passphrase' => 'ThisIsATestFromPictureThis',
        'env' => 'production',
    ],
    'openrouter' => [
        'api_key' => '',
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
        'upload_max_size' => 20971520,
    ],
    'agent' => [
        'session_timeout_minutes' => 60, // Change this value to set agent session timeout
    ],
];
?>
