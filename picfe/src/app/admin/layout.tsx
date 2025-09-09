import React from 'react';

export const dynamic = 'force-dynamic';
export const fetchCache = 'force-no-store';
import AdminClientAuth from '../../components/AdminClientAuth';

// Server-rendered admin layout. Client-only auth/redirect is handled by
// AdminClientAuth so prerender won't attempt to call browser-only hooks.
export default function AdminLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-gray-900 text-white">
      <AdminClientAuth>{children}</AdminClientAuth>
    </div>
  );
}
