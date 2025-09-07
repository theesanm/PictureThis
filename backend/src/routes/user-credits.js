const express = require('express');
const { authenticateToken } = require('../middleware/auth');
const { query } = require('../utils/database');

const router = express.Router();

// Get user's current credit balance
router.get('/', authenticateToken, async (req, res) => {
  try {
    const userId = req.user.userId;
    const userResult = await query('SELECT credits FROM users WHERE id = $1', [userId]);
    
    if (userResult.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }
    
    // Get credit cost per image from settings
    const settingsResult = await query('SELECT credit_cost_per_image FROM settings ORDER BY id DESC LIMIT 1');
    const creditCost = settingsResult.rows.length > 0 ? settingsResult.rows[0].credit_cost_per_image : 10;
    
    // Get recent credit transactions
    const transactionsResult = await query(
      'SELECT * FROM credit_transactions WHERE user_id = $1 ORDER BY created_at DESC LIMIT 5',
      [userId]
    );
    
    res.json({
      success: true,
      data: {
        credits: userResult.rows[0].credits,
        creditCost,
        canGenerate: userResult.rows[0].credits >= creditCost,
        recentTransactions: transactionsResult.rows.map(tx => ({
          id: tx.id,
          amount: tx.amount,
          type: tx.transaction_type,
          description: tx.description,
          createdAt: tx.created_at
        }))
      }
    });
  } catch (error) {
    console.error('Credits fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch user credits'
    });
  }
});

module.exports = router;
