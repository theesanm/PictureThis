<?php
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../utils/CSRF.php';

class PricingController {
    protected $CREDIT_PACKAGES = [
        'small' => ['credits' => 100, 'price' => 100.00, 'name' => '100 Credits'],
        'medium' => ['credits' => 250, 'price' => 200.00, 'name' => '250 Credits'],
        'large' => ['credits' => 375, 'price' => 250.00, 'name' => '375 Credits'],
        'premium' => ['credits' => 500, 'price' => 300.00, 'name' => '500 Credits']
    ];

    protected function getPayfastConfig() {
        // Keep these in sync with backend/.env or use env on server
        // For the PHP app we want PayFast return/cancel to point back to this backend
        $backend = null;

        // Attempt to build backend URL from incoming request headers first (useful when accessed via ngrok or proxy)
        $host = null;
        // Prefer X-Forwarded-Host (may contain comma-separated values)
        if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
            $host = trim($parts[0]);
        } elseif (!empty($_SERVER['HTTP_X_ORIGINAL_HOST'])) {
            $host = trim($_SERVER['HTTP_X_ORIGINAL_HOST']);
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $host = trim($_SERVER['HTTP_HOST']);
        } elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
            $parsed = parse_url(rtrim($_SERVER['HTTP_ORIGIN'], '/'));
            if (!empty($parsed['host'])) $host = $parsed['host'];
        } elseif (!empty($_SERVER['HTTP_REFERER'])) {
            $parsed = parse_url(rtrim($_SERVER['HTTP_REFERER'], '/'));
            if (!empty($parsed['host'])) $host = $parsed['host'];
        }

        if (!empty($host)) {
            $proto = 'http';
            if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
                || (!empty($_SERVER['HTTP_ORIGIN']) && strpos($_SERVER['HTTP_ORIGIN'], 'https://') === 0)
            ) {
                $proto = 'https';
            }
            $backend = $proto . '://' . $host;
        }

        // Fallback to environment variables if headers didn't provide a backend URL
        if (empty($backend)) {
            $backend = getenv('BACKEND_APP_URL') ?: (defined('APP_URL') ? APP_URL : null);
        }

        // Debug logging
        error_log('PayFast backend URL: ' . $backend);
        error_log('HTTP_HOST: ' . ($_SERVER['HTTP_HOST'] ?? 'none'));
        error_log('HTTP_ORIGIN: ' . ($_SERVER['HTTP_ORIGIN'] ?? 'none'));
        error_log('X-Forwarded-Host: ' . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'none'));

        return [
            'merchant_id' => PAYFAST_MERCHANT_ID ?: null,
            'merchant_key' => PAYFAST_MERCHANT_KEY ?: null,
            'passphrase' => PAYFAST_PASSPHRASE ?: null,
            'pfHost' => (PAYFAST_ENV === 'production') ? 'www.payfast.co.za' : 'sandbox.payfast.co.za',
            'return_url' => ($backend ?: '') . '/payment/popup/success',
            'cancel_url' => ($backend ?: '') . '/payment/popup/cancel',
            // notify must point to the backend so the PHP ITN handler receives it
            'notify_url' => ($backend ?: '') . '/api/credits/payfast/notify'
        ];
    }

    public function index() {
        // show pricing page
        include __DIR__ . '/../views/header.php';
        $packages = $this->CREDIT_PACKAGES;

        // If user logged in, fetch their recent transactions to show on pricing page
        $transactions = [];
        $totalAdded = 0; $totalConsumed = 0; $imagesGenerated = 0; $creditCostPerImage = 10;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            $userId = (int)$_SESSION['user']['id'];
            try {
                $pdo = get_db();
                $t = $pdo->prepare('SELECT ct.*, u.email, u.full_name FROM credit_transactions ct LEFT JOIN users u ON ct.user_id = u.id WHERE ct.user_id = ? ORDER BY ct.created_at DESC LIMIT 10');
                $t->execute([$userId]);
                $transactions = $t->fetchAll(PDO::FETCH_ASSOC);

                $s = $pdo->prepare('SELECT SUM(CASE WHEN amount>0 THEN amount ELSE 0 END) as added, SUM(CASE WHEN amount<0 THEN -amount ELSE 0 END) as consumed FROM credit_transactions WHERE user_id = ?');
                $s->execute([$userId]);
                $sum = $s->fetch(PDO::FETCH_ASSOC);
                $totalAdded = (int)($sum['added'] ?? 0);
                $totalConsumed = (int)($sum['consumed'] ?? 0);
            } catch (Exception $e) {
                error_log('Error fetching user transactions: ' . $e->getMessage());
            }
        }

        include __DIR__ . '/../views/pricing.php';
        include __DIR__ . '/../views/footer.php';
    }

    public function packages() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->CREDIT_PACKAGES]);
        exit;
    }

    // Initiate PayFast payment: returns JSON with payfastData and paymentUrl
    public function initiate() {
    if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }

        // Validate CSRF token
        if (!CSRF::validateRequest()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $packageId = $input['packageId'] ?? null;
        if (!$packageId || !isset($this->CREDIT_PACKAGES[$packageId])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid package selected']);
            exit;
        }

        $pdo = get_db();
        $userId = $_SESSION['user']['id'];
        $stmt = $pdo->prepare('SELECT email, full_name FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r || empty($r['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User email required']);
            exit;
        }

        $payfast = $this->buildPayfastPayload($packageId, $userId, $r['email'], $r['full_name'] ?: 'User');

        // Persist a pending payment record for reconciliation and idempotency
        try {
            $pdo->prepare('INSERT INTO payments (payment_id, user_id, package_id, credits, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())')
                ->execute([$payfast['data']['m_payment_id'], $userId, $packageId, $this->CREDIT_PACKAGES[$packageId]['credits'], $payfast['data']['amount'], 'pending']);
        } catch (Exception $e) {
            // If payments table missing or insert fails, continue but log
            error_log('Could not insert pending payment: ' . $e->getMessage());
        }

        $pfHost = $payfast['pfHost'];
        $paymentUrl = "https://{$pfHost}/eng/process";

        echo json_encode(['success' => true, 'data' => ['payfastData' => $payfast['data'], 'paymentUrl' => $paymentUrl, 'rawPayload' => $payfast['raw']]]);
        exit;
    }

    // PayFast ITN endpoint
    public function notify() {
        // PayFast sends x-www-form-urlencoded POST data. Use raw body to verify signature
        $raw = file_get_contents('php://input');
        parse_str($raw, $posted);

        // Basic logging for debug
        error_log('PayFast ITN received: ' . $raw);

        $cfg = $this->getPayfastConfig();

        // Compute expected signature using raw body (strip signature param)
        $expected = $this->generateSignatureFromRaw($raw, $cfg['passphrase']);
        $received = $posted['signature'] ?? '';

        if ($received !== $expected) {
            error_log('PayFast signature mismatch. Received: ' . $received . ' Expected: ' . $expected);
            // Always respond 200 to acknowledge receipt; log for manual review
            http_response_code(200);
            echo 'OK';
            return;
        }

        // Validate merchant
        if (!isset($posted['merchant_id']) || $posted['merchant_id'] != $cfg['merchant_id']) {
            error_log('PayFast merchant_id mismatch: ' . ($posted['merchant_id'] ?? 'none'));
            http_response_code(200);
            echo 'OK';
            return;
        }
    // Delegate core processing to an internal handler. This avoids self-HTTP calls deadlocking the PHP built-in server.
    $this->processItnPosted($posted);

    // Acknowledge receipt
    http_response_code(200);
    echo 'OK';
    }

    // Test ITN sender (dev only)
    public function testItn() {
        // Accept packageId and userId via POST JSON
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $packageId = $input['packageId'] ?? 'small';
        $userId = isset($input['userId']) ? (int)$input['userId'] : 1;

        if (!isset($this->CREDIT_PACKAGES[$packageId])) {
            echo json_encode(['success' => false, 'message' => 'Invalid package']); exit;
        }

        $pkg = $this->CREDIT_PACKAGES[$packageId];
        $cfg = $this->getPayfastConfig();
        $testData = [
            'merchant_id' => $cfg['merchant_id'],
            'merchant_key' => $cfg['merchant_key'],
            'm_payment_id' => "credit_{$userId}_" . time(),
            'amount' => number_format($pkg['price'], 2, '.', ''),
            'item_name' => $pkg['name'],
            'payment_status' => 'COMPLETE',
            'custom_int1' => (string)$pkg['credits']
        ];

        // build raw payload and signature
        // For signature, use the same order as testData array
        $parts = [];
        foreach ($testData as $k => $v) {
            $encoded = rawurlencode(trim($v));
            $encoded = str_replace('%20', '+', $encoded);
            $parts[] = "{$k}={$encoded}";
        }
        $raw = implode('&', $parts);
        if (!empty($cfg['passphrase'])) {
            $passEncoded = rawurlencode(trim($cfg['passphrase']));
            $passEncoded = str_replace('%20', '+', $passEncoded);
            $raw .= '&passphrase=' . $passEncoded;
        }
        $testData['signature'] = md5($raw);

        // If notify_url points back at this app (localhost / APP_URL), call internal handler to avoid deadlocks
        $notify = $cfg['notify_url'];
        $res = null;
        $isLocal = false;
        if (!empty($notify)) {
            $lower = strtolower($notify);
            if (strpos($lower, 'localhost') !== false || strpos($lower, '127.0.0.1') !== false || (defined('APP_URL') && strpos($lower, strtolower(APP_URL)) !== false)) {
                $isLocal = true;
            }
        }

        if ($isLocal) {
            // Call internal handler directly
            $this->processItnPosted($testData);
            $res = 'internal';
        } else {
            $post = http_build_query($testData);
            $opts = ['http' => ['method' => 'POST', 'header' => "Content-type: application/x-www-form-urlencoded\r\n", 'content' => $post, 'timeout' => 5]];
            $context = stream_context_create($opts);
            $res = @file_get_contents($notify, false, $context);
        }

        echo json_encode(['success' => true, 'sent' => $testData, 'response' => $res]);
    }

    // Render a simple payment success page after PayFast return
    public function success() {
        // Expects GET params: payment_id, user_id, package_id
        include __DIR__ . '/../views/payment_success.php';
    }

    // Render a simple payment cancelled page
    public function cancelled() {
        include __DIR__ . '/../views/payment_cancelled.php';
    }

    // Iframe-specific success handler for modal payments
    public function iframeSuccess() {
        // Expects GET params: payment_id, user_id, package_id
        include __DIR__ . '/../views/payment_iframe_success.php';
    }

        // Iframe-specific cancel handler for modal payments
    public function iframeCancel() {
        // Expects GET params: payment_id, user_id, package_id
        include __DIR__ . '/../views/payment_iframe_cancel.php';
    }

    // Popup-specific success handler for modal payments
    public function popupSuccess() {
        // Expects GET params: payment_id, user_id, package_id
        include __DIR__ . '/../views/payment_popup_success.php';
    }

    // Popup-specific cancel handler for modal payments
    public function popupCancel() {
        // Expects GET params: payment_id, user_id, package_id
        include __DIR__ . '/../views/payment_popup_cancel.php';
    }

    // Pollable endpoint to check whether a payment has been processed and return user credits
    public function paymentStatus() {
        header('Content-Type: application/json');
        $paymentId = $_GET['payment_id'] ?? $_POST['payment_id'] ?? null;
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($_POST['user_id']) ? (int)$_POST['user_id'] : null);

        if (!$userId || !$paymentId) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        $pdo = get_db();
        $status = 'pending';
        try {
            // Check payments table if exists
            $q = $pdo->prepare('SELECT status FROM payments WHERE payment_id = ? LIMIT 1');
            $q->execute([$paymentId]);
            $r = $q->fetch(PDO::FETCH_ASSOC);
            if ($r && isset($r['status'])) $status = $r['status'];

            // Also check credit_transactions for presence
            $t = $pdo->prepare('SELECT SUM(amount) as total_credits FROM credit_transactions WHERE payment_id = ? LIMIT 1');
            $t->execute([$paymentId]);
            $tr = $t->fetch(PDO::FETCH_ASSOC);
            $applied = ($tr && $tr['total_credits']) ? (int)$tr['total_credits'] : 0;

            $u = $pdo->prepare('SELECT credits FROM users WHERE id = ? LIMIT 1');
            $u->execute([$userId]);
            $user = $u->fetch(PDO::FETCH_ASSOC);
            $currentCredits = $user ? (int)$user['credits'] : null;

            echo json_encode(['success' => true, 'status' => $status, 'applied' => $applied, 'credits' => $currentCredits]);
            return;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }
    }

    // Internal helper to process an ITN-like posted array. Extracted to avoid deadlocks when calling our own notify endpoint.
    protected function processItnPosted(array $posted) {
        // Validate required fields
        if (empty($posted['m_payment_id']) || empty($posted['custom_int1'])) {
            error_log('PayFast ITN missing fields');
            return;
        }

        $credits = (int)$posted['custom_int1'];
        if ($credits <= 0) {
            error_log('Invalid credits in ITN: ' . $posted['custom_int1']);
            return;
        }

        // Map credits to package id
        $packageId = null;
        foreach ($this->CREDIT_PACKAGES as $id => $pkg) {
            if ($pkg['credits'] === $credits) { $packageId = $id; break; }
        }

        if (!$packageId) {
            error_log('No package for credits: ' . $credits);
            return;
        }

        // Parse m_payment_id for user id (format credit_{userId}_{timestamp})
        $parts = explode('_', $posted['m_payment_id']);
        if (count($parts) < 3 || $parts[0] !== 'credit') {
            error_log('Invalid m_payment_id format: ' . $posted['m_payment_id']);
            return;
        }

        $userId = (int)$parts[1];
        if ($userId <= 0) {
            error_log('Invalid user id in payment id: ' . $parts[1]);
            return;
        }

        // Optionally validate amount
        $expectedAmount = number_format($this->CREDIT_PACKAGES[$packageId]['price'], 2, '.', '');
        $amountGross = $posted['amount_gross'] ?? $posted['amount'] ?? null;
        if ($amountGross !== null && $amountGross !== $expectedAmount) {
            error_log('Amount mismatch: received ' . $amountGross . ' expected ' . $expectedAmount);
            return;
        }

        // Idempotency check and apply
        $pdo = get_db();
        try {
            $q = $pdo->prepare('SELECT id FROM credit_transactions WHERE payment_id = ? LIMIT 1');
            $q->execute([$posted['m_payment_id']]);
            if ($q->fetch()) {
                error_log('Duplicate ITN received for ' . $posted['m_payment_id']);
                return;
            }

            if (isset($posted['payment_status']) && strtoupper($posted['payment_status']) === 'COMPLETE') {
                // Update user credits with diagnostics
                try {
                    $upd = $pdo->prepare('UPDATE users SET credits = COALESCE(credits,0) + ? WHERE id = ?');
                    $ok = $upd->execute([$credits, $userId]);
                    $rows = $upd->rowCount();
                    error_log("ITN: UPDATE users set credits for user {$userId} returned execute=" . json_encode($ok) . " rowCount={$rows}");

                    if ($rows === 0) {
                        // No rows updated - maybe user missing. Log full user lookup for debugging.
                        $u = $pdo->prepare('SELECT id, email, credits FROM users WHERE id = ? LIMIT 1');
                        $u->execute([$userId]);
                        $userRow = $u->fetch(PDO::FETCH_ASSOC);
                        error_log('ITN: No user row updated. User lookup: ' . json_encode($userRow));
                    }

                } catch (Exception $e) {
                    error_log('ITN: Error updating user credits: ' . $e->getMessage());
                    // Continue to try inserting transaction record for auditing
                }

                // Insert transaction log
                try {
                    $ins = $pdo->prepare('INSERT INTO credit_transactions (user_id, amount, transaction_type, stripe_payment_id, description, payment_id, created_at) VALUES (?, ?, ?, NULL, ?, ?, NOW())');
                    $desc = "PayFast purchase: {$credits} credits ({$packageId})";
                    $insOk = $ins->execute([$userId, $credits, 'purchase', $desc, $posted['m_payment_id']]);
                    $insId = $pdo->lastInsertId();
                    error_log("ITN: Inserted credit_transactions user={$userId} credits={$credits} ok=" . json_encode($insOk) . " insertId={$insId}");
                } catch (Exception $e) {
                    error_log('ITN: Error inserting credit_transactions: ' . $e->getMessage());
                }

                // Mark pending payments if table exists
                try {
                    $res = $pdo->prepare('UPDATE payments SET status = ?, processed_at = NOW() WHERE payment_id = ?')
                        ->execute(['processed', $posted['m_payment_id']]);
                    error_log('ITN: Updated payments table for ' . $posted['m_payment_id'] . ' result=' . json_encode($res));
                } catch (Exception $e) {
                    error_log('ITN: Error updating payments table: ' . $e->getMessage());
                }
            } else {
                error_log('Payment not complete. Status: ' . ($posted['payment_status'] ?? 'unknown'));
            }
        } catch (Exception $e) {
            error_log('Error processing ITN: ' . $e->getMessage());
        }
    }

    protected function buildPayfastPayload($packageId, $userId, $userEmail, $userName = 'User') {
        $cfg = $this->getPayfastConfig();
        $package = $this->CREDIT_PACKAGES[$packageId];
        $paymentId = "credit_{$userId}_" . time();

        $data = [
            'merchant_id' => $cfg['merchant_id'],
            'merchant_key' => $cfg['merchant_key'],
            'return_url' => $cfg['return_url'] . "?payment_id={$paymentId}&user_id={$userId}&package_id={$packageId}",
            'cancel_url' => $cfg['cancel_url'] . "?payment_id={$paymentId}&user_id={$userId}&package_id={$packageId}",
            'notify_url' => $cfg['notify_url'],
            'm_payment_id' => $paymentId,
            'amount' => number_format($package['price'], 2, '.', ''),
            'item_name' => $package['name'],
            'custom_int1' => (string)$package['credits']
        ];

        // Build signature using PayFast rules: specific field order and URL-encoding with + for spaces
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

        return ['data' => $data, 'raw' => $raw, 'pfHost' => $cfg['pfHost']];
    }

    // Verify signature when given the raw urlencoded POST body from PayFast
    protected function generateSignatureFromRaw(string $rawBody, ?string $passphrase = null): string {
        // Remove any signature param if present
        $withoutSig = preg_replace('/(^|&)signature=[^&]*(&|$)/i', '', $rawBody);
        $withoutSig = rtrim($withoutSig, '&');

        if (!empty($passphrase)) {
            $passEncoded = rawurlencode(trim($passphrase));
            $passEncoded = str_replace('%20', '+', $passEncoded);
            $withoutSig .= '&passphrase=' . $passEncoded;
        }

        return md5($withoutSig);
    }
}
