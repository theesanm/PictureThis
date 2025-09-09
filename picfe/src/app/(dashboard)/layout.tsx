'use client';

import React, { useEffect, ReactNode, useState } from 'react';
import { useRouter } from 'next/navigation';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { useAuth } from '@/lib/auth-context';
import RequireVerification from '@/components/RequireVerification';
import { ToastProvider } from '@/components/ui/use-toast';

interface DashboardLayoutProps {
  children: ReactNode;
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
  const { isAuthenticated, loading, user } = useAuth();
  const router = useRouter();
  const [showVerificationNotice, setShowVerificationNotice] = useState(false);

  useEffect(() => {
    if (!loading && !isAuthenticated) {
      router.push('/login');
    }
  }, [isAuthenticated, loading, router]);

  // Check if the user's email is verified
  useEffect(() => {
    if (user && user.isVerified === false) {
      setShowVerificationNotice(true);
    } else {
      setShowVerificationNotice(false);
    }
  }, [user]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-900">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-purple-500"></div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return null; // Will redirect in the useEffect
  }

  return (
    <ToastProvider>
      <div className="min-h-screen flex flex-col bg-gray-900 text-white">
        <Header />
        <main className="flex-1 container mx-auto px-4 py-8">
          {showVerificationNotice && user?.email ? (
            <div className="mb-6">
              <RequireVerification email={user.email} />
            </div>
          ) : null}
          {children}
        </main>
        <Footer />
      </div>
    </ToastProvider>
  );
}
