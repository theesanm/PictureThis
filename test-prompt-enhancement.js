// Simple test script for prompt enhancement
const testPromptEnhancement = async () => {
  try {
    console.log('Testing prompt enhancement...');
    
    const response = await fetch('http://localhost:3011/api/prompts/enhance', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer demo-token'
      },
      body: JSON.stringify({ prompt: 'sunset mountain' })
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();
    console.log('✅ Success!');
    console.log('Original prompt:', result.data.originalPrompt);
    console.log('Enhanced prompts count:', result.data.enhancedPrompts.length);
    console.log('First enhanced prompt:', result.data.enhancedPrompts[0]);
    
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
};

// Run the test
testPromptEnhancement();
