"use client";

import React from 'react';
import { AuthProvider } from '@/lib/auth-context';
// import { ToastContainer } from 'react-toastify';
// import 'react-toastify/dist/ReactToastify.css';

export default function ClientAppProviders({ children }: { children: React.ReactNode }) {
  return (
    <AuthProvider>
      {children}
      {/* <ToastContainer position="top-right" /> */}
    </AuthProvider>
  );
}
