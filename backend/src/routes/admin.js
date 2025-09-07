const express = require('express');
const { authenticateToken, requireAdmin } = require('../middleware/auth');
const { query } = require('../utils/database');

const router = express.Router();

// Apply admin middleware to all routes
router.use(authenticateToken);
router.use(requireAdmin);

// Get system statistics
router.get('/stats', async (req, res) => {
  try {
    const userCount = await query('SELECT COUNT(*) as count FROM users');
    const imageCount = await query('SELECT COUNT(*) as count FROM images');
    const totalCredits = await query('SELECT SUM(credits) as total FROM users');

    res.json({
      success: true,
      data: {
        users: parseInt(userCount.rows[0].count),
        images: parseInt(imageCount.rows[0].count),
        totalCredits: parseInt(totalCredits.rows[0].total) || 0
      }
    });
  } catch (error) {
    console.error('Admin stats error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch statistics'
    });
  }
});

// Update system settings
router.put('/settings/:key', async (req, res) => {
  try {
    const { key } = req.params;
    const { value } = req.body;

    await query(
      'INSERT INTO settings (key, value) VALUES ($1, $2) ON CONFLICT (key) DO UPDATE SET value = $2, updated_at = CURRENT_TIMESTAMP',
      [key, value]
    );

    res.json({
      success: true,
      message: 'Setting updated successfully'
    });
  } catch (error) {
    console.error('Settings update error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update setting'
    });
  }
});

module.exports = router;
