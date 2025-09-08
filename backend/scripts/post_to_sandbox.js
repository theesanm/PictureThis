const https = require('https');
const querystring = require('querystring');
const { generatePayFastData } = require('../src/routes/payfast-credits');

(async () => {
  try {
    const pkg = 'small';
    const userId = 1;
    const userEmail = 'test@example.com';
    const userName = 'Test User';

    const data = generatePayFastData(pkg, userId, userEmail, userName);

    // Build urlencoded payload in alphabetical order
    const keys = Object.keys(data).sort();
    const parts = [];
    for (const k of keys) {
      if (data[k] === undefined || data[k] === null || data[k] === '') continue;
      parts.push(`${k}=${encodeURIComponent(data[k].toString()).replace(/%20/g, '+')}`);
    }
    const raw = parts.join('&');

    console.log('Posting to sandbox with payload:\n', raw);

    const options = {
      hostname: 'sandbox.payfast.co.za',
      port: 443,
      path: '/eng/process',
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Content-Length': Buffer.byteLength(raw)
      }
    };

    const req = https.request(options, (res) => {
      console.log('STATUS:', res.statusCode);
      let body = '';
      res.on('data', (chunk) => body += chunk);
      res.on('end', () => {
        console.log('RESPONSE LENGTH:', body.length);
        console.log('RESPONSE BODY (truncated 2000 chars):\n', body.slice(0, 2000));
      });
    });

    req.on('error', (e) => console.error('Request error', e));
    req.write(raw);
    req.end();
  } catch (err) {
    console.error(err);
  }
})();
