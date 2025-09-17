#!/bin/bash
echo "Testing the new free GPT model..."
curl -s -X POST http://localhost:3011/api/prompts/enhance \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer demo-token" \
  -d '{"prompt": "beautiful garden"}' | jq -r '.data.originalPrompt, .data.enhancedPrompts[0]' 2>/dev/null || echo "API call failed"
