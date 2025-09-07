require('dotenv').config({ path: require('path').join(__dirname, '../.env') });
const express = require('express');
const cors = require('cors');

console.log('🔄 Starting minimal backend server...');

const app = express();
const PORT = 3011;

// Basic middleware
app.use(cors());
app.use(express.json());

// Health check only
app.get('/api/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    message: 'Minimal server is working',
    timestamp: new Date().toISOString(),
    port: PORT
  });
});

// Test route
app.get('/api/test', (req, res) => {
  res.json({ message: 'Test endpoint working' });
});

// Start server
const server = app.listen(PORT, '0.0.0.0', (err) => {
  if (err) {
    console.error('❌ Failed to start server:', err);
    process.exit(1);
  }
  console.log(`🚀 Minimal server running on port ${PORT}`);
  console.log(`📊 Health: http://localhost:${PORT}/api/health`);
  console.log(`🧪 Test: http://localhost:${PORT}/api/test`);
});

server.on('error', (err) => {
  console.error('❌ Server error:', err);
});

process.on('SIGINT', () => {
  console.log('\n👋 Shutting down server...');
  server.close(() => {
    console.log('✅ Server closed');
    process.exit(0);
  });
});
