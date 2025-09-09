import React from 'react';

// Server-rendered homepage. Avoid client directive to prevent server from trying
// to invoke client-only components during prerender.
export const dynamic = 'force-dynamic';

export default function Home() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-900 text-white">
      <main>
        <h1 className="text-2xl font-bold">PictureThis</h1>
        <p className="text-gray-300 mt-2">Welcome — minimal homepage for build isolation.</p>
      </main>
    </div>
  );
}
