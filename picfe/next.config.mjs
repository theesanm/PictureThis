/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  // Use regular production build instead of standalone
  // output: 'standalone',
  trailingSlash: true,
  images: {
    unoptimized: true,
    remotePatterns: [
      {
        protocol: 'http',
        hostname: 'backend',
        port: '3011',
        pathname: '/uploads/**',
      },
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
  experimental: {
    serverActions: {
      allowedOrigins: ['localhost:3701', '127.0.0.1:3701']
    }
  },
  // Completely disable static generation
  generateBuildId: async () => {
    return 'build-' + Date.now()
  },
  // Disable static optimization
  // optimizeFonts: false,
  // swcMinify: false,
  // Proxy API requests to backend (only in development)
  async rewrites() {
    // Only apply rewrites in development, not in production Docker
    if (process.env.NODE_ENV === 'development') {
      return [
        {
          source: '/api/:path*',
          destination: 'http://backend:3011/api/:path*',
        },
      ];
    }
    return [];
  },
};

export default nextConfig;
