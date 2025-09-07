#!/bin/bash

# Script to verify a user's email for development purposes

EMAIL=$1

if [ -z "$EMAIL" ]; then
  echo "Usage: $0 <email@example.com>"
  exit 1
fi

echo "Verifying email for: $EMAIL"

# Send request to dev endpoint
curl -X POST \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\"}" \
  http://localhost:3011/api/dev/verify-user

echo
echo "If successful, the user should now be verified and able to access protected resources."
