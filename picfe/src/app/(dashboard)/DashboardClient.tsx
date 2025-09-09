'use client';

import React from 'react';

interface DashboardClientProps {
  children: React.ReactNode;
}

export function DashboardClient({ children }: DashboardClientProps) {
  return (
    <div className="min-h-screen flex flex-col bg-gray-900 text-white">
      <main className="flex-1 container mx-auto px-4 py-8">
        {children}
      </main>
    </div>
  );
}
