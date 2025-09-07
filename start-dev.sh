#!/bin/bash

# Define text colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the absolute path of the project directory (where this script is located)
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Print header
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   PictureThis - Development Starter    ${NC}"
echo -e "${BLUE}========================================${NC}"
echo -e "${YELLOW}Project directory: $PROJECT_DIR${NC}"

# Function to check if a port is in use
is_port_in_use() {
  lsof -i:"$1" &> /dev/null
}

# Check if ports are available
if is_port_in_use 3010; then
  echo -e "${RED}Error: Port 3010 is already in use. Cannot start frontend.${NC}"
  frontend_available=false
else
  frontend_available=true
fi

if is_port_in_use 3011; then
  echo -e "${RED}Error: Port 3011 is already in use. Cannot start backend.${NC}"
  backend_available=false
else
  backend_available=true
fi

# Kill existing processes if needed
echo -e "\n${YELLOW}Preparing environment...${NC}"

# Check if we should proceed
if [ "$frontend_available" = false ] || [ "$backend_available" = false ]; then
  echo -e "${RED}Cannot start one or more servers due to port conflicts.${NC}"
  echo -e "Please free up the required ports and try again."
  exit 1
fi

# Run database migrations
echo -e "\n${YELLOW}Running database migrations...${NC}"
cd "${PROJECT_DIR}/backend" && node src/utils/migrate.js
if [ $? -ne 0 ]; then
    echo -e "${RED}Database migration failed! Check error messages above.${NC}"
    exit 1
fi

# Start backend
echo -e "\n${YELLOW}Starting backend server on port 3011...${NC}"
cd "${PROJECT_DIR}/backend" && NODE_ENV=development PORT=3011 node src/server.js &
backend_pid=$!

# Give the backend a moment to start
sleep 2

# Check if backend started successfully
if ! is_port_in_use 3011; then
  echo -e "${RED}Failed to start backend server.${NC}"
  exit 1
fi

# Start frontend (only using the picfe directory for the new Next.js app)
echo -e "\n${YELLOW}Starting frontend server on port 3010...${NC}"

# Check for the picfe directory (only use the Next.js frontend)
if [ -d "${PROJECT_DIR}/picfe" ]; then
  echo -e "${GREEN}Found picfe directory at ${PROJECT_DIR}/picfe${NC}"
  cd "${PROJECT_DIR}/picfe" && npm run dev -- --port 3010 &
  frontend_pid=$!
else
  echo -e "${RED}Error: 'picfe' directory not found!${NC}"
  echo -e "${YELLOW}Available directories at $PROJECT_DIR:${NC}"
  ls -la "${PROJECT_DIR}" | grep -v "node_modules"
  exit 1
fi

# Wait for a moment to see if frontend started
sleep 5

# Print success message if both started
echo -e "\n${GREEN}PictureThis development environment is running!${NC}"
echo -e "${GREEN}----------------------------------------------${NC}"
echo -e "Frontend: ${BLUE}http://localhost:3010${NC}"
echo -e "Backend:  ${BLUE}http://localhost:3011${NC}"
echo -e "API Health Check: ${BLUE}http://localhost:3011/api/health${NC}"
echo -e "\n${YELLOW}Press Ctrl+C to stop all servers${NC}"

# Trap to kill both processes when script is terminated
trap "kill $backend_pid $frontend_pid &> /dev/null; echo -e '\n${GREEN}Servers stopped.${NC}'; exit" INT

# Wait for a terminate signal
wait
