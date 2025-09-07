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
  origin: ['http://localhost:3000', 'http://localhost:3010', 'http://localhost:3011', 'http://127.0.0.1:3000', 'http://127.0.0.1:3010', 'http://127.0.0.1:3011'],
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'x-requested-with']
}));

app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

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
app.use('/api/users/credits', require('./routes/user-credits'));
app.use('/api/images', require('./routes/images'));
app.use('/api/prompts', require('./routes/prompts'));
app.use('/api/admin', require('./routes/admin'));

// Development utilities (disabled in production)
if (process.env.NODE_ENV !== 'production') {
  app.use('/api/dev', require('./routes/dev-utils'));
  console.log('⚠️ Dev utilities enabled - NOT FOR PRODUCTION USE');
}

// Import admin routes
const adminPermissions = require('./routes/admin-permissions');
const adminUsers = require('./routes/admin-users');
app.use('/api/admin', adminPermissions);
app.use('/api/admin', adminUsers);

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

// Error handling middleware
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({
    success: false,
    message: 'Something went wrong!',
    error: process.env.NODE_ENV === 'development' ? err.message : {}
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
