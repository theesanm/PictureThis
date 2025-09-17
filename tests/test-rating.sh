#!/bin/bash

echo "Testing updated prompt enhancement with ratings..."

timeout 10 curl -s -X POST http://localhost:3011/api/prompts/enhance \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer demo-token" \
  -d '{"prompt": "sunset mountain"}' | jq '.data.enhancedPrompts[0:3]' || echo "Request failed or timed out"
