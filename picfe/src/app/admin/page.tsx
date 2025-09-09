"use client";

import React from 'react';
import Link from 'next/link';
import { useAuth } from '@/lib/auth-context';

// Prevent Next.js from prerendering this client-heavy admin route
export const dynamic = 'force-dynamic';
export const fetchCache = 'force-no-store';

// Inline icon components to avoid SSR/prerender issues
const ArrowLeft = (props: React.SVGProps<SVGSVGElement>) => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
    <path d="M19 12H5" />
    <path d="M12 19l-7-7 7-7" />
  </svg>
);

const Users = (props: React.SVGProps<SVGSVGElement>) => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
    <path d="M17 21v-2a4 4 0 0 0-3-3.87" />
    <path d="M9 21v-2a4 4 0 0 1 3-3.87" />
    <path d="M8 7a4 4 0 1 1 8 0" />
  </svg>
);

const CreditCard = (props: React.SVGProps<SVGSVGElement>) => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
    <rect x="1" y="4" width="22" height="16" rx="2" />
    <line x1="1" y1="10" x2="23" y2="10" />
  </svg>
);

const Settings = (props: React.SVGProps<SVGSVGElement>) => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
    <circle cx="12" cy="12" r="3" />
    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06A2 2 0 1 1 2.3 17.1l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09c.7 0 1.27-.4 1.51-1a1.65 1.65 0 0 0-.33-1.82L4.3 3.3A2 2 0 1 1 7.13.47l.06.06a1.65 1.65 0 0 0 1.82.33h.09c.7 0 1.27-.4 1.51-1V1a2 2 0 1 1 4 0v.09c.24.6.81 1 1.51 1h.09c.7 0 1.27-.4 1.51-1a1.65 1.65 0 0 0 1.82-.33l.06-.06A2 2 0 1 1 21.7 6.9l-.06.06a1.65 1.65 0 0 0-.33 1.82c.2.6.82 1 1.51 1H21a2 2 0 1 1 0 4h-.09c-.7 0-1.27.4-1.51 1z" />
  </svg>
);

const Database = (props: React.SVGProps<SVGSVGElement>) => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
    <ellipse cx="12" cy="5" rx="9" ry="3" />
    <path d="M3 5v6c0 1.66 4 3 9 3s9-1.34 9-3V5" />
    <path d="M3 11v6c0 1.66 4 3 9 3s9-1.34 9-3v-6" />
  </svg>
);

export default function AdminDashboard() {
  const { user } = useAuth();
  
  return (
    <div className="min-h-screen bg-gray-900">
      <div className="bg-gray-800 border-b border-gray-700 shadow-lg">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between py-4">
            <div className="flex items-center">
              <Link href="/dashboard" className="flex items-center text-gray-300 hover:text-white">
                <ArrowLeft width={16} height={16} className="mr-2" />
                Return to Dashboard
              </Link>
              <h1 className="ml-8 text-xl font-bold text-white">Admin Portal</h1>
            </div>
            <div className="flex items-center">
              <span className="text-gray-300 mr-2">Logged in as Admin:</span>
              <span className="text-white font-medium">{user?.email}</span>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 className="text-3xl font-bold text-white mb-8">Admin Dashboard</h1>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {/* User Management */}
          <Link href="/admin/users">
            <div className="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:bg-gray-750 transition-colors">
              <div className="flex items-center">
                <div className="bg-purple-500 bg-opacity-20 p-3 rounded-lg">
                  <Users width={24} height={24} className="text-purple-400" />
                </div>
                <h2 className="ml-4 text-xl font-semibold text-white">User Management</h2>
              </div>
              <p className="mt-4 text-gray-300">
                Manage users, assign roles, and control account status. Reset acceptance agreements.
              </p>
            </div>
          </Link>
          
          {/* Credit Management */}
          <Link href="/admin/credits">
            <div className="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:bg-gray-750 transition-colors">
              <div className="flex items-center">
                <div className="bg-green-500 bg-opacity-20 p-3 rounded-lg">
                  <CreditCard width={24} height={24} className="text-green-400" />
                </div>
                <h2 className="ml-4 text-xl font-semibold text-white">Credit Management</h2>
              </div>
              <p className="mt-4 text-gray-300">
                Assign credits to users, view credit history, and set credit pricing.
              </p>
            </div>
          </Link>
          
          {/* System Settings */}
          <Link href="/admin/settings">
            <div className="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:bg-gray-750 transition-colors">
              <div className="flex items-center">
                <div className="bg-blue-500 bg-opacity-20 p-3 rounded-lg">
                  <Settings width={24} height={24} className="text-blue-400" />
                </div>
                <h2 className="ml-4 text-xl font-semibold text-white">System Settings</h2>
              </div>
              <p className="mt-4 text-gray-300">
                Configure application settings, credit costs per image, and global options.
              </p>
            </div>
          </Link>

          {/* Analytics */}
          <Link href="/admin/analytics">
            <div className="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:bg-gray-750 transition-colors">
              <div className="flex items-center">
                <div className="bg-yellow-500 bg-opacity-20 p-3 rounded-lg">
                  <Database width={24} height={24} className="text-yellow-400" />
                </div>
                <h2 className="ml-4 text-xl font-semibold text-white">Analytics</h2>
              </div>
              <p className="mt-4 text-gray-300">
                View system usage, image generation statistics, and credit consumption.
              </p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  );
}
