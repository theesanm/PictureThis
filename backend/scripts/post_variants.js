const https = require('https');
const { generatePayFastData, generateSignature } = require('../src/routes/payfast-credits');

function buildRawFromObj(data) {
  const keys = Object.keys(data).sort();
  return keys.map(k => `${k}=${encodeURIComponent(data[k]).replace(/%20/g, '+')}`).join('&');
}

async function post(raw) {
  return new Promise((resolve, reject) => {
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
      let body='';
      res.on('data', c => body+=c);
      res.on('end', () => resolve({ status: res.statusCode, body }));
    });
    req.on('error', reject);
    req.write(raw);
    req.end();
  });
}

(async () => {
  const pkg = 'small';
  const data = generatePayFastData(pkg, 1, 'test@example.com', 'Test User');

  // Variant A: signature computed normally (includes merchant_key)
  const rawA = buildRawFromObj(data);
  console.log('Posting Variant A (current) length', rawA.length);
  const resA = await post(rawA);
  console.log('Variant A status', resA.status);

  // Variant B: compute signature excluding merchant_key from the string (but still include merchant_key param in form)
  const dataB = { ...data };
  // recompute signature excluding merchant_key
  const dataForSig = { ...dataB };
  delete dataForSig.signature;
  delete dataForSig.merchant_key; // exclude merchant_key from signature computation
  const sigB = generateSignature(dataForSig, process.env.PAYFAST_PASSPHRASE || 'ThisIsATestFromPictureThis');
  dataB.signature = sigB;
  const rawB = buildRawFromObj(dataB);
  console.log('Posting Variant B (exclude merchant_key from signature) length', rawB.length);
  const resB = await post(rawB);
  console.log('Variant B status', resB.status);

  console.log('Done');
})();
