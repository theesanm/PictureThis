# cFox Payfast Integration Guide

## Overview
This document outlines the step-by-step process for integrating Payfast payment gateway into applications. It covers sandbox testing, live deployment, signature generation, ITN (Instant Transaction Notification) handling, and troubleshooting. This guide ensures a smooth, error-free integration based on the working implementation in the PayfastIntegration project.

## Prerequisites
- Node.js and Express.js setup.
- Payfast sandbox account (for testing): Sign up at https://sandbox.payfast.co.za.
- Payfast live account (for production): Sign up at https://www.payfast.co.za.
- Basic knowledge of HTML, JavaScript, and server-side handling.
- HTTPS-enabled domain for production (Payfast requires HTTPS).

## Step 1: Obtain Payfast Credentials
1. Log in to your Payfast account (sandbox or live).
2. Navigate to **Account > Settings**.
3. Note the following:
   - **Merchant ID**: A unique identifier for your account.
   - **Merchant Key**: Used for authentication.
   - **Salt Passphrase**: Required for signature generation (set this in the dashboard if not already).
4. For sandbox, use test credentials if needed (e.g., Merchant ID: 10041798, Merchant Key: vlnqle74tnkl7, Passphrase: ThisIsATestFromPictureThis).

## Step 2: Set Up Environment Variables
In your `server.js` file, define the following constants:
```javascript
const SANDBOX = true; // Set to false for live
const PF_HOST = SANDBOX ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
const MERCHANT_ID = 'YOUR_MERCHANT_ID';
const MERCHANT_KEY = 'YOUR_MERCHANT_KEY';
const PASSPHRASE = 'YOUR_SALTPASSPHRASE';
const PORT = process.env.PORT || 3010;
let PUBLIC_BASE = process.env.PUBLIC_BASE || `http://localhost:${PORT}`; // Set to your live domain in production
```

- For production, set `SANDBOX = false` and `PUBLIC_BASE` to your live domain (e.g., `https://yourdomain.com`).

## Step 3: Implement Signature Generation
Payfast requires MD5 signatures for security. Implement the following functions:

### For Form Submission (Initial Payment)
```javascript
function pfEncode(v) {
  if (v === undefined || v === null) return '';
  return encodeURIComponent(String(v).trim()).replace(/%20/g, '+');
}

function dataToString(data) {
  let s = '';
  const order = ['merchant_id', 'merchant_key', 'return_url', 'cancel_url', 'notify_url', 'm_payment_id', 'amount', 'item_name', 'custom_int1'];
  for (const k of order) {
    if (data[k] !== undefined && data[k] !== '') s += `${k}=${pfEncode(data[k])}&`;
  }
  s = s.slice(0, -1);
  if (PASSPHRASE) s += `&passphrase=${pfEncode(PASSPHRASE)}`;
  return s;
}

const signature = crypto.createHash('md5').update(dataToString(data)).digest('hex');
```

- **Order**: Use the fixed order as specified in Payfast docs (merchant details first, then transaction).
- **Encoding**: Use `encodeURIComponent` with spaces as `+`.

### For ITN Verification
```javascript
// In /notify endpoint
const keys = Object.keys(pfData).filter(k => k !== 'signature');
let pfParamString = '';
for (const k of keys) {
  if (pfData[k] !== undefined) pfParamString += `${k}=${pfEncode(pfData[k])}&`;
}
pfParamString = pfParamString.slice(0, -1);
const temp = pfParamString + `&passphrase=${pfEncode(PASSPHRASE)}`;
const sig = crypto.createHash('md5').update(temp).digest('hex');
```

- **Order**: Use the order the fields appear in the POST data.
- **Include Empty Fields**: Include all fields, even if empty (e.g., `custom_str1=`).
- **Verify**: Compare generated `sig` with `pfData.signature`.

## Step 4: Create Payment Form
In the `/buy` endpoint:
```javascript
app.get('/buy', (req, res) => {
  const credits = parseInt(req.query.credits || 0, 10);
  const amount = (priceMap[credits] || 0).toFixed(2);
  const m_payment_id = `credit-${Date.now()}-${credits}`;
  const data = {
    merchant_id: MERCHANT_ID,
    merchant_key: MERCHANT_KEY,
    return_url: `${PUBLIC_BASE}/`, // Redirect to home after payment
    cancel_url: `${PUBLIC_BASE}/cancel`,
    notify_url: `${PUBLIC_BASE}/notify`,
    m_payment_id,
    amount,
    item_name: `${credits} Credits`,
    custom_int1: credits
  };
  const signature = crypto.createHash('md5').update(dataToString(data)).digest('hex');
  data.signature = signature;

  // Generate auto-submitting form
  let html = `<form action="https://${PF_HOST}/eng/process" method="post">`;
  for (const k in data) {
    html += `<input type="hidden" name="${k}" value="${String(data[k]).replace(/'/g, '&#39;')}" />`;
  }
  html += `<input type="submit" value="Pay Now" /></form><script>document.forms[0].submit();</script>`;
  res.send(html);
});
```

- **URLs**: Ensure `notify_url` is publicly accessible (use ngrok for local testing).
- **Auto-Submit**: The form posts to Payfast and redirects the user.

## Step 5: Handle ITN Notifications
In the `/notify` endpoint:
```javascript
app.post('/notify', async (req, res) => {
  res.status(200).send('OK'); // Respond immediately
  const pfData = req.body;
  // Generate signature as above
  // Verify signature and server confirmation
  if (pfData.payment_status === 'COMPLETE' && sig === pfData.signature) {
    // Update credits: e.g., add pfData.custom_int1 to database
    console.log('Payment successful, credits updated');
  }
});
```

- **Server Confirmation**: Optionally, POST to `https://www.payfast.co.za/eng/query/validate` to confirm with Payfast.
- **Update Logic**: Increment user credits based on `custom_int1`.
- **Logging**: Log all ITN data for debugging.

## Step 6: Set Up ITN in Payfast Dashboard
1. Log in to Payfast (sandbox or live).
2. Go to **Account > Settings > Instant Transaction Notification**.
3. Set the ITN URL to `https://yourdomain.com/notify` (must be HTTPS in production).
4. Save changes.

## Step 7: Testing in Sandbox
1. Start the server: `PUBLIC_BASE=https://your-ngrok-url npm start`.
2. Access the demo at `https://your-ngrok-url`.
3. Click "Buy Now", complete payment in sandbox.
4. Verify ITN logs and credit updates.
5. Check for signature matches and server validation.

## Step 8: Going Live
1. Update credentials to live values.
2. Set `SANDBOX = false`.
3. Update `PUBLIC_BASE` to your live domain.
4. Deploy to production with HTTPS.
5. Test with small amounts.
6. Monitor logs for ITN processing.

## Troubleshooting
- **Signature Mismatch**: Ensure correct order, encoding, and passphrase. Include empty fields for ITN.
- **ITN Not Received**: Confirm `notify_url` is public and ITN URL is set in dashboard.
- **Credits Not Updating**: Check ITN logs; ensure `payment_status = 'COMPLETE'` and signature valid.
- **Common Issues**: Wrong passphrase, missing HTTPS, or incorrect field order.
- **Logs**: Enable detailed logging for `pfParamString`, generated sig, and received sig.

## Conclusion
Follow this guide for consistent Payfast integrations. Test thoroughly in sandbox before going live. For issues, refer to Payfast docs or support. This process ensures secure, reliable payments.</content>
<parameter name="filePath">/Volumes/MacM4Ext/Projects/PayfastIntegration/cFoxPayfast.md
