const express = require('express');
const { authenticateToken, requireAdmin } = require('../middleware/auth');
const { query } = require('../utils/database');

const router = express.Router();

// Apply admin middleware to all routes
router.use(authenticateToken);
router.use(requireAdmin);

// Get all credit transactions for admin
router.get('/transactions', async (req, res) => {
  try {
    const result = await query(`
      SELECT
        ct.id,
        ct.user_id,
        ct.amount,
        ct.transaction_type,
        ct.description,
        ct.created_at,
        u.email,
        u.full_name
      FROM credit_transactions ct
      LEFT JOIN users u ON ct.user_id = u.id
      ORDER BY ct.created_at DESC
      LIMIT 100
    `);

    const transactions = result.rows.map(row => ({
      id: row.id,
      userId: row.user_id,
      userEmail: row.email,
      userName: row.full_name,
      amount: row.amount,
      type: row.transaction_type,
      description: row.description,
      createdAt: row.created_at
    }));

    res.json({
      success: true,
      data: {
        transactions: transactions
      }
    });
  } catch (error) {
    console.error('Admin credit transactions fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch credit transactions'
    });
  }
});

// Get credit statistics for admin
router.get('/stats', async (req, res) => {
  try {
    const totalCredits = await query('SELECT SUM(credits) as total FROM users');
    const totalTransactions = await query('SELECT COUNT(*) as count FROM credit_transactions');
    const recentTransactions = await query(`
      SELECT COUNT(*) as count
      FROM credit_transactions
      WHERE created_at >= NOW() - INTERVAL '30 days'
    `);

    res.json({
      success: true,
      data: {
        totalCredits: parseInt(totalCredits.rows[0].total) || 0,
        totalTransactions: parseInt(totalTransactions.rows[0].count),
        recentTransactions: parseInt(recentTransactions.rows[0].count)
      }
    });
  } catch (error) {
    console.error('Admin credit stats error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch credit statistics'
    });
  }
});

module.exports = router;
