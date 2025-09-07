const express = require('express');
const { authenticateToken } = require('../middleware/auth');
const { query } = require('../utils/database');
const { body, validationResult } = require('express-validator');

const router = express.Router();

// Get user profile
router.get('/profile', authenticateToken, async (req, res) => {
  try {
    const userId = req.user.userId;
    
    const user = await query('SELECT * FROM users WHERE id = $1', [userId]);

    if (user.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }

    res.json({
      success: true,
      data: {
        user: {
          id: user.rows[0].id,
          email: user.rows[0].email,
          fullName: user.rows[0].full_name,
          credits: user.rows[0].credits,
          createdAt: user.rows[0].created_at
        }
      }
    });
  } catch (error) {
    console.error('Profile fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch profile'
    });
  }
});

// Check user permissions
router.get('/permissions/:type', authenticateToken, async (req, res) => {
  try {
    const userId = req.user.userId;
    const { type } = req.params;
    
    // Valid permission types
    const validTypes = ['image_usage']; 
    
    if (!validTypes.includes(type)) {
      return res.status(400).json({
        success: false,
        message: 'Invalid permission type'
      });
    }
    
    const permissionResult = await query(
      'SELECT * FROM user_permissions WHERE user_id = $1 AND permission_type = $2',
      [userId, type]
    );
    
    if (permissionResult.rows.length > 0) {
      return res.json({
        success: true,
        data: {
          hasPermission: permissionResult.rows[0].accepted,
          acceptanceDate: permissionResult.rows[0].acceptance_date
        }
      });
    } else {
      return res.json({
        success: true,
        data: {
          hasPermission: false,
          acceptanceDate: null
        }
      });
    }
  } catch (error) {
    console.error('Error checking user permissions:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to check user permissions'
    });
  }
});

// Update user permissions
router.post('/permissions/:type', authenticateToken, async (req, res) => {
  try {
    const userId = req.user.userId;
    const { type } = req.params;
    const { accepted } = req.body;
    
    // Valid permission types
    const validTypes = ['image_usage']; 
    
    if (!validTypes.includes(type)) {
      return res.status(400).json({
        success: false,
        message: 'Invalid permission type'
      });
    }
    
    // Get IP address and user agent
    const ipAddress = req.headers['x-forwarded-for'] || req.connection.remoteAddress;
    const userAgent = req.headers['user-agent'];
    
    // Check if we already have a record for this user
    const existingPermission = await query(
      'SELECT * FROM user_permissions WHERE user_id = $1 AND permission_type = $2',
      [userId, type]
    );
    
    if (existingPermission.rows.length === 0) {
      // Insert new permission acceptance record
      await query(
        'INSERT INTO user_permissions (user_id, permission_type, accepted, acceptance_date, ip_address, user_agent) VALUES ($1, $2, $3, $4, $5, $6)',
        [userId, type, accepted, accepted ? new Date() : null, ipAddress, userAgent]
      );
    } else {
      // Update existing permission acceptance record
      await query(
        'UPDATE user_permissions SET accepted = $1, acceptance_date = $2, ip_address = $3, user_agent = $4 WHERE user_id = $5 AND permission_type = $6',
        [accepted, accepted ? new Date() : null, ipAddress, userAgent, userId, type]
      );
    }
    
    return res.json({
      success: true,
      message: accepted ? 'Permission accepted' : 'Permission declined',
      data: {
        hasPermission: accepted,
        acceptanceDate: accepted ? new Date() : null
      }
    });
  } catch (error) {
    console.error('Error updating user permissions:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update user permissions'
    });
  }
});

// Update user profile
router.put('/profile', [
  authenticateToken,
  body('fullName').optional().trim().isLength({ min: 1 }).withMessage('Full name cannot be empty'),
  body('email').optional().isEmail().normalizeEmail().withMessage('Invalid email format')
], async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        message: 'Validation errors',
        errors: errors.array()
      });
    }

    const userId = req.user.userId;
    const { fullName, email } = req.body;

    // Check if email is already taken by another user
    if (email) {
      const existingUser = await query('SELECT id FROM users WHERE email = $1 AND id != $2', [email, userId]);
      if (existingUser.rows.length > 0) {
        return res.status(400).json({
          success: false,
          message: 'Email already in use'
        });
      }
    }

    // Build update query dynamically
    const updates = [];
    const values = [];
    let paramCount = 1;

    if (fullName !== undefined) {
      updates.push(`full_name = $${paramCount}`);
      values.push(fullName);
      paramCount++;
    }

    if (email !== undefined) {
      updates.push(`email = $${paramCount}`);
      values.push(email);
      paramCount++;
    }

    if (updates.length === 0) {
      return res.status(400).json({
        success: false,
        message: 'No valid fields to update'
      });
    }

    values.push(userId);
    const updateQuery = `UPDATE users SET ${updates.join(', ')} WHERE id = $${paramCount} RETURNING id, email, full_name, credits`;

    const result = await query(updateQuery, values);

    if (result.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }

    res.json({
      success: true,
      message: 'Profile updated successfully',
      data: {
        user: {
          id: result.rows[0].id,
          email: result.rows[0].email,
          fullName: result.rows[0].full_name,
          credits: result.rows[0].credits
        }
      }
    });
  } catch (error) {
    console.error('Profile update error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update profile'
    });
  }
});

module.exports = router;
