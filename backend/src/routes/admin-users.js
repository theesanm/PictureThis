const express = require('express');
const { authenticateToken, requireAdmin } = require('../middleware/auth');
const { query } = require('../utils/database');

const router = express.Router();

// Apply admin middleware to all routes
router.use(authenticateToken);
router.use(requireAdmin);

// Get all users
router.get('/users', async (req, res) => {
  try {
    const result = await query(
      'SELECT id, email, full_name, credits, is_admin, email_verified, created_at FROM users ORDER BY created_at DESC'
    );
    
    // Convert database fields to API format
    const users = result.rows.map(user => ({
      id: user.id,
      email: user.email,
      fullName: user.full_name,
      credits: user.credits,
      isAdmin: user.is_admin,
      isVerified: user.email_verified,
      createdAt: user.created_at
    }));
    
    res.json({
      success: true,
      data: {
        users: users
      }
    });
  } catch (error) {
    console.error('Admin users fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch users'
    });
  }
});

// Add or remove credits from a user
router.post('/users/credits', async (req, res) => {
  try {
    const { email, credits, reason } = req.body;
    
    if (!email || credits === undefined) {
      return res.status(400).json({
        success: false,
        message: 'Email and credits are required'
      });
    }
    
    // Find the user
    const userResult = await query('SELECT id, credits FROM users WHERE email = $1', [email]);
    
    if (userResult.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }
    
    const userId = userResult.rows[0].id;
    const currentCredits = userResult.rows[0].credits || 0;
    const newCredits = currentCredits + parseInt(credits);
    
    // Update user credits
    await query('UPDATE users SET credits = $1 WHERE id = $2', [newCredits, userId]);
    
    // Log the transaction
    const transactionType = credits > 0 ? 'admin_add' : 'admin_remove';
    await query(
      'INSERT INTO credit_transactions (user_id, amount, transaction_type, description) VALUES ($1, $2, $3, $4)',
      [userId, credits, transactionType, reason || `Admin adjusted credits by ${credits}`]
    );
    
    res.json({
      success: true,
      message: `User credits updated successfully. New balance: ${newCredits}`,
      data: {
        email,
        previousCredits: currentCredits,
        adjustment: credits,
        newBalance: newCredits
      }
    });
  } catch (error) {
    console.error('Admin credit adjustment error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update user credits'
    });
  }
});

// Update user admin status
router.put('/users/:id/admin', async (req, res) => {
  try {
    const { id } = req.params;
    const { isAdmin } = req.body;
    
    if (isAdmin === undefined) {
      return res.status(400).json({
        success: false,
        message: 'isAdmin status is required'
      });
    }
    
    await query('UPDATE users SET is_admin = $1 WHERE id = $2', [isAdmin, id]);
    
    res.json({
      success: true,
      message: `User admin status updated to ${isAdmin}`
    });
  } catch (error) {
    console.error('Admin status update error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update admin status'
    });
  }
});

module.exports = router;
