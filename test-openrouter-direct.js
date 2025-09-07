const fs = require('fs');

// Test OpenRouter API call directly
async function testOpenRouter() {
  try {
    console.log('Testing OpenRouter API...');
    
    const requestBody = {
      model: "openai/gpt-oss-20b:free",
      messages: [
        {
          role: "system",
          content: "Return only a valid JSON array with 3 objects. Each object should have 'rating' and 'prompt' fields. No other text."
        },
        {
          role: "user",
          content: "Create 3 simple test prompts for 'sunset'"
        }
      ],
      max_tokens: 500,
      temperature: 0.3
    };

    const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${process.env.OPENROUTER_API_KEY}`,
        'Content-Type': 'application/json',
        'HTTP-Referer': 'http://localhost:3011',
        'X-Title': 'PictureThis Test'
      },
      body: JSON.stringify(requestBody)
    });

    if (!response.ok) {
      throw new Error(`API error: ${response.status} ${response.statusText}`);
    }

    const data = await response.json();
    console.log('Raw response:', JSON.stringify(data, null, 2));
    
    const content = data.choices[0].message.content;
    console.log('Content:', content);
    
    // Try to parse as JSON
    try {
      const parsed = JSON.parse(content);
      console.log('Successfully parsed JSON:', parsed);
    } catch (e) {
      console.log('Failed to parse JSON:', e.message);
    }

  } catch (error) {
    console.error('Test failed:', error);
  }
}

// Load environment variables
require('dotenv').config({ path: '/Volumes/MacM4Ext/Projects/PictureThis/PictureThis/backend/.env' });

testOpenRouter();
