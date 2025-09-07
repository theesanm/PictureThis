import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  /* config options here */
  webpack: (config) => {
    return config;
  },
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: 'http://localhost:3011/api/:path*',
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
