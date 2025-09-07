# Picture This - AI Image Generation Platform

An AI-powered image generation platform built with Node.js, Express, PostgreSQL, and React.

## 🚀 Features

- **User Authentication**: Secure registration and login with email verification
- **Credit System**: Purchase and manage credits for image generation
- **AI Image Generation**: Generate images using prompts
- **Image Management**: Upload, store, and download images
- **Admin Dashboard**: System monitoring and configuration
- **Responsive Design**: Works on desktop and mobile devices

## 🏗️ Project Structure

```
PictureThis/
├── backend/                 # Node.js/Express backend
│   ├── src/
│   │   ├── routes/         # API route handlers
│   │   ├── controllers/    # Business logic
│   │   ├── middleware/     # Custom middleware
│   │   ├── models/         # Data models
│   │   ├── utils/          # Utility functions
│   │   └── server.js       # Main server file
│   ├── package.json
│   └── .env                # Environment variables
├── picfe/                  # Next.js/React frontend
│   ├── src/
│   │   ├── app/            # Next.js App Router
│   │   ├── components/     # React components
│   │   └── lib/            # Shared utilities
│   └── package.json
├── frontend/               # (Old version - reference only)
├── development-plan.html   # Development checklist
└── README.md
```

## 🛠️ Quick Start

### Prerequisites

- Node.js (v18 or higher)
- PostgreSQL (v12 or higher)
- npm or yarn

### All-in-one Development Setup

For the quickest start, use the built-in development starter:

```bash
# Start both backend and frontend servers
./start-dev.sh

# In a separate terminal, create a test user
./setup-test-user.sh test@example.com password123 "Test User"
```

This will:
1. Start the PostgreSQL database
2. Run database migrations
3. Start the backend server on port 3011
4. Start the frontend server on port 3010
5. Create a verified test user with 100 credits

### Manual Setup

#### Database Setup

1. Install PostgreSQL and create a database named `picturethis`
2. Update the database credentials in `backend/.env`

#### Backend Setup

```bash
cd backend
npm install
npm run migrate  # Create database tables
npm run dev      # Start development server on port 3011
```

#### Frontend Setup

```bash
cd picfe
npm install
npm run dev      # Start development server on port 3010
```

## 🔧 Environment Variables

The `.env` file in the backend directory contains:

```env
# Database Configuration
DB_HOST=localhost
DB_PORT=5430
DB_NAME=picturethis
DB_USER=postgres
DB_PASSWORD=your_password

# Server Configuration
PORT=3011
NODE_ENV=development

# JWT Configuration
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_EXPIRE=7d

# Email Configuration
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=your_email@example.com
SMTP_PASS=your_email_password
EMAIL_FROM=noreply@picturethis.com

# Image Generation API Keys
OPENAI_API_KEY=your_openai_api_key
OPENROUTER_API_KEY=your_openrouter_api_key
```

## 🧪 Development Utilities

For development and testing, several scripts are provided:

- `./start-dev.sh` - Start the full development environment
- `./setup-test-user.sh <email> <password> <name>` - Create a verified test user with credits
- `./verify-user.sh <email>` - Verify an existing user's email

## 🐛 Troubleshooting

### Authentication Errors (403/401)

If you encounter authentication errors:

1. Check if the user's email is verified (required for protected endpoints)
2. Run `./verify-user.sh your-email@example.com` to manually verify a user
3. Check that your JWT token is valid and not expired

### Image Generation Issues

If image generation fails:

1. Make sure the user has sufficient credits
2. Check that the AI provider API keys are correctly configured in `.env`
3. Verify that the backend can connect to the image generation service

## 📡 API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - User login
- `GET /api/auth/profile` - Get user profile

### Credits
- `GET /api/credits/balance` - Get credit balance
- `POST /api/credits/purchase` - Purchase credits
- `GET /api/credits/history` - Transaction history

### Images
- `POST /api/images/generate` - Generate new image
- `GET /api/images/my-images` - Get user's images
- `POST /api/images/upload` - Upload image
- `GET /api/images/:id/download` - Download image

### Admin
- `GET /api/admin/stats` - System statistics
- `PUT /api/admin/settings/:key` - Update settings

## 🚀 Deployment

1. Set up production database
2. Configure environment variables
3. Build frontend: `npm run build`
4. Start backend: `npm start`
5. Serve frontend static files

## 📋 Development Roadmap

See `development-plan.html` for detailed development checklist and milestones.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This project is licensed under the MIT License.
