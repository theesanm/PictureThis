'use client';

import React, { useEffect, ReactNode } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/lib/auth-context';
import Link from 'next/link';
import { Image } from 'lucide-react';

interface AuthLayoutProps {
  children: ReactNode;
}

export default function AuthLayout({ children }: AuthLayoutProps) {
  const { isAuthenticated, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!loading && isAuthenticated) {
      router.push('/dashboard');
    }
  }, [isAuthenticated, loading, router]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-900">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-purple-500"></div>
      </div>
    );
  }

  if (isAuthenticated) {
    return null; // Will redirect in the useEffect
  }

  return (
    <div className="min-h-screen flex flex-col bg-gray-900 text-white">
      <header className="py-6 px-4">
        <div className="container mx-auto">
          <Link href="/" className="flex items-center gap-3 w-fit">
            <div className="p-2 bg-gradient-to-r from-purple-600 to-pink-500 rounded-lg flex items-center justify-center">
              <Image size={24} className="text-white" />
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
