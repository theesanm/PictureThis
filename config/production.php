<?php
// Production Configuration
// This file contains all production-specific settings
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
        'pass' => 'LfUYHI%]{sjb5A*u', // Your production database password
        'name' => 'cfoxcozj_PictureThis', // Your production database name
    ],
    'payfast' => [
        'merchant_id' => '10041798', // Set in cPanel env vars
        'merchant_key' => 'vlnqle74tnkl7', // Set in cPanel env vars
        'passphrase' => 'ThisIsATestFromPictureThis', // Set in cPanel env vars
        'env' => 'production',
    ],
    'openrouter' => [
        'api_key' => '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', // Set in cPanel env vars
        'app_url' => 'https://demo.cfox.co.za',
        'gemini_model' => 'google/gemini-2.5-flash-image-preview',
        'model' => '@preset/picture-this-agent',
    ],
    'email' => [
        'smtp_host' => 'metallurgix.aserv.co.za',
        'smtp_username' => 'cfoxcozj',
        'smtp_password' => 'Runx141kw007@', // Set in cPanel env vars
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