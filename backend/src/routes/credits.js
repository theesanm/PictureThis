const express = require('express');
const { authenticateToken } = require('../middleware/auth');
const { query } = require('../utils/database');

const router = express.Router();

// Get available credit packages
router.get('/packages', async (req, res) => {
  try {
    // Get credit cost per image from settings (for description purposes only)
    const settingsResult = await query('SELECT credit_cost_per_image FROM settings ORDER BY id DESC LIMIT 1');
    const creditCost = settingsResult.rows.length > 0 ? settingsResult.rows[0].credit_cost_per_image : 1;

    // Fixed package prices (not calculated from credit cost)
    const packages = {
      'small': { 
        credits: 50, 
        price: 200.00, 
        name: '50 Credits' 
      },
      'medium': { 
        credits: 75, 
        price: 250.00, 
        name: '75 Credits (10% off)' 
      },
      'large': { 
        credits: 125, 
        price: 300.00, 
        name: '125 Credits (20% off)' 
      },
      'premium': { 
        credits: 200, 
        price: 350.00, 
        name: '200 Credits (30% off)' 
      }
    };

    res.json({
      success: true,
      data: packages
    });
  } catch (error) {
    console.error('Packages fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch credit packages'
    });
  }
});

// Get user credits
router.get('/balance', authenticateToken, async (req, res) => {
  try {
    const result = await query('SELECT credits FROM users WHERE id = $1', [req.user.userId]);

    res.json({
      success: true,
      data: {
        credits: result.rows[0].credits
      }
    });
  } catch (error) {
    console.error('Credit balance error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch credit balance'
    });
  }
});

// Purchase credits (placeholder - integrate with Stripe later)
router.post('/purchase', authenticateToken, async (req, res) => {
  try {
    const { amount, stripeToken } = req.body;

    // TODO: Integrate with Stripe for payment processing
    // For now, just add credits directly (for testing)

    await query('UPDATE users SET credits = credits + $1 WHERE id = $2', [amount, req.user.userId]);

    // Log transaction
    await query(
      'INSERT INTO credit_transactions (user_id, amount, transaction_type, description) VALUES ($1, $2, $3, $4)',
      [req.user.userId, amount, 'purchase', `Credit purchase: ${amount} credits`]
    );

    res.json({
      success: true,
      message: 'Credits purchased successfully'
    });
  } catch (error) {
    console.error('Credit purchase error:', error);
    res.status(500).json({
      success: false,
      message: 'Credit purchase failed'
    });
  }
});

// Get credit transaction history
router.get('/history', authenticateToken, async (req, res) => {
  try {
    const result = await query(
      'SELECT * FROM credit_transactions WHERE user_id = $1 ORDER BY created_at DESC LIMIT 50',
      [req.user.userId]
    );

    res.json({
      success: true,
      data: {
        transactions: result.rows
      }
    });
  } catch (error) {
    console.error('Transaction history error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch transaction history'
    });
  }
});

module.exports = router;
