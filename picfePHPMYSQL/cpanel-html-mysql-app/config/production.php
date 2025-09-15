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
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'user' => getenv('DB_USER') ?: 'cfoxcozj_picThisdb',
        'pass' => getenv('DB_PASS') ?: '',
        'name' => getenv('DB_NAME') ?: 'cfoxcozj_PictureThis',
    ],
    'payfast' => [
        'merchant_id' => getenv('PAYFAST_MERCHANT_ID') ?: '',
        'merchant_key' => getenv('PAYFAST_MERCHANT_KEY') ?: '',
        'passphrase' => getenv('PAYFAST_PASSPHRASE') ?: '',
        'env' => getenv('PAYFAST_ENV') ?: 'production',
    ],
    'openrouter' => [
        'api_key' => getenv('OPENROUTER_API_KEY') ?: '',
        'app_url' => getenv('OPENROUTER_APP_URL') ?: 'https://demo.cfox.co.za',
        'gemini_model' => getenv('GEMINI_MODEL') ?: 'google/gemini-2.5-flash-image-preview',
        'model' => getenv('DEFAULT_MODEL') ?: 'openai/gpt-oss-20b:free',
    ],
    'email' => [
        'smtp_host' => getenv('SMTP_HOST') ?: 'metallurgix.aserv.co.za',
        'smtp_username' => getenv('SMTP_USER') ?: 'cfoxcozj',
        'smtp_password' => getenv('SMTP_PASS') ?: '',
        'smtp_port' => getenv('SMTP_PORT') ?: '587',
        'from_email' => getenv('FROM_EMAIL') ?: 'picturethis@cfox.co.za',
    ],
    'images' => [
        'retention_days' => getenv('IMAGE_RETENTION_DAYS') ?: 7,
        'min_images_per_user' => getenv('MIN_IMAGES_PER_USER') ?: 3,
        'upload_max_size' => getenv('UPLOAD_MAX_SIZE') ?: 20971520,
    ],
];
?>