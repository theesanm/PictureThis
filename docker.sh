#!/bin/bash

# PictureThis Docker Management Script
# Usage: ./docker.sh [command]

set -e

COMPOSE_FILE="docker-compose.yml"
if [ "$1" = "prod" ]; then
    COMPOSE_FILE="docker-compose.prod.yml"
    shift
fi

COMMAND=$1

case $COMMAND in
    "build")
        echo "🏗️  Building Docker images..."
        docker-compose -f $COMPOSE_FILE build
        ;;
    "up")
        echo "🚀 Starting services..."
        docker-compose -f $COMPOSE_FILE up -d
        ;;
    "down")
        echo "🛑 Stopping services..."
        docker-compose -f $COMPOSE_FILE down
        ;;
    "logs")
        SERVICE=${2:-""}
        if [ -z "$SERVICE" ]; then
            echo "📋 Showing logs for all services..."
            docker-compose -f $COMPOSE_FILE logs -f
        else
            echo "📋 Showing logs for $SERVICE..."
            docker-compose -f $COMPOSE_FILE logs -f $SERVICE
        fi
        ;;
    "restart")
        SERVICE=${2:-""}
        if [ -z "$SERVICE" ]; then
            echo "🔄 Restarting all services..."
            docker-compose -f $COMPOSE_FILE restart
        else
            echo "🔄 Restarting $SERVICE..."
            docker-compose -f $COMPOSE_FILE restart $SERVICE
        fi
        ;;
    "clean")
        echo "🧹 Cleaning up Docker resources..."
        docker-compose -f $COMPOSE_FILE down -v
        docker system prune -f
        ;;
    "db")
        echo "🗄️  Accessing PostgreSQL..."
        docker-compose -f $COMPOSE_FILE exec postgres psql -U picturethis_user -d picturethis
        ;;
    "status")
        echo "📊 Service Status:"
        docker-compose -f $COMPOSE_FILE ps
        ;;
    "setup")
        echo "⚙️  Setting up PictureThis with Docker..."

        # Check if .env exists
        if [ ! -f .env ]; then
            echo "📋 Creating .env file from template..."
            cp .env.docker .env
            echo "⚠️  Please edit .env file with your actual values before continuing!"
            exit 1
        fi

        echo "🏗️  Building images..."
        docker-compose -f $COMPOSE_FILE build

        echo "🚀 Starting services..."
        docker-compose -f $COMPOSE_FILE up -d

        echo "⏳ Waiting for services to be ready..."
        sleep 30

        echo "✅ Setup complete!"
        echo "🌐 Frontend: http://localhost:3000"
        echo "🔧 Backend: http://localhost:3011"
        ;;
    *)
        echo "🐳 PictureThis Docker Management Script"
        echo ""
        echo "Usage: $0 [prod] <command>"
        echo ""
        echo "Commands:"
        echo "  setup     - Initial setup and start services"
        echo "  build     - Build Docker images"
        echo "  up        - Start all services"
        echo "  down      - Stop all services"
        echo "  logs      - Show logs (optionally specify service)"
        echo "  restart   - Restart services (optionally specify service)"
        echo "  status    - Show service status"
        echo "  db        - Access PostgreSQL database"
        echo "  clean     - Clean up Docker resources"
        echo ""
        echo "Examples:"
        echo "  $0 setup              # Setup for development"
        echo "  $0 prod setup         # Setup for production"
        echo "  $0 logs backend       # Show backend logs"
        echo "  $0 prod up            # Start production services"
        ;;
esac
