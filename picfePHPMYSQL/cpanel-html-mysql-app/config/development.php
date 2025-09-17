<?php
// Development Configuration
// This file contains all development-specific settings
// Sensitive values are loaded from environment variables or .env file

return [
    'app' => [
        'env' => 'development',
        'url' => getEnvVar('APP_URL', 'http://localhost:8000'),
        'name' => 'PictureThis (Dev)',
        'debug' => true,
    ],
    'database' => [
        'host' => getEnvVar('DB_HOST', '127.0.0.1:3306'),
        'user' => getEnvVar('DB_USER', 'pt_user'),
        'pass' => getEnvVar('DB_PASS', 'pt_pass'),
        'name' => getEnvVar('DB_NAME', 'picturethis_dev'),
    ],
    'payfast' => [
        'merchant_id' => getEnvVar('PAYFAST_MERCHANT_ID', '10041798'),
        'merchant_key' => getEnvVar('PAYFAST_MERCHANT_KEY', 'vlnqle74tnkl7'),
        'passphrase' => getEnvVar('PAYFAST_PASSPHRASE', 'ThisIsATestFromPictureThis'),
        'env' => 'development',
    ],
    'openrouter' => [
        'api_key' => getEnvVar('OPENROUTER_API_KEY', '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'),
        'app_url' => getEnvVar('APP_URL', 'https://demo.cfox.co.za'),
        'gemini_model' => 'google/gemini-2.5-flash-image-preview',
        'model' => '@preset/picture-this-agent',
    ],
    'email' => [
        'smtp_host' => getEnvVar('SMTP_HOST', 'metallurgix.aserv.co.za'),
        'smtp_username' => getEnvVar('SMTP_USERNAME', 'cfoxcozj'),
        'smtp_password' => getEnvVar('SMTP_PASSWORD', 'Runx141kw007@'),
        'smtp_port' => getEnvVar('SMTP_PORT', '587'),
        'from_email' => getEnvVar('SMTP_FROM_EMAIL', 'picturethis@cfox.co.za'),
    ],
    'images' => [
        'retention_days' => 7,
        'min_images_per_user' => 3,
    ],
];
?>