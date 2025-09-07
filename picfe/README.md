# PictureThis - Next.js Frontend

This is the new frontend implementation for PictureThis, an AI image generation application built with Next.js, React, and Tailwind CSS.

## Features

- Modern, responsive UI with a dark theme
- User authentication (register, login, email verification)
- AI image generation from text prompts
- Image generation from reference images
- Prompt enhancement using AI
- User dashboard with recent images
- User profile management
- Credit system for image generation

## Tech Stack

- **Next.js 14+** - React framework with App Router
- **Tailwind CSS 4.1** - Utility-first CSS framework
- **React** - UI library
- **Framer Motion** - Animation library
- **React Toastify** - Toast notifications
- **Lucide React** - Icon library

## Getting Started

1. Install dependencies:

```bash
npm install
```

2. Start the development server:

```bash
npm run dev
```

3. Open [http://localhost:3010](http://localhost:3010) in your browser.

## Environment Variables

Create a `.env.local` file in the root of the project with the following variables:

```
NEXT_PUBLIC_API_URL=http://localhost:3011/api
```

## Project Structure

- `/app` - Next.js App Router routes and layouts
- `/components` - Reusable UI components
- `/lib` - Utility functions, API client, and authentication context

## API Integration

The frontend connects to a backend API at `http://localhost:3011/api` for all data operations. The API endpoints used include:

- `/auth/*` - User authentication
- `/users/*` - User management
- `/images/*` - Image generation and gallery
- `/prompts/*` - Prompt enhancement
- `/credits/*` - Credit management

## Developer Notes

- Ensure the backend API is running at `http://localhost:3011` before starting the frontend.
- All API requests require authentication via JWT token for protected routes.
- The application uses a credit system where each image generation consumes credits from the user's account.
