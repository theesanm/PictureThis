const express = require('express');
const { authenticateToken, requireAdmin } = require('../middleware/auth');
const { query } = require('../utils/database');

const router = express.Router();

// Apply admin middleware to all routes
router.use(authenticateToken);
router.use(requireAdmin);

// Get all system settings
router.get('/', async (req, res) => {
  try {
    // Get the settings record (there's only one row in this table)
    const result = await query('SELECT * FROM settings LIMIT 1');

    if (result.rows.length === 0) {
      // Create default settings if none exist
      const defaultSettings = {
        creditCostPerImage: 10,
        maxFreeCredits: 50,
        stripeEnabled: false,
        enhancedPromptEnabled: true,
        enhancedPromptCost: 0,
        aiProvider: 'openrouter'
      };

      res.json({
        success: true,
        data: {
          settings: defaultSettings
        }
      });
      return;
    }

    const settings = result.rows[0];

    res.json({
      success: true,
      data: {
        settings: {
          creditCostPerImage: settings.credit_cost_per_image || 10,
          maxFreeCredits: settings.max_free_credits || 50,
          stripeEnabled: settings.stripe_enabled || false,
          enhancedPromptEnabled: settings.enhanced_prompt_enabled !== false,
          enhancedPromptCost: settings.enhanced_prompt_cost || 0,
          aiProvider: settings.ai_provider || 'openrouter'
        }
      }
    });
  } catch (error) {
    console.error('System settings fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch system settings'
    });
  }
});

// Update system settings
router.put('/', async (req, res) => {
  try {
    const { settings } = req.body;

    if (!settings) {
      return res.status(400).json({
        success: false,
        message: 'Settings data is required'
      });
    }

    // Check if settings exist, if not create them
    const existingSettings = await query('SELECT * FROM settings LIMIT 1');

    if (existingSettings.rows.length === 0) {
      // Create settings
      await query(
        `INSERT INTO settings
         (credit_cost_per_image, max_free_credits, stripe_enabled,
          enhanced_prompt_enabled, enhanced_prompt_cost, ai_provider)
         VALUES ($1, $2, $3, $4, $5, $6)`,
        [
          settings.creditCostPerImage || 10,
          settings.maxFreeCredits || 50,
          settings.stripeEnabled || false,
          settings.enhancedPromptEnabled !== false,
          settings.enhancedPromptCost || 0,
          settings.aiProvider || 'openrouter'
        ]
      );
    } else {
      // Update existing settings
      await query(
        `UPDATE settings
         SET credit_cost_per_image = $1,
             max_free_credits = $2,
             stripe_enabled = $3,
             enhanced_prompt_enabled = $4,
             enhanced_prompt_cost = $5,
             ai_provider = $6,
             updated_at = NOW()
         WHERE id = $7`,
        [
          settings.creditCostPerImage,
          settings.maxFreeCredits,
          settings.stripeEnabled,
          settings.enhancedPromptEnabled,
          settings.enhancedPromptCost,
          settings.aiProvider,
          existingSettings.rows[0].id
        ]
      );
    }

    res.json({
      success: true,
      message: 'System settings updated successfully'
    });
  } catch (error) {
    console.error('System settings update error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update system settings'
    });
  }
});

// Get credit settings (alias for credit-settings)
router.get('/credits', async (req, res) => {
  try {
    // Get the settings record (there's only one row in this table)
    const result = await query('SELECT * FROM settings LIMIT 1');

    if (result.rows.length === 0) {
      // Create default settings if none exist
      const defaultSettings = {
        creditCostPerImage: 1,
        creditCostPerEnhancement: 1,
        initialUserCredits: 10,
        enablePromptEnhancement: true
      };

      res.json({
        success: true,
        data: {
          settings: defaultSettings
        }
      });
      return;
    }

    const settings = result.rows[0];

    res.json({
      success: true,
      data: {
        settings: {
          creditCostPerImage: settings.credit_cost_per_image || 1,
          creditCostPerEnhancement: settings.enhanced_prompt_cost || 1,
          initialUserCredits: settings.max_free_credits || 10,
          enablePromptEnhancement: settings.enhanced_prompt_enabled !== false
        }
      }
    });
  } catch (error) {
    console.error('Credit settings fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch credit settings'
    });
  }
});

// Update credit settings (alias for credit-settings)
router.put('/credits', async (req, res) => {
  try {
    const { creditCostPerImage, creditCostPerEnhancement, initialUserCredits, enablePromptEnhancement } = req.body;

    // Validate inputs
    if (creditCostPerImage === undefined ||
        creditCostPerEnhancement === undefined ||
        initialUserCredits === undefined) {
      return res.status(400).json({
        success: false,
        message: 'All credit settings are required'
      });
    }

    // Validate numeric values
    if (creditCostPerImage < 0 ||
        creditCostPerEnhancement < 0 ||
        initialUserCredits < 0) {
      return res.status(400).json({
        success: false,
        message: 'Credit values cannot be negative'
      });
    }

    // Check if settings exist, if not create them
    const existingSettings = await query('SELECT * FROM settings LIMIT 1');

    if (existingSettings.rows.length === 0) {
      // Create settings
      await query(
        `INSERT INTO settings
         (credit_cost_per_image, max_free_credits, stripe_enabled,
          enhanced_prompt_enabled, enhanced_prompt_cost, ai_provider)
         VALUES ($1, $2, $3, $4, $5, $6)`,
        [
          creditCostPerImage,
          initialUserCredits,
          false, // stripe_enabled
          enablePromptEnhancement || true,
          creditCostPerEnhancement,
          'openrouter' // ai_provider
        ]
      );
    } else {
      // Update existing settings
      await query(
        `UPDATE settings
         SET credit_cost_per_image = $1,
             max_free_credits = $2,
             enhanced_prompt_enabled = $3,
             enhanced_prompt_cost = $4,
             updated_at = NOW()
         WHERE id = $5`,
        [
          creditCostPerImage,
          initialUserCredits,
          enablePromptEnhancement || true,
          creditCostPerEnhancement,
          existingSettings.rows[0].id
        ]
      );
    }

    res.json({
      success: true,
      message: 'Credit settings updated successfully'
    });
  } catch (error) {
    console.error('Credit settings update error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update credit settings'
    });
  }
});

// Get all credit settings
router.get('/credit-settings', async (req, res) => {
  try {
    // Get all credit-related settings
    const result = await query(`
      SELECT key, value 
      FROM settings 
      WHERE key IN ('creditCostPerImage', 'creditCostPerEnhancement', 'initialUserCredits', 'enablePromptEnhancement')
    `);
    
    // Convert settings to a more usable object
    const settings = {};
    result.rows.forEach(row => {
      // Try to parse numbers for numeric settings
      if (['creditCostPerImage', 'creditCostPerEnhancement', 'initialUserCredits'].includes(row.key)) {
        settings[row.key] = parseInt(row.value) || 0;
      } else if (row.key === 'enablePromptEnhancement') {
        settings[row.key] = row.value === 'true';
      } else {
        settings[row.key] = row.value;
      }
    });
    
    res.json({
      success: true,
      data: {
        settings: {
          creditCostPerImage: settings.creditCostPerImage || 1,
          creditCostPerEnhancement: settings.creditCostPerEnhancement || 1,
          initialUserCredits: settings.initialUserCredits || 10,
          enablePromptEnhancement: settings.enablePromptEnhancement !== false
        }
      }
    });
  } catch (error) {
    console.error('Credit settings fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch credit settings'
    });
  }
});

// Update credit settings
router.put('/credit-settings', async (req, res) => {
  try {
    const { creditCostPerImage, creditCostPerEnhancement, initialUserCredits, enablePromptEnhancement } = req.body;
    
    // Validate inputs
    if (creditCostPerImage === undefined || 
        creditCostPerEnhancement === undefined || 
        initialUserCredits === undefined) {
      return res.status(400).json({
        success: false,
        message: 'All credit settings are required'
      });
    }
    
    // Validate numeric values
    if (creditCostPerImage < 0 || 
        creditCostPerEnhancement < 0 || 
        initialUserCredits < 0) {
      return res.status(400).json({
        success: false,
        message: 'Credit values cannot be negative'
      });
    }
    
    // Start a transaction
    await query('BEGIN');
    
    // Update settings
    await query(
      `INSERT INTO settings (key, value) VALUES ($1, $2) 
       ON CONFLICT (key) DO UPDATE SET value = $2, updated_at = CURRENT_TIMESTAMP`,
      ['creditCostPerImage', String(creditCostPerImage)]
    );
    
    await query(
      `INSERT INTO settings (key, value) VALUES ($1, $2) 
       ON CONFLICT (key) DO UPDATE SET value = $2, updated_at = CURRENT_TIMESTAMP`,
      ['creditCostPerEnhancement', String(creditCostPerEnhancement)]
    );
    
    await query(
      `INSERT INTO settings (key, value) VALUES ($1, $2) 
       ON CONFLICT (key) DO UPDATE SET value = $2, updated_at = CURRENT_TIMESTAMP`,
      ['initialUserCredits', String(initialUserCredits)]
    );
    
    if (enablePromptEnhancement !== undefined) {
      await query(
        `INSERT INTO settings (key, value) VALUES ($1, $2) 
         ON CONFLICT (key) DO UPDATE SET value = $2, updated_at = CURRENT_TIMESTAMP`,
        ['enablePromptEnhancement', enablePromptEnhancement ? 'true' : 'false']
      );
    }
    
    // Commit transaction
    await query('COMMIT');
    
    res.json({
      success: true,
      message: 'Credit settings updated successfully'
    });
  } catch (error) {
    // Rollback on error
    await query('ROLLBACK');
    
    console.error('Credit settings update error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update credit settings'
    });
  }
});

module.exports = router;
