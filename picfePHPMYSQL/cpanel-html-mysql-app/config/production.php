<?php
// Production Configuration
// This file contains all production-specific settings
// IMPORTANT: Set environment variables in cPanel or create .env file
return [
    'app' => [
        'env' => 'production',
        'url' => getEnvVar('APP_URL', 'https://demo.cfox.co.za'), // Replace with your production domain
        'name' => 'PictureThis',
        'debug' => false,
    ],
    'database' => [
        'host' => getEnvVar('DB_HOST', '127.0.0.1:3306'), // Your production database host
        'user' => getEnvVar('DB_USER', 'cfoxcozj_picThisdb'), // Your production database user
        'pass' => getEnvVar('DB_PASS', 'LfUYHI%]{sjb5A*u'), // Your production database password
        'name' => getEnvVar('DB_NAME', 'cfoxcozj_PictureThis'), // Your production database name
    ],
    'payfast' => [
        'merchant_id' => getEnvVar('PAYFAST_MERCHANT_ID', 'YOUR_ACTUAL_PAYFAST_MERCHANT_ID'), // Set in cPanel env vars
        'merchant_key' => getEnvVar('PAYFAST_MERCHANT_KEY', 'YOUR_ACTUAL_PAYFAST_MERCHANT_KEY'), // Set in cPanel env vars
        'passphrase' => getEnvVar('PAYFAST_PASSPHRASE', 'YOUR_ACTUAL_PAYFAST_PASSPHRASE'), // Set in cPanel env vars
        'env' => 'production',
    ],
    'openrouter' => [
        'api_key' => getEnvVar('OPENROUTER_API_KEY', 'YOUR_ACTUAL_OPENROUTER_API_KEY'), // Set in cPanel env vars
        'app_url' => getEnvVar('APP_URL', 'https://demo.cfox.co.za'),
        'gemini_model' => 'google/gemini-2.5-flash-image-preview',
        'model' => 'openai/gpt-oss-20b:free',
    ],
    'email' => [
        'smtp_host' => getEnvVar('SMTP_HOST', 'metallurgix.aserv.co.za'),
        'smtp_username' => getEnvVar('SMTP_USERNAME', 'cfoxcozj'),
        'smtp_password' => getEnvVar('SMTP_PASSWORD', 'YOUR_ACTUAL_SMTP_PASSWORD'), // Set in cPanel env vars
        'smtp_port' => getEnvVar('SMTP_PORT', '587'),
        'from_email' => getEnvVar('SMTP_FROM_EMAIL', 'picturethis@cfox.co.za'),
    ],
    'images' => [
        'retention_days' => 7,
        'min_images_per_user' => 3,
        'upload_max_size' => 20971520,
    ],
];
?>