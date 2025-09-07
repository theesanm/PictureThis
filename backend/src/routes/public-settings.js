const express = require('express');
const router = express.Router();
const db = require('../utils/database');

// Get public system settings (no auth required)
router.get('/', async (req, res) => {
  try {
    // Get settings from database
    const settings = await db.query('SELECT * FROM settings ORDER BY id DESC LIMIT 1');
    
    if (settings.rows.length === 0) {
      // Create default settings if none exist
      const defaultSettings = {
        creditCostPerImage: 10,
        maxFreeCredits: 50,
        stripeEnabled: false,
        enhancedPromptEnabled: true,
        enhancedPromptCost: 0,
        aiProvider: 'openrouter'
      };
      
      const result = await db.query(
        `INSERT INTO settings 
         (credit_cost_per_image, max_free_credits, stripe_enabled, 
          enhanced_prompt_enabled, enhanced_prompt_cost, ai_provider) 
         VALUES ($1, $2, $3, $4, $5, $6) RETURNING *`,
        [
          defaultSettings.creditCostPerImage,
          defaultSettings.maxFreeCredits,
          defaultSettings.stripeEnabled,
          defaultSettings.enhancedPromptEnabled,
          defaultSettings.enhancedPromptCost,
          defaultSettings.aiProvider
        ]
      );
      
      // Format the response to match the frontend expected format
      const formattedSettings = {
        creditCostPerImage: result.rows[0].credit_cost_per_image,
        maxFreeCredits: result.rows[0].max_free_credits,
        stripeEnabled: result.rows[0].stripe_enabled,
        enhancedPromptEnabled: result.rows[0].enhanced_prompt_enabled,
        enhancedPromptCost: result.rows[0].enhanced_prompt_cost,
        aiProvider: result.rows[0].ai_provider
      };
      
      return res.status(200).json({ 
        success: true, 
        data: { settings: formattedSettings } 
      });
    }
    
    // Format the response to match the frontend expected format
    const formattedSettings = {
      creditCostPerImage: settings.rows[0].credit_cost_per_image,
      maxFreeCredits: settings.rows[0].max_free_credits,
      stripeEnabled: settings.rows[0].stripe_enabled,
      enhancedPromptEnabled: settings.rows[0].enhanced_prompt_enabled,
      enhancedPromptCost: settings.rows[0].enhanced_prompt_cost,
      aiProvider: settings.rows[0].ai_provider
    };
    
    res.status(200).json({ 
      success: true, 
      data: { settings: formattedSettings } 
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
