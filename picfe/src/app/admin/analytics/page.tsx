'use client';

import React from 'react';
import Link from 'next/link';
import { ArrowLeft, BarChart2 } from 'lucide-react';

export default function Analytics() {
  return (
    <div className="min-h-screen bg-gray-900">
      <div className="bg-gray-800 border-b border-gray-700 shadow-lg">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between py-4">
            <div className="flex items-center">
              <Link href="/admin" className="flex items-center text-gray-300 hover:text-white">
                <ArrowLeft size={16} className="mr-2" />
                Back to Admin Dashboard
              </Link>
              <h1 className="ml-8 text-xl font-bold text-white">Analytics</h1>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex flex-col items-center justify-center h-64 bg-gray-800 rounded-lg border border-gray-700">
          <BarChart2 size={48} className="text-purple-500 mb-4" />
          <h2 className="text-xl font-semibold text-white mb-2">
            Analytics Dashboard Coming Soon
          </h2>
          <p className="text-gray-400">
            This feature is currently under development.
          </p>
        </div>
      </div>
    </div>
  );
}
