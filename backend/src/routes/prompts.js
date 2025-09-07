const express = require('express');
const { authenticateToken } = require('../middleware/auth');

const router = express.Router();

// Enhance prompt using OpenRouter LLM
router.post('/enhance', authenticateToken, async (req, res) => {
  try {
    const { prompt } = req.body;
    const userId = req.user.userId;

    if (!prompt || prompt.trim().length === 0) {
      return res.status(400).json({
        success: false,
        message: 'Prompt is required'
      });
    }

    console.log('Enhancing prompt:', prompt);
    
    // Get credit cost from request or settings
    const db = require('../utils/database');
    let enhancedPromptCost = req.body.creditCost ? parseInt(req.body.creditCost) : null;
    
    // If not provided in the request, get from settings
    if (!enhancedPromptCost) {
      const settingsResult = await db.query('SELECT enhanced_prompt_cost FROM settings LIMIT 1');
      enhancedPromptCost = settingsResult.rows.length > 0 ? parseInt(settingsResult.rows[0].enhanced_prompt_cost) : 1;
    }
    
    // Check if prompt enhancement feature is enabled
    const featureResult = await db.query('SELECT enhanced_prompt_enabled FROM settings LIMIT 1');
    const isFeatureEnabled = featureResult.rows.length > 0 ? featureResult.rows[0].enhanced_prompt_enabled : true;
    
    if (!isFeatureEnabled) {
      return res.status(403).json({
        success: false,
        message: 'Enhanced prompts are currently disabled'
      });
    }
    
    // If cost is greater than 0, check user credits
    if (enhancedPromptCost > 0) {
      const userResult = await db.query('SELECT credits FROM users WHERE id = $1', [userId]);
      const userCredits = parseInt(userResult.rows[0]?.credits) || 0;
      
      if (userCredits < enhancedPromptCost) {
        return res.status(403).json({
          success: false,
          message: `Insufficient credits. You need ${enhancedPromptCost} credits to enhance prompts.`,
          data: {
            available: userCredits,
            required: enhancedPromptCost
          }
        });
      }
      
      // Deduct credits and record transaction
      await db.query('UPDATE users SET credits = credits - $1 WHERE id = $2', [enhancedPromptCost, userId]);
      await db.query(
        'INSERT INTO credit_transactions (user_id, amount, transaction_type, description) VALUES ($1, $2, $3, $4)',
        [userId, -enhancedPromptCost, 'usage', 'Prompt enhancement']
      );
    }

    // Call OpenRouter API
    const enhancedPrompts = await enhancePromptWithLLM(prompt);

    res.json({
      success: true,
      data: {
        originalPrompt: prompt,
        enhancedPrompts: enhancedPrompts
      }
    });

  } catch (error) {
    console.error('Prompt enhancement error:', error);
    
    // Always return fallback prompts, never fail completely
    const fallbackPrompts = [
      `${prompt}, highly detailed, photorealistic, professional photography, dramatic lighting, sharp focus, masterpiece quality`,
      `${prompt}, digital art style, vibrant colors, concept art, artstation trending, detailed textures, cinematic composition`,
      `${prompt}, oil painting style, rich colors, detailed brushwork, classical art composition, golden hour lighting`,
      `${prompt}, fantasy art style, magical atmosphere, ethereal lighting, detailed environment, atmospheric perspective`,
      `${prompt}, modern minimalist style, clean lines, balanced composition, soft lighting, professional quality`
    ];
    
    res.json({
      success: true,
      data: {
        originalPrompt: prompt,
        enhancedPrompts: fallbackPrompts,
        fallback: true,
        message: 'Using fallback prompts due to service unavailability'
      }
    });
  }
});

async function enhancePromptWithLLM(userPrompt) {
  const startTime = Date.now();
  try {
    const systemPrompt = `You are a professional AI image generation prompt engineer. Your job is to take a simple user prompt and enhance it to match the tone and style, then create 5 detailed, enhanced prompts that will generate stunning, high-quality images.

Guidelines:
- Analyze the user's input tone, style, and intent to enhance it appropriately
- Create 5 unique, detailed prompts that expand on the user's concept
- Each prompt should be very detailed and descriptive (60-120 words)
- Include artistic styles, lighting, composition, colors, and mood
- Make each prompt unique while staying true to the original concept
- Focus on visual details that AI image generators understand well
- Include technical photography/art terms when appropriate
- Rate each prompt from 1-5 based on quality and potential for stunning results
- Return prompts ordered by rating (1 = best, 5 = good but less optimal)

CRITICAL: Your response must be ONLY valid JSON in this exact format:
[
  {"rating": 1, "prompt": "most detailed and best prompt here"},
  {"rating": 2, "prompt": "second best prompt here"},
  {"rating": 3, "prompt": "third best prompt here"},
  {"rating": 4, "prompt": "fourth best prompt here"},
  {"rating": 5, "prompt": "fifth prompt here"}
]

Do not include any text before or after the JSON array. No markdown formatting, no explanations, just pure JSON.`;

    const userMessage = `Original prompt: "${userPrompt}"

Please enhance this prompt to match its tone and style, then create 5 detailed, enhanced prompts for AI image generation. Rate each prompt 1-5 (1 being the best) and return them ordered by rating.`;

    const requestBody = {
      model: process.env.OPENROUTER_MODEL || "anthropic/claude-3-haiku",
      messages: [
        {
          role: "system",
          content: systemPrompt
        },
        {
          role: "user",
          content: userMessage
        }
      ],
      max_tokens: 4000,
      temperature: 0.8
    };

    // Create an AbortController for timeout handling
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 60000); // 60 second timeout for free models

    console.log('Calling OpenRouter API... (this may take up to 60 seconds for free models)');

    const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${process.env.OPENROUTER_API_KEY}`,
        'Content-Type': 'application/json',
        'HTTP-Referer': process.env.SITE_URL || 'http://localhost:3011',
        'X-Title': 'PictureThis AI Image Generator'
      },
      body: JSON.stringify(requestBody),
      signal: controller.signal
    });

    // Clear the timeout since we got a response
    clearTimeout(timeoutId);

    if (!response.ok) {
      const errorText = await response.text().catch(() => 'Unknown error');
      console.error(`OpenRouter API error: ${response.status} ${response.statusText}`);
      console.error('Error details:', errorText);
      throw new Error(`OpenRouter API error: ${response.status} ${response.statusText}`);
    }

    const data = await response.json();
    const elapsed = Date.now() - startTime;
    console.log(`OpenRouter response received in ${elapsed}ms`);

    const content = data.choices[0].message.content;
    
    // Log the actual response content for debugging
    console.log('=== OPENROUTER RESPONSE CONTENT ===');
    console.log('Content type:', typeof content);
    console.log('Content length:', content ? content.length : 'null');
    console.log('First 500 characters:', content ? content.substring(0, 500) : 'null');
    console.log('Last 200 characters:', content ? content.substring(Math.max(0, content.length - 200)) : 'null');
    console.log('=== END RESPONSE CONTENT ===');
    
    try {
      // Try to parse as JSON
      const enhancedPromptsWithRatings = JSON.parse(content);
      
      if (Array.isArray(enhancedPromptsWithRatings) && enhancedPromptsWithRatings.length > 0) {
        // Check if the response has the expected format with ratings
        if (enhancedPromptsWithRatings[0].rating && enhancedPromptsWithRatings[0].prompt) {
          // Sort by rating (1 = best, 5 = good but less optimal) and return max 10
          const sortedPrompts = enhancedPromptsWithRatings
            .sort((a, b) => a.rating - b.rating)
            .slice(0, 10);
          return sortedPrompts.map(item => item.prompt);
        } else {
          // Fallback: if it's just an array of strings, return as is
          // If it's objects, extract the prompt property; if it's strings, return as is
          const processedPrompts = enhancedPromptsWithRatings.slice(0, 10).map(item => 
            typeof item === 'string' ? item : (item.prompt || item)
          );
          return processedPrompts;
        }
      } else {
        throw new Error('Invalid response format');
      }
    } catch (parseError) {
      console.error('=== JSON PARSE ERROR ===');
      console.error('Parse error:', parseError.message);
      console.error('Attempting to parse:', content ? content.substring(0, 200) + '...' : 'null');
      console.error('=== END PARSE ERROR ===');
      console.error('Failed to parse JSON response, falling back to text processing');
      
      // Fallback: try to extract prompts from text
      const lines = content.split('\n').filter(line => line.trim().length > 20);
      const prompts = lines.slice(0, 5).map((line, index) => ({
        rating: index + 1,
        prompt: line.replace(/^\d+\.?\s*/, '').replace(/^["\-\*]\s*/, '').replace(/["]*$/, '').trim()
      }));
      
      return prompts.length > 0 ? prompts.map(item => item.prompt) : [
        `${userPrompt}, highly detailed, photorealistic, professional photography, dramatic lighting, sharp focus, masterpiece quality`,
        `${userPrompt}, digital art style, vibrant colors, concept art, artstation trending, detailed textures, cinematic composition`,
        `${userPrompt}, oil painting style, rich colors, detailed brushwork, classical art composition, golden hour lighting`,
        `${userPrompt}, fantasy art style, magical atmosphere, ethereal lighting, detailed environment, atmospheric perspective`,
        `${userPrompt}, modern minimalist style, clean lines, balanced composition, soft lighting, professional quality`
      ];
    }

  } catch (error) {
    console.error('LLM enhancement failed:', error);
    
    // Handle specific error types
    if (error.name === 'AbortError') {
      console.error('Request timed out after 60 seconds - using fallback prompts');
    } else if (error.message.includes('fetch')) {
      console.error('Network error connecting to OpenRouter - using fallback prompts');
    }
    
    // Fallback prompts if API fails or times out
    return [
      `${userPrompt}, highly detailed, photorealistic, professional photography, dramatic lighting, sharp focus, masterpiece quality`,
      `${userPrompt}, digital art style, vibrant colors, concept art, artstation trending, detailed textures, cinematic composition`,
      `${userPrompt}, oil painting style, rich colors, detailed brushwork, classical art composition, golden hour lighting`,
      `${userPrompt}, fantasy art style, magical atmosphere, ethereal lighting, detailed environment, atmospheric perspective`,
      `${userPrompt}, modern minimalist style, clean lines, balanced composition, soft lighting, professional quality`
    ];
  }
}

module.exports = router;
