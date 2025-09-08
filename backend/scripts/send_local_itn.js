const https = require('https');
const http = require('http');
const crypto = require('crypto');
const querystring = require('querystring');

(async () => {
  try {
    // Configuration - adjust if needed
    const host = 'localhost';
    const port = 3011;
    const path = '/api/credits/payfast/notify';

    const PAYFAST_PASSPHRASE = process.env.PAYFAST_PASSPHRASE || 'ThisIsATestFromPictureThis';
    const PAYFAST_MERCHANT_ID = process.env.PAYFAST_MERCHANT_ID || '10041798';
    const PAYFAST_MERCHANT_KEY = process.env.PAYFAST_MERCHANT_KEY || 'vlnqle74tnkl7';

    const testData = {
      amount_gross: '200.00',
      custom_str1: '1',
      custom_str2: 'small',
      custom_str3: '50',
      email_address: 'test@example.com',
      item_description: 'Purchase 50 credits for PictureThis',
      item_name: '50 Credits',
      m_payment_id: `test_${Date.now()}`,
      merchant_id: PAYFAST_MERCHANT_ID,
      merchant_key: PAYFAST_MERCHANT_KEY,
      name_first: 'Test',
      name_last: 'User',
      payment_status: 'COMPLETE'
    };

    // Build raw string in alphabetical order of keys (PayFast requirement)
    const keys = Object.keys(testData).sort();
    const parts = keys.map(k => `${k}=${encodeURIComponent(testData[k]).replace(/%20/g, '+')}`);
    let raw = parts.join('&');

    // Compute signature using passphrase appended
    const sigString = `${raw}&passphrase=${encodeURIComponent(PAYFAST_PASSPHRASE).replace(/%20/g, '+')}`;
    const signature = crypto.createHash('md5').update(sigString).digest('hex');

    // Append signature to payload
    raw += `&signature=${signature}`;

    console.log('Raw payload to send:');
    console.log(raw);
    console.log('Signature:', signature);

    // Send as application/x-www-form-urlencoded
    const options = {
      hostname: host,
      port: port,
      path: path,
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Content-Length': Buffer.byteLength(raw)
      }
    };

    const req = http.request(options, (res) => {
      console.log('STATUS:', res.statusCode);
      let body = '';
      res.on('data', (chunk) => body += chunk);
      res.on('end', () => {
        console.log('RESPONSE BODY:', body);
      });
    });

    req.on('error', (e) => console.error('Request error', e));
    req.write(raw);
    req.end();

  } catch (err) {
    console.error('Test script error', err);
  }
})();
