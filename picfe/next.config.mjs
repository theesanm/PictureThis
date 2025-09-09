/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  // Run on port 3010 to match backend CORS configuration
  experimental: {
    serverActions: {
      allowedOrigins: ['localhost:3010', '127.0.0.1:3010']
    }
  },
  // Proxy API requests to backend
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: 'http://localhost:3011/api/:path*',
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
