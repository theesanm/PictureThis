"use client";

import React from 'react';
import Link from 'next/link';

const ImageIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 24, height = 24, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
    <circle cx="8.5" cy="8.5" r="1.5" />
    <path d="M21 15l-5-5L5 21" />
  </svg>
);

interface AuthClientProps {
  children: React.ReactNode;
}

export default function AuthClient({ children }: AuthClientProps) {
  return (
    <div className="min-h-screen flex flex-col bg-gray-900 text-white">
      <header className="py-6 px-4">
        <div className="container mx-auto">
          <Link href="/" className="flex items-center gap-3 w-fit">
              <div className="p-2 bg-gradient-to-r from-purple-600 to-pink-500 rounded-lg flex items-center justify-center">
              <ImageIcon width={24} height={24} className="text-white" />
            </div>
            <span className="text-xl md:text-2xl font-bold text-white">PictureThis</span>
          </Link>
        </div>
      </header>

      <main className="flex-1 flex items-center justify-center py-12 px-4">
        <div className="max-w-md w-full space-y-8">
          {children}
        </div>
      </main>

      <footer className="py-6 bg-gray-900 border-t border-gray-800">
        <div className="container mx-auto px-4 text-center">
          <p className="text-gray-400 text-sm">
            &copy; {new Date().getFullYear()} PictureThis. All rights reserved.
          </p>
        </div>
      </footer>
    </div>
  );
}