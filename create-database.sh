#!/bin/bash

# Script to create the PostgreSQL database if it doesn't exist
# Load environment variables from .env
if [ -f ".env" ]; then
  export $(grep -v '^#' .env | xargs)
else
  echo "Error: .env file not found"
  exit 1
fi

# Check if PostgreSQL is running
if ! command -v pg_isready &> /dev/null; then
  echo "Error: PostgreSQL client utilities not found. Please install PostgreSQL."
  exit 1
fi

# Check if PostgreSQL server is running
pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USER > /dev/null 2>&1
if [ $? -ne 0 ]; then
  echo "Error: PostgreSQL server is not running or not accessible."
  echo "Please ensure PostgreSQL is running at $DB_HOST:$DB_PORT"
  exit 1
fi

# Check if the database exists
echo "Checking if database '$DB_NAME' exists..."
DATABASE_EXISTS=$(PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -p $DB_PORT -U $DB_USER -lqt | cut -d \| -f 1 | grep -w $DB_NAME | wc -l)

if [ $DATABASE_EXISTS -eq 0 ]; then
  echo "Database '$DB_NAME' does not exist. Creating it now..."
  PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -p $DB_PORT -U $DB_USER -c "CREATE DATABASE $DB_NAME;"
  if [ $? -eq 0 ]; then
    echo "Database '$DB_NAME' created successfully."
  else
    echo "Error: Failed to create database '$DB_NAME'."
    exit 1
  fi
else
  echo "Database '$DB_NAME' already exists."
fi

echo "Database check complete."
exit 0
