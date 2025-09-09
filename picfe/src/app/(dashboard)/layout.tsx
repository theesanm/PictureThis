import React from 'react';
import { DashboardClient } from './DashboardClient';

// Prevent Next.js from prerendering dashboard routes which rely on client-side-only
// hooks and localStorage. This ensures pages under /(dashboard) are treated as dynamic.
export const dynamic = 'force-dynamic';

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <DashboardClient>
      {children}
    </DashboardClient>
  );
}
