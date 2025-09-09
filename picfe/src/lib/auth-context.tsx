'use client';

import React, { createContext, useState, useContext, useEffect, ReactNode } from 'react';
import { jwtDecode } from 'jwt-decode';
import { authAPI, userAPI, creditsAPI } from './api';

interface User {
  id?: string;
  email?: string;
  name?: string;
  fullName?: string;
  role?: string;
  isAdmin?: boolean;
  credits?: number;
  isVerified?: boolean;
  createdAt?: string;
  updatedAt?: string;
}

interface AuthContextType {
  user: User | null;
  credits: number;
  isAuthenticated: boolean;
  loading: boolean;
  isLoading: boolean; // Alias for loading for better readability
  login: (email: string, password: string) => Promise<{ success: boolean; message?: string; user?: User }>;
  register: (userData: RegisterData) => Promise<{ success: boolean; email?: string; message?: string }>;
  logout: () => void;
  updateUserCredits: (newCredits: number) => void;
  refreshUserProfile: () => Promise<void>;
}

interface AuthProviderProps {
  children: ReactNode;
}

interface RegisterData {
  name: string;
  email: string;
  password: string;
}

interface DecodedToken {
  exp: number;
  [key: string]: any;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: AuthProviderProps) {
  // Dummy provider with hardcoded values
  const value: AuthContextType = {
    user: null,
    credits: 0,
    isAuthenticated: false,
    loading: false,
    isLoading: false,
    login: async () => ({ success: false, message: 'Not implemented' }),
    register: async () => ({ success: false, message: 'Not implemented' }),
    logout: () => {},
    updateUserCredits: () => {},
    refreshUserProfile: async () => {},
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export default AuthContext;
