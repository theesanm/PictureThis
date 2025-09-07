require('dotenv').config({ path: '../.env' });
const express = require('express');
const cors = require('cors');
const app = express();
const PORT = process.env.PORT || 3011;

// Basic middleware
app.use(cors());
app.use(express.json());

// Health check
app.get('/api/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    timestamp: new Date().toISOString(),
    message: 'Backend is working!'
  });
});

// Test endpoint
app.get('/api/test', (req, res) => {
  res.json({ message: 'Test endpoint working!' });
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Picture This Backend Server running on port ${PORT}`);
  console.log(`📊 Health check available at http://localhost:${PORT}/api/health`);
  console.log(`🔗 Test endpoint available at http://localhost:${PORT}/api/test`);
});

module.exports = app;
