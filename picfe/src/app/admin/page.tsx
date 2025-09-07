'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { Users, CreditCard, Settings, ArrowLeft, Database } from 'lucide-react';
import { useAuth } from '@/lib/auth-context';

export default function AdminDashboard() {
  const { user } = useAuth();
  const router = useRouter();
  
  return (
    <div className="min-h-screen bg-gray-900">
      <div className="bg-gray-800 border-b border-gray-700 shadow-lg">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between py-4">
            <div className="flex items-center">
              <Link href="/dashboard" className="flex items-center text-gray-300 hover:text-white">
                <ArrowLeft size={16} className="mr-2" />
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
                  <Users size={24} className="text-purple-400" />
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
                  <CreditCard size={24} className="text-green-400" />
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
                  <Settings size={24} className="text-blue-400" />
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
                  <Database size={24} className="text-yellow-400" />
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
