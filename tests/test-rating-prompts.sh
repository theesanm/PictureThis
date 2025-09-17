#!/bin/bash

echo "Testing updated prompt enhancement with ratings..."

response=$(curl -s -X POST http://localhost:3011/api/prompts/enhance \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer demo-token" \
  -d '{"prompt": "sunset mountain"}')

echo "Response received:"
echo "$response" | jq '.data.enhancedPrompts[0:3]' 2>/dev/null || echo "$response"

echo ""
echo "Full response structure:"
echo "$response" | jq '.data.enhancedPrompts | length' 2>/dev/null && echo "Number of prompts returned"
