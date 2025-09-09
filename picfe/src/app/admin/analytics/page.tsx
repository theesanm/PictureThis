import React from 'react';
import Link from 'next/link';

// Inline icon components to avoid SSR/prerender issues with external icon libs
const ArrowLeft = (props: React.SVGProps<SVGSVGElement>) => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
    <path d="M19 12H5" />
    <path d="M12 19l-7-7 7-7" />
  </svg>
);

const BarChart2 = (props: React.SVGProps<SVGSVGElement>) => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
    <path d="M3 3v18h18" />
    <rect x="7" y="10" width="3" height="7" />
    <rect x="12" y="6" width="3" height="11" />
    <rect x="17" y="3" width="3" height="14" />
  </svg>
);

export default function Analytics() {
  return (
    <div className="min-h-screen bg-gray-900">
      <div className="bg-gray-800 border-b border-gray-700 shadow-lg">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between py-4">
            <div className="flex items-center">
              <Link href="/admin" className="flex items-center text-gray-300 hover:text-white">
                <ArrowLeft width={16} height={16} className="mr-2" />
                Back to Admin Dashboard
              </Link>
              <h1 className="ml-8 text-xl font-bold text-white">Analytics</h1>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex flex-col items-center justify-center h-64 bg-gray-800 rounded-lg border border-gray-700">
          <BarChart2 width={48} height={48} className="text-purple-500 mb-4" />
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
