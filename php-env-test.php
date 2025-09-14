<?php
// Test with server environment variables
putenv('APP_ENV=production');
putenv('DB_HOST=127.0.0.1');
putenv('DB_USER=cfoxcozj_picThisdb');
putenv('DB_PASS=LfUYHI%]{sjb5A*u');
putenv('DB_NAME=cfoxcozj_PictureThis');
putenv('PAYFAST_MERCHANT_ID=10041798');
putenv('PAYFAST_MERCHANT_KEY=vlnqle74tnkl7');
putenv('PAYFAST_PASSPHRASE=ThisIsATestFromPictureThis');
putenv('OPENROUTER_API_KEY=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');

echo "<h1>PHP Environment Test</h1>";
echo "<pre>";

echo "Environment Variables Set via PHP:\n";
echo "APP_ENV: " . getenv('APP_ENV') . "\n";
echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "DB_USER: " . getenv('DB_USER') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? 'SET' : 'NOT SET') . "\n";
echo "DB_NAME: " . getenv('DB_NAME') . "\n";
echo "PAYFAST_MERCHANT_ID: " . getenv('PAYFAST_MERCHANT_ID') . "\n";
echo "OPENROUTER_API_KEY: " . (getenv('OPENROUTER_API_KEY') ? 'SET' : 'NOT SET') . "\n";

echo "\nTesting Database Connection:\n";
try {
    $dsn = "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4";
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>