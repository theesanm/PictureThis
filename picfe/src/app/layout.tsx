import type { Metadata } from "next";
// import { Inter, Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
// import ClientAppProviders from '@/components/ClientAppProviders';

// Force dynamic rendering for all pages
export const dynamic = 'force-dynamic';

// const geistSans = Geist({
//   variable: "--font-geist-sans",
//   subsets: ["latin"],
// });

// const geistMono = Geist_Mono({
//   variable: "--font-geist-mono",
//   subsets: ["latin"],
// });

// const inter = Inter({
//   variable: "--font-inter",
//   subsets: ["latin"],
// });

export const metadata: Metadata = {
  title: "PictureThis - AI Image Generation",
  description: "Generate stunning AI images from text or image prompts",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className="antialiased">
        {children}
      </body>
    </html>
  );
}
