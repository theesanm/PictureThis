# PictureThis - Docker Deployment

This guide explains how to deploy PictureThis using Docker containers.

## 🏗️ Architecture

The application consists of three main services:
- **Frontend**: Next.js application (Port 3000)
- **Backend**: Node.js/Express API (Port 3011)
- **Database**: PostgreSQL (Port 5432)
- **Proxy**: Nginx reverse proxy (Port 80)

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose installed
- At least 4GB RAM available
- Git

### 1. Clone and Setup
```bash
git clone <your-repo-url>
cd picturethis
```

### 2. Environment Configuration
```bash
# Copy environment template
cp .env.docker .env

# Edit with your actual values
nano .env
```

### 3. Build and Run
```bash
# Build and start all services
docker-compose up --build

# Or run in background
docker-compose up -d --build
```

### 4. Access Your Application
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:3011
- **PostgreSQL**: localhost:5432

## 📋 Environment Variables

### Required Variables
```bash
# Database
POSTGRES_DB=picturethis
POSTGRES_USER=picturethis_user
POSTGRES_PASSWORD=your_secure_password

# JWT
JWT_SECRET=your_jwt_secret_here

# OpenAI
OPENAI_API_KEY=your_openai_api_key

# PayFast (for payments)
PAYFAST_MERCHANT_ID=your_merchant_id
PAYFAST_MERCHANT_KEY=your_merchant_key
PAYFAST_PASSPHRASE=your_passphrase
```

## 🛠️ Development vs Production

### Development Mode (with hot reload)
```bash
# Uses docker-compose.override.yml automatically
docker-compose up --build
```

### Production Mode
```bash
# Uses only docker-compose.yml
docker-compose -f docker-compose.yml up --build
```

## 🔧 Docker Commands

### Build Images
```bash
docker-compose build
```

### Start Services
```bash
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f backend
```

### Rebuild Specific Service
```bash
docker-compose up --build backend
```

## 📊 Database Management

### Access PostgreSQL
```bash
docker-compose exec postgres psql -U picturethis_user -d picturethis
```

### Backup Database
```bash
docker-compose exec postgres pg_dump -U picturethis_user picturethis > backup.sql
```

### Restore Database
```bash
docker-compose exec -T postgres psql -U picturethis_user -d picturethis < backup.sql
```

## 🌐 Production Deployment

### 1. Cloud Platforms

#### AWS ECS/Fargate
```bash
# Build and push to ECR
aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin <account>.dkr.ecr.us-east-1.amazonaws.com

# Tag images
docker tag picturethis_frontend <account>.dkr.ecr.us-east-1.amazonaws.com/picturethis:frontend
docker tag picturethis_backend <account>.dkr.ecr.us-east-1.amazonaws.com/picturethis:backend

# Push images
docker push <account>.dkr.ecr.us-east-1.amazonaws.com/picturethis:frontend
docker push <account>.dkr.ecr.us-east-1.amazonaws.com/picturethis:backend
```

#### Google Cloud Run
```bash
# Build and push to GCR
gcloud builds submit --tag gcr.io/<project>/picturethis

# Deploy
gcloud run deploy picturethis --image gcr.io/<project>/picturethis --platform managed
```

#### DigitalOcean App Platform
```bash
# Use the provided docker-compose.yml
# App Platform will automatically detect and deploy
```

### 2. SSL/TLS Setup
```bash
# Add SSL certificates to nginx/ssl directory
# Update nginx.conf for HTTPS
```

### 3. Scaling
```bash
# Scale backend services
docker-compose up -d --scale backend=3

# Use load balancer for frontend
```

## 🔍 Troubleshooting

### Common Issues

#### Port Conflicts
```bash
# Check what's using ports
lsof -i :3000
lsof -i :3011
lsof -i :5432

# Change ports in docker-compose.yml if needed
```

#### Database Connection Issues
```bash
# Check database logs
docker-compose logs postgres

# Verify connection
docker-compose exec backend curl http://postgres:5432
```

#### Build Failures
```bash
# Clear Docker cache
docker system prune -a

# Rebuild without cache
docker-compose build --no-cache
```

#### Memory Issues
```bash
# Increase Docker memory limit
# Docker Desktop: Preferences > Resources > Memory
```

## 📁 File Structure

```
picturethis/
├── docker-compose.yml          # Main compose file
├── docker-compose.override.yml # Development overrides
├── Dockerfile                  # Frontend Dockerfile
├── nginx.conf                  # Nginx configuration
├── .env.docker                 # Environment template
├── .dockerignore              # Docker ignore rules
├── picfe/                     # Frontend (Next.js)
│   ├── Dockerfile
│   ├── next.config.mjs
│   └── ...
└── backend/                   # Backend (Node.js)
    ├── Dockerfile
    ├── src/
    └── ...
```

## 🔐 Security Considerations

- Change all default passwords
- Use strong JWT secrets
- Enable HTTPS in production
- Regularly update base images
- Use secrets management for sensitive data
- Implement proper firewall rules

## 📞 Support

For issues or questions:
1. Check the logs: `docker-compose logs`
2. Verify environment variables
3. Ensure all required services are running
4. Check network connectivity between containers
