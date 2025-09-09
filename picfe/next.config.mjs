/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  // Run on port 3010 to match backend CORS configuration
  experimental: {
    serverActions: {
      allowedOrigins: ['localhost:3000', '127.0.0.1:3000']
    }
  },
  // Proxy API requests to backend
  async rewrites() {
    return [
      {
        source: '/api/:path*',
  // Use NEXT_PUBLIC_API_URL set at build time (Dockerfile provides a default).
  // This allows the image build inside Docker to point to the backend service name
  // while local development can keep using localhost via .env.local.
  destination: `${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3011/api'}/:path*`,
      },
    ];
  },
  // Allow images from localhost backend and external sources
  images: {
    remotePatterns: [
      {
        protocol: 'http',
        hostname: 'localhost',
        port: '3011',
        pathname: '/uploads/**',
      },
      {
        protocol: 'https',
        hostname: 'picsum.photos',
        pathname: '/**',
      },
    ],
  },
};

export default nextConfig;
