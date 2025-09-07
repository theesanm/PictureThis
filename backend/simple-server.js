require('dotenv').config();
const express = require('express');
const cors = require('cors');
const app = express();
const PORT = 3011; // Use the standard backend port

console.log('🔄 Starting server...');
console.log('PORT:', PORT);

// Basic middleware
app.use(cors());
app.use(express.json());

// Simple health check
app.get('/api/health', (req, res) => {
  console.log('🩺 Health check requested');
  res.json({ 
    status: 'OK', 
    timestamp: new Date().toISOString(),
    port: PORT 
  });
});

// Simple test route
app.get('/api/test', (req, res) => {
  console.log('🧪 Test route requested');
  res.json({ message: 'Backend is working!', port: PORT });
});

// Error handler
app.use((err, req, res, next) => {
  console.error('❌ Error:', err);
  res.status(500).json({ error: 'Server error' });
});

console.log(`🚀 Attempting to bind to port ${PORT}...`);

const server = app.listen(PORT, '0.0.0.0', () => {
  console.log(`✅ Server successfully started on port ${PORT}`);
  console.log(`📊 Health check: http://localhost:${PORT}/api/health`);
  console.log(`🧪 Test endpoint: http://localhost:${PORT}/api/test`);
});

server.on('error', (err) => {
  console.error('❌ Server error:', err);
});

module.exports = app;
