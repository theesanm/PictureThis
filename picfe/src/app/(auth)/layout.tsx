import React from 'react';
import AuthClient from './AuthClient';

// Prevent Next.js from prerendering auth routes which use client-only behavior
export const dynamic = 'force-dynamic';

export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return <AuthClient>{children}</AuthClient>;
}
