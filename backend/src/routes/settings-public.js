const express = require('express');
const router = express.Router();
const db = require('../utils/database');

// Public settings endpoint - only returns public-facing settings
router.get('/', async (req, res) => {
  try {
    // Get settings from database
    const settings = await db.query('SELECT * FROM settings ORDER BY id DESC LIMIT 1');
    
    if (settings.rows.length === 0) {
      // Return default settings
      return res.status(200).json({ 
        success: true, 
        data: { 
          settings: {
            creditCostPerImage: 10,
            enhancedPromptEnabled: true,
            enhancedPromptCost: 0
          }
        }
      });
    }
    
    // Format and return only the public-facing settings
    const publicSettings = {
      creditCostPerImage: settings.rows[0].credit_cost_per_image,
      enhancedPromptEnabled: settings.rows[0].enhanced_prompt_enabled,
      enhancedPromptCost: settings.rows[0].enhanced_prompt_cost
    };
    
    res.status(200).json({ 
      success: true, 
      data: { settings: publicSettings }
    });
  } catch (error) {
    console.error('Error fetching settings:', error);
    res.status(500).json({ 
      success: false, 
      message: 'Failed to fetch system settings' 
    });
  }
});

module.exports = router;
