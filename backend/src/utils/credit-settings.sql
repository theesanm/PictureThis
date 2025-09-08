-- Credit settings SQL script

-- Create settings table if not exists
CREATE TABLE IF NOT EXISTS settings (
  id SERIAL PRIMARY KEY,
  key VARCHAR(255) NOT NULL UNIQUE,
  value TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create default credit settings
-- Credits per image generation
INSERT INTO settings (key, value) 
VALUES ('creditCostPerImage', '1')
ON CONFLICT (key) DO UPDATE 
SET value = '1', updated_at = CURRENT_TIMESTAMP;

-- Credits per prompt enhancement
INSERT INTO settings (key, value) 
VALUES ('creditCostPerEnhancement', '1')
ON CONFLICT (key) DO UPDATE 
SET value = '1', updated_at = CURRENT_TIMESTAMP;

-- Initial credits for new users
INSERT INTO settings (key, value) 
VALUES ('initialUserCredits', '10')
ON CONFLICT (key) DO UPDATE 
SET value = '10', updated_at = CURRENT_TIMESTAMP;

-- Enable/disable prompt enhancement feature
INSERT INTO settings (key, value) 
VALUES ('enablePromptEnhancement', 'true')
ON CONFLICT (key) DO UPDATE 
SET value = 'true', updated_at = CURRENT_TIMESTAMP;

-- Credit description text for UI
INSERT INTO settings (key, value) 
VALUES ('creditDescriptionText', '1 credit = 1 image generation')
ON CONFLICT (key) DO UPDATE 
SET value = '1 credit = 1 image generation', updated_at = CURRENT_TIMESTAMP;
