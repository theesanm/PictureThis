/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  // Run on port 3010 to match backend CORS configuration
  experimental: {
    serverActions: {
      allowedOrigins: ['localhost:3010', '127.0.0.1:3010']
    }
  }
};

export default nextConfig;
