#!/bin/bash

echo "Testing prompt enhancement with longer timeout for free models..."
echo "This may take up to 60 seconds - please wait..."

start_time=$(date +%s)

response=$(curl -s -X POST http://localhost:3011/api/prompts/enhance \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer demo-token" \
  -d '{"prompt": "beautiful landscape"}')

end_time=$(date +%s)
elapsed=$((end_time - start_time))

echo ""
echo "Response received after ${elapsed} seconds"
echo ""

# Check if we got a valid response
if echo "$response" | jq '.success' >/dev/null 2>&1; then
    echo "✅ Success! Response structure:"
    echo "$response" | jq '.data.enhancedPrompts | length'
    echo "Number of prompts returned"
    echo ""
    echo "First 3 prompts with ratings:"
    echo "$response" | jq '.data.enhancedPrompts[0:3]'
else
    echo "❌ Error or invalid response:"
    echo "$response"
fi
