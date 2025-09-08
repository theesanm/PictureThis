require('dotenv').config({ path: require('path').join(__dirname, '../.env') });
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const path = require('path');

console.log('🔄 Starting Picture This Backend Server...');
console.log('📁 Environment loaded from:', require('path').join(__dirname, '../.env'));

const app = express();
const PORT = process.env.PORT || 3011;

console.log(`🎯 Target port: ${PORT}`);

// Middleware
app.use(helmet({
  crossOriginResourcePolicy: { policy: "cross-origin" }
}));

// CORS configuration
app.use(cors({
  origin: function (origin, callback) {
    // Allow requests with no origin (like mobile apps or curl requests)
    if (!origin) return callback(null, true);
    
    const allowedOrigins = [
      'http://localhost:3000',
      'http://localhost:3010', 
      'http://localhost:3011',
      'http://127.0.0.1:3000',
      'http://127.0.0.1:3010',
      'http://127.0.0.1:3011'
    ];
    
    // Allow ngrok URLs (they end with ngrok-free.app or ngrok.app)
    if (origin.includes('ngrok-free.app') || origin.includes('ngrok.app')) {
      return callback(null, true);
    }
    
    if (allowedOrigins.includes(origin)) {
      return callback(null, true);
    }
    
    return callback(new Error('Not allowed by CORS'));
  },
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'x-requested-with']
}));

app.use(express.json({ limit: '10mb' }));
// Capture raw urlencoded body for payment gateway signature verification (PayFast ITN)
app.use(express.urlencoded({
  extended: true,
  verify: (req, res, buf) => {
    // store raw body as a string for routes that need exact POST payload
    req.rawBody = buf && buf.toString && buf.toString();
  }
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: (process.env.RATE_LIMIT_WINDOW || 15) * 60 * 1000, // 15 minutes
  max: process.env.RATE_LIMIT_MAX_REQUESTS || 100,
  message: 'Too many requests from this IP, please try again later.'
});
app.use('/api/', limiter);

// Routes
app.use('/api/auth', require('./routes/auth'));
app.use('/api/users', require('./routes/users'));
app.use('/api/credits', require('./routes/credits'));
app.use('/api/credits', require('./routes/payfast-credits').router);
app.use('/api/users/credits', require('./routes/user-credits'));
app.use('/api/images', require('./routes/images'));
app.use('/api/prompts', require('./routes/prompts'));
app.use('/api/admin/settings', require('./routes/admin-settings'));
app.use('/api/settings', require('./routes/settings-public'));
app.use('/api/admin', require('./routes/admin'));

// Development utilities (disabled in production)
if (process.env.NODE_ENV !== 'production') {
  app.use('/api/dev', require('./routes/dev-utils'));
  console.log('⚠️ Dev utilities enabled - NOT FOR PRODUCTION USE');
}

// Import admin routes
const adminPermissions = require('./routes/admin-permissions');
const adminUsers = require('./routes/admin-users');
const adminCredits = require('./routes/admin-credits');
app.use('/api/admin', adminPermissions);
app.use('/api/admin', adminUsers);
app.use('/api/admin/credits', adminCredits);

// Settings routes
app.use('/api/admin/settings', require('./routes/settings'));
app.use('/api/settings', require('./routes/settings-public'));

// Serve uploaded images statically with CORS headers
app.use('/uploads', (req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET');
  res.header('Access-Control-Allow-Headers', 'Content-Type');
  res.header('Cross-Origin-Resource-Policy', 'cross-origin');
  next();
}, express.static(path.join(__dirname, '../uploads')));

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK', timestamp: new Date().toISOString() });
});

// Payment success page
app.get('/payment/success', async (req, res) => {
  try {
    // Check if this is a PayFast return with payment details
    const paymentId = req.query.payment_id;
    const userId = req.query.user_id;
    const packageId = req.query.package_id;

    let creditsAdded = false;
    let message = 'Your credits have been added to your account.';

    if (paymentId && userId && packageId) {
      // Check if credits have already been added
      const { query } = require('./utils/database');
      const existingTransaction = await query(
        `SELECT id FROM credit_transactions WHERE payment_id = '${paymentId}'`
      );

      if (existingTransaction.rows.length === 0) {
        // Credits haven't been added yet, add them now
        const { CREDIT_PACKAGES } = require('./routes/payfast-credits');
        const packageData = CREDIT_PACKAGES[packageId];

        if (packageData) {
          await query(`UPDATE users SET credits = credits + ${packageData.credits} WHERE id = ${userId}`);

          await query(
            `INSERT INTO credit_transactions (user_id, amount, transaction_type, stripe_payment_id, description, payment_id) VALUES (${userId}, ${packageData.credits}, 'purchase', null, 'PayFast purchase: ${packageData.credits} credits (${packageId})', '${paymentId}')`
          );

          creditsAdded = true;
          console.log(`✅ Credits added via success page: ${packageData.credits} credits to user ${userId}`);
        } else {
          message = 'Invalid package selected.';
        }
      } else {
        message = 'Payment already processed. Your credits are available.';
      }
    } else {
      message = 'Missing payment information.';
    }

    res.send(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Successful - PictureThis</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                text-align: center;
                background-color: #f5f5f5;
            }
            .success-container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .success-icon {
                font-size: 48px;
                color: #28a745;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                margin-bottom: 30px;
            }
            .status {
                background: #d4edda;
                color: #155724;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
                display: ${creditsAdded ? 'block' : 'none'};
            }
            .button {
                display: inline-block;
                padding: 12px 24px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s;
            }
            .button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <div class="success-icon">✓</div>
            <h1>Payment Successful!</h1>
            <div class="status">Credits added to your account!</div>
            <p>${message} You can now continue generating amazing images with PictureThis.</p>
            <a href="${process.env.NODE_ENV === 'production' ? 'https://yourdomain.com' : 'https://acefad368307.ngrok-free.app'}" class="button">Continue to PictureThis</a>
        </div>
    </body>
    </html>
  `);
  } catch (error) {
    console.error('Payment success page error:', error);
    res.status(500).send('Error processing payment success');
  }
});

// Payment cancelled page
app.get('/payment/cancelled', (req, res) => {
  res.send(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Cancelled - PictureThis</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                text-align: center;
                background-color: #f5f5f5;
            }
            .cancel-container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .cancel-icon {
                font-size: 48px;
                color: #dc3545;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                margin-bottom: 30px;
            }
            .button {
                display: inline-block;
                padding: 12px 24px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s;
            }
            .button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="cancel-container">
            <div class="cancel-icon">✕</div>
            <h1>Payment Cancelled</h1>
            <p>Your payment was cancelled. No charges have been made to your account.</p>
            <a href="${process.env.NODE_ENV === 'production' ? 'https://yourdomain.com' : 'https://acefad368307.ngrok-free.app'}" class="button">Return to PictureThis</a>
        </div>
    </body>
    </html>
  `);
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({
    success: false,
    message: 'Something went wrong!'
  });
});

// 404 handler
app.use('*', (req, res) => {
  res.status(404).json({
    success: false,
    message: 'Route not found'
  });
});

app.listen(PORT, '0.0.0.0', (err) => {
  if (err) {
    console.error('❌ Failed to start server:', err);
    process.exit(1);
  }
  console.log(`🚀 Picture This Backend Server running on port ${PORT}`);
  console.log(`📊 Health check available at http://localhost:${PORT}/api/health`);
  console.log(`🌐 Server accessible at http://0.0.0.0:${PORT}`);
});

module.exports = app;
