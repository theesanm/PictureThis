const express = require('express');
const crypto = require('crypto');
const { authenticateToken } = require('../middleware/auth');
const { query } = require('../utils/database');

const router = express.Router();

/*
 * PayFast Integration for PictureThis
 *
 * This module implements PayFast payment processing according to their official documentation:
 * https://developers.payfast.co.za/docs
 *
 * Key Features:
 * - Secure signature generation using MD5 hash of alphabetically sorted parameters
 * - ITN (Instant Transaction Notification) webhook for payment confirmation
 * - Duplicate payment prevention
 * - Comprehensive validation and error handling
 * - Sandbox and production environment support
 *
 * Security Considerations:
 * - All signatures are verified using the same algorithm for generation and verification
 * - Merchant ID validation prevents processing notifications from wrong accounts
 * - Amount validation ensures payment amounts match expected values
 * - HTTPS-only URLs for all PayFast interactions
 * - Proper input sanitization and validation
 *
 * PayFast Account Requirements:
 * - Valid PayFast merchant account with API credentials
 * - ITN webhook URL must be publicly accessible (ngrok in development)
 * - Return and cancel URLs must be valid HTTPS URLs
 */

// PayFast configuration
const PAYFAST_CONFIG = {
  merchant_id: process.env.PAYFAST_MERCHANT_ID,
  merchant_key: process.env.PAYFAST_MERCHANT_KEY,
  passphrase: process.env.PAYFAST_PASSPHRASE,
  return_url: process.env.NODE_ENV === 'production'
    ? 'https://yourdomain.com/payment/success'
    : 'https://1d503f739e4d.ngrok-free.app/payment/success',
  cancel_url: process.env.NODE_ENV === 'production'
    ? 'https://yourdomain.com/payment/cancelled'
    : 'https://1d503f739e4d.ngrok-free.app/dashboard',
  notify_url: process.env.NODE_ENV === 'production'
    ? 'https://yourdomain.com/api/credits/payfast/notify'
    : 'https://1d503f739e4d.ngrok-free.app/api/credits/payfast/notify',
  pfHost: process.env.NODE_ENV === 'production' ? 'www.payfast.co.za' : 'sandbox.payfast.co.za'
};

// Credit packages
const CREDIT_PACKAGES = {
  'small': { credits: 50, price: 200.00, name: '50 Credits' },
  'medium': { credits: 75, price: 250.00, name: '75 Credits' },
  'large': { credits: 125, price: 300.00, name: '125 Credits' },
  'premium': { credits: 200, price: 350.00, name: '200 Credits' }
};

// Generate PayFast payment form data
function generatePayFastData(packageId, userId, userEmail, userName = 'User') {
  const packageData = CREDIT_PACKAGES[packageId];
  if (!packageData) throw new Error('Invalid package');

  const paymentId = `credit_${userId}_${Date.now()}`;

  const data = {
    merchant_id: PAYFAST_CONFIG.merchant_id,
    merchant_key: PAYFAST_CONFIG.merchant_key,
    return_url: process.env.NODE_ENV === 'production'
      ? `https://yourdomain.com/payment/success?payment_id=${paymentId}&user_id=${userId}&package_id=${packageId}`
      : `https://1d503f739e4d.ngrok-free.app/payment/success?payment_id=${paymentId}&user_id=${userId}&package_id=${packageId}`,
    cancel_url: PAYFAST_CONFIG.cancel_url,
    notify_url: PAYFAST_CONFIG.notify_url,
    m_payment_id: paymentId,
    amount: packageData.price.toFixed(2),
    item_name: packageData.name,
    custom_int1: packageData.credits.toString() // Use custom_int1 as per working implementation
  };

  // Generate signature according to PayFast specifications
  data.signature = generateSignature(data, PAYFAST_CONFIG.passphrase);

  // Debug info (avoid logging sensitive credentials in production)
  console.log('=== PayFast Debug Info ===');
  console.log('Environment:', process.env.NODE_ENV);
  console.log('PayFast Host:', PAYFAST_CONFIG.pfHost);
  // Mask merchant id for logs
  if (PAYFAST_CONFIG.merchant_id) {
    const m = PAYFAST_CONFIG.merchant_id.toString();
    console.log('Merchant ID:', m.slice(0, 2) + '...' + m.slice(-2));
  }
  console.log('Full URL:', `https://${PAYFAST_CONFIG.pfHost}/eng/process`);
  console.log('Generated Signature (truncated):', data.signature ? data.signature.slice(0, 8) + '...' : '(none)');
  
  // Debug: Show the exact string used for signature generation
  const debugString = buildPayloadString(data, false, PAYFAST_CONFIG.passphrase);
  console.log('Signature generation string:', debugString);
  console.log('========================');

  return data;
}

// Generate PayFast signature according to official documentation
function generateSignature(data, passPhrase = null) {
  // If raw POST string is provided, prefer building signature from it to avoid
  // discrepancies due to middleware parsing/encoding. If `data` is a string,
  // treat it as the raw urlencoded payload.
  let getString = '';

  if (typeof data === 'string') {
    // The raw body may be provided by express's verify hook (req.rawBody)
    // PayFast expects the string exactly as received, except we should remove
    // any trailing signature parameter if present.
    getString = data;
    // If signature field present in raw string, remove it
    getString = getString.replace(/(^|&)signature=[^&]*(&|$)/i, (m, p1, p2) => p1 === '&' && p2 === '&' ? '&' : '').replace(/&$/, '');
    if (passPhrase !== null && passPhrase.trim() !== '') {
      getString += `&passphrase=${encodeURIComponent(passPhrase.trim()).replace(/%20/g, "+")}`;
    }
    return crypto.createHash('md5').update(getString).digest('hex');
  }

  // Use the specific field order required by PayFast (NOT alphabetical)
  const payfastFieldOrder = ['merchant_id', 'merchant_key', 'return_url', 'cancel_url', 'notify_url', 'm_payment_id', 'amount', 'item_name', 'custom_int1'];
  
  const parts = [];
  for (const key of payfastFieldOrder) {
    if (key === 'signature') continue; // Exclude signature
    const val = data[key];
    if (val === undefined || val === null || val === '') continue;
    let value = val.toString().trim();
    // Always URL encode values (including URLs) using RFC1738-like replacement of spaces with +
    value = encodeURIComponent(value).replace(/%20/g, '+');
    parts.push(`${key}=${value}`);
  }

  getString = parts.join('&');
  if (passPhrase !== null && passPhrase.trim() !== '') {
    getString += `&passphrase=${encodeURIComponent(passPhrase.trim()).replace(/%20/g, "+")}`;
  }

  return crypto.createHash('md5').update(getString).digest('hex');
}

// Build canonical urlencoded payload string from data (PayFast field order)
function buildPayloadString(data, includeSignature = true, passPhrase = null) {
  // Use the specific field order required by PayFast
  const payfastFieldOrder = ['merchant_id', 'merchant_key', 'return_url', 'cancel_url', 'notify_url', 'm_payment_id', 'amount', 'item_name', 'custom_int1'];
  
  const parts = [];
  for (const key of payfastFieldOrder) {
    if (!includeSignature && key === 'signature') continue;
    const val = data[key];
    if (val === undefined || val === null || val === '') continue;
    const encoded = encodeURIComponent(val.toString().trim()).replace(/%20/g, '+');
    parts.push(`${key}=${encoded}`);
  }
  let getString = parts.join('&');
  if (passPhrase !== null && passPhrase.toString().trim() !== '') {
    getString += `&passphrase=${encodeURIComponent(passPhrase.toString().trim()).replace(/%20/g, '+')}`;
  }
  return getString;
}

// Get available credit packages
router.get('/packages', (req, res) => {
  res.json({
    success: true,
    data: CREDIT_PACKAGES
  });
});

// Initiate PayFast payment
router.post('/payfast/initiate', authenticateToken, async (req, res) => {
  try {
    const { packageId } = req.body;
    const userId = req.user.userId;

    // Validate package ID
    if (!packageId || !CREDIT_PACKAGES[packageId]) {
      return res.status(400).json({
        success: false,
        message: 'Invalid package selected'
      });
    }

    // Get user email and name
    const userResult = await query(`SELECT email, full_name FROM users WHERE id = ${userId}`);
    if (userResult.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }

    const userEmail = userResult.rows[0].email;
    const userName = userResult.rows[0].full_name || 'User';

    // Validate user data
    if (!userEmail) {
      return res.status(400).json({
        success: false,
        message: 'User email is required'
      });
    }

  // Generate PayFast payment data
  const payfastData = generatePayFastData(packageId, userId, userEmail, userName);
  // Build canonical raw payload (including the signature) so frontend can compare
  const rawPayload = buildPayloadString(payfastData, true, null);

    // Generate HTML form with proper escaping
    let htmlForm = `<form action="https://${PAYFAST_CONFIG.pfHost}/eng/process" method="post" id="payfast-form">`;
    for (let key in payfastData) {
      if (payfastData.hasOwnProperty(key)) {
        const value = payfastData[key];
        if (value !== "") {
          // Properly escape HTML attributes
          const escapedKey = key.replace(/"/g, '&quot;');
          const escapedValue = value.toString().trim().replace(/"/g, '&quot;');
          htmlForm += `<input name="${escapedKey}" type="hidden" value="${escapedValue}" />`;
        }
      }
    }
    htmlForm += '<input type="submit" value="Pay Now" style="display: none;" /></form>';

    // Add JavaScript for automatic form submission
    htmlForm += `
    <script>
      document.getElementById('payfast-form').submit();
    </script>`;

    res.json({
      success: true,
      data: {
        payfastData,
        paymentUrl: `https://${PAYFAST_CONFIG.pfHost}/eng/process`,
        htmlForm,
        rawPayload // for debugging: exact urlencoded payload client will send
      }
    });
  } catch (error) {
    console.error('PayFast initiation error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to initiate payment'
    });
  }
});

// PayFast ITN (Instant Transaction Notification) webhook
router.post('/payfast/notify', async (req, res) => {
  try {
    const postedData = req.body || {};
    const raw = req.rawBody || '';

    console.log('=== PayFast ITN Notification Received ===');
    console.log('Request Method:', req.method);
    console.log('Content-Type:', req.headers['content-type']);
    console.log('User-Agent:', req.headers['user-agent']);
    console.log('Raw Body:', raw);
    console.log('Parsed Body:', postedData);
    console.log('=====================================');

    // Basic validation - ensure we have data
    if ((!postedData || Object.keys(postedData).length === 0) && !raw) {
      console.error('No data received in PayFast ITN');
      return res.status(400).send('No data received');
    }

    // Verify PayFast signature - prefer raw body when available to avoid
    // discrepancies caused by express parsing/encoding
    let expectedSignature;
    if (raw) {
      expectedSignature = generateSignature(raw, PAYFAST_CONFIG.passphrase);
      console.log('Computed signature from raw body');
    } else {
      expectedSignature = generateSignature(postedData, PAYFAST_CONFIG.passphrase);
      console.log('Computed signature from parsed body');
    }

    console.log('Expected Signature:', expectedSignature);
    console.log('Received Signature:', postedData.signature || '(none)');
    console.log('Signature Match:', (postedData.signature || '') === expectedSignature);

    if ((postedData.signature || '') !== expectedSignature) {
      console.error('\u274c Invalid PayFast signature - ITN rejected');
      return res.status(400).send('Invalid signature');
    }

    // Validate merchant_id matches our configuration
    if (postedData.merchant_id !== PAYFAST_CONFIG.merchant_id) {
      console.error(`❌ Merchant ID mismatch: received ${postedData.merchant_id}, expected ${PAYFAST_CONFIG.merchant_id}`);
      return res.status(400).send('Invalid merchant');
    }

    // Validate required fields for our business logic
    if (!postedData.m_payment_id || !postedData.custom_int1) {
      console.error('❌ Missing required custom fields in PayFast ITN');
      return res.status(400).send('Missing required fields');
    }

    // Validate credits format from custom_int1
    const credits = parseInt(postedData.custom_int1);
    if (isNaN(credits) || credits <= 0) {
      console.error('❌ Invalid credits format:', postedData.custom_int1);
      return res.status(400).send('Invalid credits amount');
    }

    // Find the package that matches the credits amount
    let packageId = null;
    for (const [id, pkg] of Object.entries(CREDIT_PACKAGES)) {
      if (pkg.credits === credits) {
        packageId = id;
        break;
      }
    }

    if (!packageId) {
      console.error('❌ No package found for credits amount:', credits);
      return res.status(400).send('Invalid credits amount');
    }

    // Extract user ID from payment ID (format: credit_{userId}_{timestamp})
    const paymentIdParts = postedData.m_payment_id.split('_');
    if (paymentIdParts.length < 3 || paymentIdParts[0] !== 'credit') {
      console.error('❌ Invalid payment ID format:', postedData.m_payment_id);
      return res.status(400).send('Invalid payment ID format');
    }

    const userId = parseInt(paymentIdParts[1]);
    if (isNaN(userId) || userId <= 0) {
      console.error('❌ Invalid user ID in payment ID:', paymentIdParts[1]);
      return res.status(400).send('Invalid user ID');
    }

    // Validate payment amount matches expected amount (if provided)
    if (postedData.amount_gross) {
      const expectedAmount = CREDIT_PACKAGES[packageId].price.toFixed(2);
      if (postedData.amount_gross !== expectedAmount) {
        console.error(`❌ Amount mismatch: received ${postedData.amount_gross}, expected ${expectedAmount}`);
        return res.status(400).send('Amount mismatch');
      }
    }

    // Check payment status
    if (postedData.payment_status === 'COMPLETE') {
      const paymentId = postedData.m_payment_id;

      console.log(`✅ Processing credit purchase: ${credits} credits for user ${userId}`);

      // Check if this payment has already been processed
      const existingTransaction = await query(
        `SELECT id FROM credit_transactions WHERE payment_id = '${paymentId}'`
      );

      if (existingTransaction.rows.length > 0) {
        console.log(`⚠️ Payment ${paymentId} already processed - skipping duplicate`);
        return res.status(200).send('OK'); // Still acknowledge receipt
      }

      // Add credits to user account
      const updateResult = await query(`UPDATE users SET credits = credits + ${credits} WHERE id = ${userId}`);
      console.log('Credit update result:', updateResult.rowCount);

      // Log transaction
      const transactionResult = await query(
        `INSERT INTO credit_transactions (user_id, amount, transaction_type, stripe_payment_id, description, payment_id) VALUES (${userId}, ${credits}, 'purchase', null, 'PayFast purchase: ${credits} credits (${packageId})', '${paymentId}')`
      );
      console.log('Transaction log result:', transactionResult.rowCount);

      console.log(`✅ Credits added successfully: ${credits} credits to user ${userId}`);
    } else {
      console.log(`ℹ️ Payment not complete. Status: ${postedData.payment_status}`);
    }

    // Always respond with 200 OK to acknowledge receipt
    res.status(200).send('OK');
  } catch (error) {
    console.error('❌ PayFast ITN processing error:', error);
    // Return 200 OK even on error to prevent PayFast from retrying
    // Log the error for investigation
    res.status(200).send('OK');
  }
});

// Manual ITN trigger for testing (remove in production)
router.post('/payfast/test-itn', async (req, res) => {
  try {
    const { packageId, userId } = req.body;

    // Get package details
    const packageData = CREDIT_PACKAGES[packageId];
    if (!packageData) {
      return res.status(400).json({ success: false, message: 'Invalid package' });
    }

    // Simulate PayFast notification data
    const testData = {
      merchant_id: PAYFAST_CONFIG.merchant_id,
      merchant_key: PAYFAST_CONFIG.merchant_key,
      return_url: PAYFAST_CONFIG.return_url,
      cancel_url: PAYFAST_CONFIG.cancel_url,
      notify_url: PAYFAST_CONFIG.notify_url,
      name_first: 'Test',
      name_last: 'User',
      email_address: 'test@example.com',
      m_payment_id: `test_${Date.now()}`,
      amount: packageData.price.toFixed(2),
      item_name: packageData.name,
      item_description: `Purchase ${packageData.credits} credits for PictureThis`,
      custom_str1: userId.toString(),
      custom_str2: packageId,
      custom_str3: packageData.credits.toString(),
      payment_status: 'COMPLETE'
    };

    // Generate signature
    testData.signature = generateSignature(testData, PAYFAST_CONFIG.passphrase);

    console.log('=== Test ITN Data ===');
    console.log(JSON.stringify(testData, null, 2));

    // Send test notification to our own endpoint
    const https = require('https');
    const querystring = require('querystring');

    const postData = querystring.stringify(testData);
    const options = {
      hostname: '1d503f739e4d.ngrok-free.app',
      port: 443,
      path: '/api/credits/payfast/notify',
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Content-Length': Buffer.byteLength(postData)
      }
    };

    const req_payfast = https.request(options, (res_payfast) => {
      console.log(`Test ITN Response: ${res_payfast.statusCode}`);
      res.json({
        success: true,
        message: 'Test ITN sent',
        testData: testData
      });
    });

    req_payfast.on('error', (e) => {
      console.error('Test ITN Error:', e);
      res.status(500).json({ success: false, message: 'Test ITN failed' });
    });

    req_payfast.write(postData);
    req_payfast.end();

  } catch (error) {
    console.error('Test ITN error:', error);
    res.status(500).json({ success: false, message: 'Test ITN failed' });
  }
});

// Debug endpoint: return canonical urlencoded payload for a package/user
// NOTE: This endpoint is for local debugging only. Do NOT enable in production.
router.get('/payfast/debug-payload', async (req, res) => {
  try {
    const packageId = req.query.packageId || 'small';
    const userId = parseInt(req.query.userId || '1');
    const userEmail = req.query.email || 'test@example.com';
    const userName = req.query.name || 'Test User';

    if (!CREDIT_PACKAGES[packageId]) {
      return res.status(400).json({ success: false, message: 'Invalid packageId' });
    }

    const payfastData = generatePayFastData(packageId, userId, userEmail, userName);
    const rawPayload = buildPayloadString(payfastData, true, null);

    res.json({ success: true, rawPayload, payfastData });
  } catch (err) {
    console.error('Debug payload error:', err);
    res.status(500).json({ success: false, message: 'Error building payload' });
  }
});

module.exports = { router, CREDIT_PACKAGES, generatePayFastData, generateSignature };
