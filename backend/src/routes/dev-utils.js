const express = require('express');
const { query } = require('../utils/database');

// This route should only be used in development environments
// Should be disabled in production

const router = express.Router();

// Verify a user's email by email address (DEV ONLY)
router.post('/verify-user', async (req, res) => {
  // Only allow in development environment
  if (process.env.NODE_ENV === 'production') {
    return res.status(403).json({
      success: false,
      message: 'This endpoint is not available in production'
    });
  }

  try {
    const { email } = req.body;
    
    if (!email) {
      return res.status(400).json({
        success: false,
        message: 'Email is required'
      });
    }

    const result = await query(
      'UPDATE users SET email_verified = true WHERE email = $1 RETURNING id, email',
      [email]
    );

    if (result.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }

    res.json({
      success: true,
      message: `User ${email} has been verified`,
      data: {
        userId: result.rows[0].id,
        email: result.rows[0].email
      }
    });
  } catch (error) {
    console.error('Error in dev verify user:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to verify user'
    });
  }
});

module.exports = router;
