<?php
// Dev helper: simulate a buy flow without needing a logged-in session.

require_once __DIR__ . '/../src/controllers/PricingController.php';
require_once __DIR__ . '/../src/lib/db.php';

$pkg = $argv[1] ?? 'small';
$userId = isset($argv[2]) ? (int)$argv[2] : 3;

$CREDIT_PACKAGES = [
    'small' => ['credits' => 50, 'price' => 200.00, 'name' => '50 Credits'],
    'medium' => ['credits' => 75, 'price' => 250.00, 'name' => '75 Credits (10% off)'],
    'large' => ['credits' => 125, 'price' => 300.00, 'name' => '125 Credits (20% off)'],
    'premium' => ['credits' => 200, 'price' => 350.00, 'name' => '200 Credits (30% off)']
];

if (!isset($CREDIT_PACKAGES[$pkg])) {
    echo json_encode(['success' => false, 'message' => 'Invalid package']);
    exit(1);
}

// Build payload locally (mirror PricingController::buildPayfastPayload logic)
$pdo = get_db();

$cfg = [
    'merchant_id' => getenv('PAYFAST_MERCHANT_ID') ?: null,
    'merchant_key' => getenv('PAYFAST_MERCHANT_KEY') ?: null,
    'passphrase' => getenv('PAYFAST_PASSPHRASE') ?: null,
    'pfHost' => (getenv('NODE_ENV') === 'production') ? 'www.payfast.co.za' : 'sandbox.payfast.co.za',
    'return_url' => defined('APP_URL') ? APP_URL . '/payment/success' : 'http://localhost:8000/payment/success',
    'cancel_url' => defined('APP_URL') ? APP_URL . '/payment/cancelled' : 'http://localhost:8000/payment/cancelled',
    'notify_url' => defined('APP_URL') ? APP_URL . '/api/credits/payfast/notify' : 'http://localhost:8000/api/credits/payfast/notify'
];

$paymentId = "credit_{$userId}_" . time();
$amount = number_format($CREDIT_PACKAGES[$pkg]['price'], 2, '.', '');
$data = [
    'merchant_id' => $cfg['merchant_id'],
    'merchant_key' => $cfg['merchant_key'],
    'return_url' => $cfg['return_url'] . "?payment_id={$paymentId}&user_id={$userId}&package_id={$pkg}",
    'cancel_url' => $cfg['cancel_url'],
    'notify_url' => $cfg['notify_url'],
    'm_payment_id' => $paymentId,
    'amount' => $amount,
    'item_name' => $CREDIT_PACKAGES[$pkg]['name'],
    'custom_int1' => (string)$CREDIT_PACKAGES[$pkg]['credits']
];

// signature
$fieldOrder = ['merchant_id','merchant_key','return_url','cancel_url','notify_url','m_payment_id','amount','item_name','custom_int1'];
$parts = [];
foreach ($fieldOrder as $k) {
    if (!isset($data[$k]) || $data[$k] === '') continue;
    $v = trim((string)$data[$k]);
    $v = rawurlencode($v);
    $v = str_replace('%20', '+', $v);
    $parts[] = "{$k}={$v}";
}
$raw = implode('&', $parts);
if (!empty($cfg['passphrase'])) {
    $raw .= '&passphrase=' . str_replace('%20', '+', rawurlencode($cfg['passphrase']));
}
$signature = md5($raw);
$data['signature'] = $signature;

$payfast = ['data' => $data, 'raw' => $raw, 'pfHost' => $cfg['pfHost']];

try {
    $pdo->prepare('INSERT INTO payments (payment_id, user_id, package_id, credits, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())')
        ->execute([$payfast['data']['m_payment_id'], $userId, $pkg, $CREDIT_PACKAGES[$pkg]['credits'], $payfast['data']['amount'], 'pending']);
} catch (Exception $e) {
    // ignore
}

$pfHost = $payfast['pfHost'];
$paymentUrl = "https://{$pfHost}/eng/process";

echo json_encode(['success' => true, 'payfast' => $payfast, 'paymentUrl' => $paymentUrl], JSON_PRETTY_PRINT);
