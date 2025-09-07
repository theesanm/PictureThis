'use client';

import React, { createContext, useState, useContext, useEffect, ReactNode } from 'react';
import { jwtDecode } from 'jwt-decode';
import { authAPI, userAPI, creditsAPI } from './api';
import { useRouter } from 'next/navigation';

interface User {
  id?: string;
  email?: string;
  name?: string;
  fullName?: string;
  role?: string;
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
  const [user, setUser] = useState<User | null>(null);
  const [credits, setCredits] = useState<number>(0);
  const [loading, setLoading] = useState<boolean>(true);
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const router = useRouter();

  useEffect(() => {
    // Check if we're in a browser environment
    if (typeof window !== 'undefined') {
      // Check for token in localStorage
      const token = localStorage.getItem('token');
      
      if (token && token !== 'undefined' && token !== 'null') {
        try {
          // Make sure the token is a valid JWT format before trying to decode it
          if (token.split('.').length !== 3) {
            console.error('Invalid token format');
            handleLogout();
            return;
          }
          
          // Decode token to check expiration
          const decoded = jwtDecode<DecodedToken>(token);
          const currentTime = Date.now() / 1000;
          
          if (decoded.exp < currentTime) {
            // Token expired
            console.log('Token expired');
            handleLogout();
          } else {
            // Token valid, get user from localStorage
            try {
              const storedUserStr = localStorage.getItem('user');
              if (!storedUserStr) throw new Error('No user data found');
              
              const storedUser = JSON.parse(storedUserStr) as User;
              setUser(storedUser);
              setCredits(storedUser.credits || 0);
              setIsAuthenticated(true);
              
              // Optional: refresh user data from API
              fetchUserProfile();
            } catch (parseError) {
              console.error('Error parsing stored user data:', parseError);
              handleLogout();
            }
          }
        } catch (error) {
          console.error('Invalid token:', error);
          handleLogout();
        }
      } else {
        // No valid token found
        console.log('No valid token found');
      }
      setLoading(false);
    }
  }, []);

  const fetchUserProfile = async (): Promise<void> => {
    try {
      const response = await userAPI.getProfile();
      if (response.data.success && response.data.data && response.data.data.user) {
        const userData = response.data.data.user as User;
        setUser(userData);
        setCredits(userData.credits || 0);
        localStorage.setItem('user', JSON.stringify(userData));
      }
    } catch (error: any) {
      console.error('Error fetching user profile:', error);
      
      // Check if this is a 403 error due to email verification
      if (error.response?.status === 403 && error.response?.data?.requiresVerification) {
        // Update the user state to indicate verification is required
        if (user) {
          const updatedUser = { ...user, isVerified: false };
          setUser(updatedUser);
          localStorage.setItem('user', JSON.stringify(updatedUser));
        }
        
        // We're not logging out the user, just setting isVerified to false
        // This way the UI can show a verification prompt
      }
    }
  };
  
    // Function to refresh user credits periodically
  const refreshCredits = async (): Promise<void> => {
    if (!isAuthenticated || !user) return;
    
    try {
      // Use the creditsAPI instead of direct fetch to ensure proper URL and auth headers
      const response = await creditsAPI.getBalance();
      const data = response.data;
      
      if (data.success && data.data && typeof data.data.credits === 'number') {
        const newCredits = data.data.credits;
        setCredits(newCredits);
        // Also update the user object
        setUser(prev => prev ? { ...prev, credits: newCredits } : null);
        
        // Update localStorage
        if (typeof window !== 'undefined') {
          const storedUser = localStorage.getItem('user');
          if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            localStorage.setItem('user', JSON.stringify({
              ...parsedUser,
              credits: newCredits
            }));
          }
        }
      }
    } catch (error: any) {
      console.error('Failed to refresh credits:', error);
      
      // Check if this is a 403 error due to email verification
      if (error.response?.status === 403) {
        const errorData = error.response.data;
        if (errorData.requiresVerification) {
          // Update the user state to indicate verification is required
          setUser(prev => prev ? { ...prev, isVerified: false } : null);
        }
      }
    }
  };
  
  // Set up credit refresh interval when user is authenticated
  useEffect(() => {
    if (!isAuthenticated) return;
    
    // Refresh credits immediately
    refreshCredits();
    
    // Set up interval for periodic refresh (every 30 seconds)
    const intervalId = setInterval(refreshCredits, 30000);
    
    // Clean up on unmount or when auth state changes
    return () => clearInterval(intervalId);
  }, [isAuthenticated, user?.id]);

  const login = async (email: string, password: string) => {
    try {
      const response = await authAPI.login({ email, password });
      
      if (response.data.success && response.data.data) {
        const { data } = response.data;
        
        // Check if token and user exist in the response
        if (!data.token) {
          console.error('No token received from server');
          return { success: false, message: 'Authentication error: No token received' };
        }
        
        if (!data.user) {
          console.error('No user data received from server');
          return { success: false, message: 'Authentication error: No user data received' };
        }
        
        // Store token and user data
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        
        // Update state
        setUser(data.user as User);
        setCredits(data.user.credits || 0);
        setIsAuthenticated(true);
        
        return { success: true, user: data.user };
      } else {
        return { success: false, message: response.data.message || 'Login failed' };
      }
    } catch (error: any) {
      console.error('Login error:', error);
      const message = error.response?.data?.message || 'Failed to login';
      return { success: false, message };
    }
  };

  const register = async (userData: RegisterData) => {
    try {
      const response = await authAPI.register(userData);
      
      if (response.data.success) {
        return { success: true, email: userData.email };
      }
      return { success: false, message: 'Unknown error occurred' };
    } catch (error: any) {
      console.error('Registration error:', error);
      const message = error.response?.data?.message || 'Registration failed';
      return { success: false, message };
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setUser(null);
    setCredits(0);
    setIsAuthenticated(false);
    router.push('/');
  };

  const updateUserCredits = (newCredits: number) => {
    if (user) {
      const updatedUser = { ...user, credits: newCredits };
      setUser(updatedUser);
      setCredits(newCredits);
      localStorage.setItem('user', JSON.stringify(updatedUser));
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        credits,
        isAuthenticated,
        loading,
        isLoading: loading, // Add alias for better readability
        login,
        register,
        logout: handleLogout,
        updateUserCredits,
        refreshUserProfile: fetchUserProfile,
      }}
    >
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
