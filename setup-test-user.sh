#!/bin/bash

# Script to create and verify a test user for development
# Usage: ./setup-test-user.sh <email> <password> <name>

EMAIL=${1:-"test@example.com"}
PASSWORD=${2:-"password123"}
NAME=${3:-"Test User"}
BACKEND_URL="http://localhost:3011/api"

echo "===== Creating Test User for PictureThis Development ====="
echo "Email: $EMAIL"
echo "Name: $NAME"
echo "Backend URL: $BACKEND_URL"

# Check if backend is running
echo "Checking if backend is running..."
if ! curl -s "$BACKEND_URL/health" > /dev/null; then
  echo "Error: Backend is not running at $BACKEND_URL. Start the backend first."
  exit 1
fi
echo "✓ Backend is running"

# Create user
echo "Creating user..."
REGISTER_RESPONSE=$(curl -s -X POST "$BACKEND_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\",\"name\":\"$NAME\"}")

if [[ $REGISTER_RESPONSE == *"already exists"* ]]; then
  echo "User already exists, continuing..."
else
  echo "$REGISTER_RESPONSE"
  echo "✓ User registration request sent"
fi

# Verify user
echo "Verifying user..."
VERIFY_RESPONSE=$(curl -s -X POST "$BACKEND_URL/dev/verify-user" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\"}")

echo "$VERIFY_RESPONSE"
echo "✓ User verification complete"

# Add credits
echo "Adding test credits..."

# Login to get token
echo "Logging in to get auth token..."
LOGIN_RESPONSE=$(curl -s -X POST "$BACKEND_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

# Extract token using grep and cut (basic parsing)
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | sed 's/"token":"//')

if [ -z "$TOKEN" ]; then
  echo "Failed to get auth token. Please check credentials."
  exit 1
fi

# Add 100 credits to user account
CREDITS_RESPONSE=$(curl -s -X POST "$BACKEND_URL/admin/users/credits" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d "{\"email\":\"$EMAIL\",\"credits\":100,\"reason\":\"Development testing\"}")

echo "$CREDITS_RESPONSE"
echo "✓ Credits added"

echo "===== Setup Complete ====="
echo "You can now log in with:"
echo "Email: $EMAIL"
echo "Password: $PASSWORD"
echo "The user has verified email and 100 test credits"
