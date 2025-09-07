const express = require('express');
const app = express();
const PORT = 3011;

app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', message: 'Backend server is running', port: PORT });
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Test server running on port ${PORT}`);
});
