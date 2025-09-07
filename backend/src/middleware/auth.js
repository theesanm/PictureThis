const jwt = require('jsonwebtoken');
const { query } = require('../utils/database');

const authenticateToken = async (req, res, next) => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

  if (!token) {
    return res.status(401).json({
      success: false,
      message: 'Access token required'
    });
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    // Check if user exists and is verified
    const result = await query(
      'SELECT id, email, email_verified FROM users WHERE id = $1',
      [decoded.userId]
    );

    if (result.rows.length === 0) {
      return res.status(403).json({
        success: false,
        message: 'User not found'
      });
    }

    const user = result.rows[0];

    // Check if email is verified
    if (!user.email_verified) {
      return res.status(403).json({
        success: false,
        message: 'Email verification required',
        requiresVerification: true,
        email: user.email
      });
    }

    req.user = {
      userId: user.id,
      email: user.email,
      emailVerified: user.email_verified
    };
    next();
  } catch (err) {
    return res.status(403).json({
      success: false,
      message: 'Invalid or expired token'
    });
  }
};

const requireAdmin = async (req, res, next) => {
  try {
    if (!req.user || !req.user.userId) {
      return res.status(401).json({
        success: false,
        message: 'Authentication required'
      });
    }
    
    // Check if the user is an admin
    const result = await query('SELECT is_admin FROM users WHERE id = $1', [req.user.userId]);
    
    if (result.rows.length === 0) {
      return res.status(403).json({
        success: false,
        message: 'User not found'
      });
    }
    
    if (!result.rows[0].is_admin) {
      return res.status(403).json({
        success: false,
        message: 'Admin privileges required'
      });
    }
    
    // User is an admin, proceed
    next();
  } catch (error) {
    console.error('Admin check error:', error);
    return res.status(500).json({
      success: false,
      message: 'Error verifying admin privileges'
    });
  }
};

module.exports = {
  authenticateToken,
  requireAdmin
};
