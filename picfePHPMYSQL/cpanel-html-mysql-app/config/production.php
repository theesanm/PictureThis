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
        'host' => '127.0.0.1', // Production database host
        'user' => 'cfoxcozj_picThisdb', // Production database user
        'pass' => '', // Set via .htaccess SetEnv or server environment
        'name' => 'cfoxcozj_PictureThis', // Production database name
    ],
    'payfast' => [
        'merchant_id' => '', // Set via .htaccess SetEnv or server environment
        'merchant_key' => '', // Set via .htaccess SetEnv or server environment
        'passphrase' => '', // Set via .htaccess SetEnv or server environment
        'env' => 'production',
    ],
    'openrouter' => [
        'api_key' => '', // Set via .htaccess SetEnv or server environment
        'app_url' => 'https://demo.cfox.co.za',
        'gemini_model' => 'google/gemini-2.5-flash-image-preview',
        'model' => 'openai/gpt-oss-20b:free',
    ],
    'email' => [
        'smtp_host' => 'metallurgix.aserv.co.za',
        'smtp_username' => 'cfoxcozj',
        'smtp_password' => '', // Set via .htaccess SetEnv or server environment
        'smtp_port' => '587',
        'from_email' => 'picturethis@cfox.co.za',
    ],
    'images' => [
        'retention_days' => 30, // Longer retention in production
        'min_images_per_user' => 5, // Keep more images in production
    ],
];
?>