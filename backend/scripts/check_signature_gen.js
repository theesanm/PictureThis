const { generatePayFastData, generateSignature } = require('../src/routes/payfast-credits');

const pkg = 'small';
const userId = 1;
const userEmail = 'test@example.com';
const userName = 'Test User';

const data = generatePayFastData(pkg, userId, userEmail, userName);
console.log('PayFast form data:');
console.log(data);

// Build canonical string using exported generateSignature (object mode)
const sig = generateSignature(data, process.env.PAYFAST_PASSPHRASE || 'ThisIsATestFromPictureThis');
console.log('Signature (calc):', sig);
console.log('Signature (in data):', data.signature);

if (sig === data.signature) console.log('Signatures match'); else console.log('Signatures do NOT match');
