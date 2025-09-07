#!/bin/bash

# Apply credit settings to the database
echo "Applying credit settings migration..."

# Get directory of this script
DIR="$(dirname "$0")"
DB_HOST=$(grep DB_HOST "$DIR/../../.env" | cut -d '=' -f2)
DB_PORT=$(grep DB_PORT "$DIR/../../.env" | cut -d '=' -f2)
DB_NAME=$(grep DB_NAME "$DIR/../../.env" | cut -d '=' -f2)
DB_USER=$(grep DB_USER "$DIR/../../.env" | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD "$DIR/../../.env" | cut -d '=' -f2)

# Check if all variables are set
if [ -z "$DB_HOST" ] || [ -z "$DB_PORT" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASSWORD" ]; then
  echo "Error: Database configuration not found or incomplete in .env file"
  exit 1
fi

echo "Using database configuration:"
echo "Host: $DB_HOST"
echo "Port: $DB_PORT"
echo "Database: $DB_NAME"
echo "User: $DB_USER"

# Execute the credit settings SQL script
PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -d "$DB_NAME" -U "$DB_USER" -f "$DIR/credit-settings.sql"

if [ $? -eq 0 ]; then
  echo "✅ Credit settings migration applied successfully!"
else
  echo "❌ Error applying credit settings migration"
  exit 1
fi
