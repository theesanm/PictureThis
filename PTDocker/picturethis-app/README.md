# PictureThis App

## Overview
PictureThis is a web application that utilizes advanced AI to generate stunning images from text descriptions or reference images. Built with React, Next.js, and Framer Motion, it provides a seamless user experience with powerful features for image generation.

## Project Structure
```
picturethis-app
├── src
│   ├── app
│   │   ├── page.tsx
│   │   └── layout.tsx
│   ├── components
│   │   ├── Header.tsx
│   │   ├── Footer.tsx
│   │   └── ...
│   └── lib
│       └── ...
├── public
│   └── ...
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
├── package.json
├── next.config.js
├── tailwind.config.js
├── tsconfig.json
└── README.md
```

## Getting Started

### Prerequisites
- Node.js (version 14 or higher)
- Docker
- Docker Compose

### Installation
1. Clone the repository:
   ```
   git clone <repository-url>
   cd picturethis-app
   ```

2. Install dependencies:
   ```
   npm install
   ```

### Docker Setup
To run the application using Docker, follow these steps:

1. **Build the Docker Image**:
   ```
   docker build -t picturethis-app .
   ```

2. **Run with Docker Compose**:
   ```
   docker-compose up
   ```

This command will start both the PictureThis application and a PostgreSQL database.

### Database Configuration
Ensure that your application is configured to connect to the PostgreSQL database. The connection details should be specified in the `docker-compose.yml` file as environment variables.

### Deployment
1. **Push Docker Image to Cloud**:
   After testing locally, push your Docker image to a cloud container registry (e.g., Docker Hub):
   ```
   docker tag picturethis-app <your-dockerhub-username>/picturethis-app
   docker push <your-dockerhub-username>/picturethis-app
   ```

2. **Deploy to Cloud**:
   Use a cloud service (e.g., AWS ECS, Google Cloud Run) to deploy the application using the pushed Docker image. Configure the necessary networking and environment variables.

### Accessing the Application
Once deployed, you can access the application via the provided cloud URL.

## Features
- Text-to-Image Generation
- Prompt Enhancement
- Instant Sharing
- Image-to-Image Generation
- Image Gallery

## License
This project is licensed under the MIT License. See the LICENSE file for details.