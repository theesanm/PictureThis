const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3011;

// Middleware
app.use(cors({
  origin: ['http://localhost:3010', 'http://127.0.0.1:3010'],
  credentials: true
}));
app.use(express.json());

// Error handling middleware
app.use((err, req, res, next) => {
  console.error('❌ Error:', err.message);
  res.status(500).json({ error: 'Something went wrong!' });
});

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({
    status: 'OK',
    message: 'Backend server is running',
    timestamp: new Date().toISOString(),
    port: PORT
  });
});

// Test endpoint
app.get('/api/test', (req, res) => {
  res.json({ message: 'Backend API is working' });
});

// Basic auth endpoints (mock for now)
app.post('/api/auth/register', (req, res) => {
  res.json({ 
    success: true, 
    message: 'User registered successfully',
    user: { id: 1, email: req.body.email }
  });
});

app.post('/api/auth/login', (req, res) => {
  res.json({ 
    success: true, 
    message: 'Login successful',
    token: 'mock-jwt-token',
    user: { id: 1, email: req.body.email }
  });
});

// Mock credits endpoint
app.get('/api/credits', (req, res) => {
  res.json({ 
    credits: 100,
    user_id: 1
  });
});

// Mock image generation endpoint
app.post('/api/images/generate', (req, res) => {
  res.json({
    success: true,
    message: 'Image generation started',
    task_id: 'mock-task-' + Date.now(),
    estimated_time: 30
  });
});

// Start server with proper error handling
const server = app.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Stable Backend Server running on port ${PORT}`);
  console.log(`📊 Health: http://localhost:${PORT}/api/health`);
  console.log(`🔗 CORS enabled for: http://localhost:3010`);
});

// Graceful shutdown
process.on('SIGINT', () => {
  console.log('\n👋 Shutting down server...');
  server.close(() => {
    console.log('✅ Server closed');
    process.exit(0);
  });
});

process.on('SIGTERM', () => {
  console.log('👋 Received SIGTERM, shutting down...');
  server.close(() => {
    console.log('✅ Server closed');
    process.exit(0);
  });
});

// Handle uncaught exceptions
process.on('uncaughtException', (err) => {
  console.error('❌ Uncaught Exception:', err);
  // Don't exit the process, just log the error
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
  // Don't exit the process, just log the error
});
