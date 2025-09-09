import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  /* config options here */
  webpack: (config) => {
    return config;
  },
  async rewrites() {
    // Use NEXT_PUBLIC_API_URL when available so the app inside Docker can
    // proxy API requests to the correct service (e.g. http://backend:3011/api)
    const apiUrl = (process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3011/api').replace(/\/$/, '');
    return [
      {
        source: '/api/:path*',
        destination: `${apiUrl}/:path*`,
      },
    ];
  },
  devIndicators: {
    buildActivity: true,
  },
  typescript: {
    // We'll handle type errors in development
    ignoreBuildErrors: true,
  },
  eslint: {
    // We'll handle ESLint errors in development
    ignoreDuringBuilds: true,
  },
  // Set the port to 3010
  serverRuntimeConfig: {
    port: 3010,
  },
};

export default nextConfig;
