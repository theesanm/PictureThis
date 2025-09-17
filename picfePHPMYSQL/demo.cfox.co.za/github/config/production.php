<?php
// Production Configuration
// This file contains all production-specific settings
// IMPORTANT: Update these values with your actual production credentials
return [
    'app' => [
        'env' => 'production',
        'url' => 'https://demo.cfox.co.za', // Replace with your production domain
        'name' => 'PictureThis',
        'debug' => false,
    ],
    'database' => [
        'host' => '127.0.0.1', // Your production database host
        'user' => 'cfoxcozj_picThisdb', // Your production database user
        'pass' => 'LfUYHI%]{sjb5A*u', // Actual production database password
        'name' => 'cfoxcozj_PictureThis', // Your production database name
    ],
    'payfast' => [
        'merchant_id' => 'YOUR_ACTUAL_PAYFAST_MERCHANT_ID', // 🔴 REPLACE WITH YOUR PAYFAST MERCHANT ID
        'merchant_key' => 'YOUR_ACTUAL_PAYFAST_MERCHANT_KEY', // 🔴 REPLACE WITH YOUR PAYFAST MERCHANT KEY
        'passphrase' => 'YOUR_ACTUAL_PAYFAST_PASSPHRASE', // 🔴 REPLACE WITH YOUR PAYFAST PASSPHRASE
        'env' => 'production',
    ],
    'openrouter' => [
        'api_key' => 'YOUR_ACTUAL_OPENROUTER_API_KEY', // 🔴 REPLACE WITH YOUR OPENROUTER API KEY
        'app_url' => 'https://demo.cfox.co.za',
        'gemini_model' => 'google/gemini-2.5-flash-image-preview',
        'model' => 'openai/gpt-oss-20b:free',
    ],
    'email' => [
        'smtp_host' => 'metallurgix.aserv.co.za',
        'smtp_username' => 'cfoxcozj',
        'smtp_password' => 'YOUR_ACTUAL_SMTP_PASSWORD', // 🔴 REPLACE WITH YOUR SMTP PASSWORD
        'smtp_port' => '587',
        'from_email' => 'picturethis@cfox.co.za',
    ],
    'images' => [
        'retention_days' => 7,
        'min_images_per_user' => 3,
        'upload_max_size' => 20971520,
    ],
];
?>