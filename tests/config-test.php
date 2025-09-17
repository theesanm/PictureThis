<?php
// Quick test to check if basic PHP and database work
echo "<h1>🔧 Quick Configuration Test</h1>";
echo "<pre>";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n\n";

echo "Environment Variables Check:\n";
$required_vars = [
    'APP_ENV',
    'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME',
    'PAYFAST_MERCHANT_ID', 'PAYFAST_MERCHANT_KEY', 'PAYFAST_PASSPHRASE',
    'OPENROUTER_API_KEY'
];

foreach ($required_vars as $var) {
    $value = getenv($var);
    $status = empty($value) ? '❌ EMPTY/MISSING' : '✅ SET';
    echo "- $var: $status\n";
}

echo "\nDatabase Connection Test:\n";
try {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $user = getenv('DB_USER') ?: '';
    $pass = getenv('DB_PASS') ?: '';
    $name = getenv('DB_NAME') ?: '';

    if (empty($user) || empty($pass) || empty($name)) {
        echo "❌ Database credentials not set in environment\n";
    } else {
        $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "✅ Database connection successful\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n</pre>";
echo "<h2>📋 Configuration Status</h2>";
echo "<p>If you see ❌ EMPTY/MISSING above, you need to set those values in .htaccess</p>";
?>