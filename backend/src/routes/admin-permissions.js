const express = require('express');
const { authenticateToken, requireAdmin } = require('../middleware/auth');
const { query } = require('../utils/database');

const router = express.Router();

// Apply admin middleware to all routes
router.use(authenticateToken);
router.use(requireAdmin);

// Get user permissions log
router.get('/user-permissions', async (req, res) => {
  try {
    const permissionsResult = await query(`
      SELECT 
        up.id,
        up.user_id,
        u.email,
        up.permission_type,
        up.accepted,
        up.acceptance_date,
        up.ip_address,
        up.user_agent
      FROM 
        user_permissions up
      JOIN
        users u ON up.user_id = u.id
      ORDER BY 
        up.acceptance_date DESC
    `);

    res.json({
      success: true,
      data: {
        permissions: permissionsResult.rows
      }
    });
  } catch (error) {
    console.error('Failed to fetch user permissions:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch user permissions'
    });
  }
});

// Get specific user's permissions
router.get('/user-permissions/:userId', async (req, res) => {
  try {
    const { userId } = req.params;
    
    const permissionsResult = await query(`
      SELECT 
        up.id,
        up.permission_type,
        up.accepted,
        up.acceptance_date,
        up.ip_address,
        up.user_agent
      FROM 
        user_permissions up
      WHERE
        up.user_id = $1
      ORDER BY 
        up.acceptance_date DESC
    `, [userId]);

    const userResult = await query('SELECT id, email, full_name FROM users WHERE id = $1', [userId]);
    
    if (userResult.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }

    res.json({
      success: true,
      data: {
        user: userResult.rows[0],
        permissions: permissionsResult.rows
      }
    });
  } catch (error) {
    console.error('Failed to fetch user permissions:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch user permissions'
    });
  }
});

module.exports = router;
